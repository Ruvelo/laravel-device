<?php

declare(strict_types=1);

namespace Ruvelo\Device\Detection;

use Detection\MobileDetect;
use Ruvelo\Device\Browser;
use Ruvelo\Device\Device;
use Ruvelo\Device\DeviceType;
use Ruvelo\Device\Engine;
use Ruvelo\Device\OperatingSystem;

/**
 * Turns a User-Agent (plus Client Hints, when the browser sent them) into a Device.
 *
 * Stateless apart from Mobile Detect, which is only asked about the long tail
 * of phones and tablets that none of our own rules recognise.
 */
final class Parser
{
    private const DESKTOP_SYSTEMS = [
        'Windows', 'macOS', 'Linux', 'Chrome OS', 'Ubuntu', 'Debian', 'Fedora', 'openSUSE',
        'Arch Linux', 'FreeBSD', 'OpenBSD', 'NetBSD', 'Solaris', 'Fuchsia',
    ];

    private const PHONE_SYSTEMS = ['Windows Phone', 'Windows Mobile', 'KaiOS', 'Symbian', 'BlackBerry OS', 'Tizen'];

    private const ANDROID_SYSTEMS = ['Android', 'Fire OS', 'HarmonyOS'];

    private ?MobileDetect $mobileDetect = null;

    /**
     * @param  bool  $ipadConfirmed  The browser told us it has a touch screen (see @detectIpad)
     * @param  array<string, string>  $headers  Lowercased request headers, for CloudFront's device headers
     */
    public function parse(string $userAgent, ?ClientHints $hints = null, bool $ipadConfirmed = false, array $headers = []): Device
    {
        $hints ??= new ClientHints;
        $bot = Bots::match($userAgent);
        $browser = Browsers::match($userAgent);
        $os = OperatingSystems::match($userAgent);
        $usedHints = false;

        if ($hints->present()) {
            [$browser, $os, $usedHints] = $this->applyHints($hints, $browser, $os);
        }

        $type = DeviceType::Unknown;
        $brand = $model = null;
        $mightBeIpad = false;
        $osName = $os['name'] ?? null;

        if ($bot !== null) {
            $type = DeviceType::Bot;
        } elseif ($userAgent === 'Amazon CloudFront') {
            $type = $this->cloudFront($headers);
        } elseif (($special = Hardware::special($userAgent)) !== null) {
            ['type' => $type, 'brand' => $brand, 'model' => $model] = $special;
        } elseif ($osName === 'iOS' || $osName === 'iPadOS') {
            $brand = 'Apple';
            [$type, $model] = match (true) {
                (bool) preg_match('~iPad~i', $userAgent) => [DeviceType::Tablet, 'iPad'],
                (bool) preg_match('~iPod~i', $userAgent) => [DeviceType::Phone, 'iPod touch'],
                default => [DeviceType::Phone, 'iPhone'],
            };
        } elseif ($osName === 'macOS' && ! $usedHints && ($ipadConfirmed || preg_match('~\b(?:CriOS|FxiOS|EdgiOS|OPiOS)/~', $userAgent))) {
            // iPadOS asks for desktop sites with a Mac User-Agent. Only the iOS
            // browsers' own tokens, or a touch screen reported by the page, give it away.
            $type = DeviceType::Tablet;
            $brand = 'Apple';
            $model = 'iPad';
            $os = ['name' => 'iPadOS', 'version' => preg_match('~Version/(\d+(?:\.\d+)?)~', $userAgent, $match) ? $match[1] : null];
        } elseif ($osName === 'macOS') {
            $type = DeviceType::Desktop;
            $brand = 'Apple';
            $model = 'Mac';
            $mightBeIpad = ! $usedHints && ($browser['name'] ?? null) === 'Safari';
        } elseif (in_array($osName, self::ANDROID_SYSTEMS, true)) {
            $model = $hints->model ?? Hardware::androidModel($userAgent);
            $brand = Hardware::androidBrand($model, $userAgent);
            $type = $this->androidType($userAgent, $hints);
        } elseif (in_array($osName, self::PHONE_SYSTEMS, true)) {
            [$type, $brand, $model] = $this->legacyPhone($userAgent, (string) $osName);
        } elseif (in_array($osName, self::DESKTOP_SYSTEMS, true)) {
            $type = DeviceType::Desktop;
        } else {
            $type = $this->mobileDetectType($userAgent);
        }

        if ($bot !== null) {
            $brand = $model = null;
        }

        return new Device(
            type: $type,
            browser: new Browser($browser['name'] ?? null, $browser['version'] ?? null),
            os: new OperatingSystem($os['name'] ?? null, $os['version'] ?? null),
            engine: $this->engine($userAgent, $os['name'] ?? null, $hints),
            brand: $brand,
            model: $model,
            isBot: $bot !== null,
            botName: $bot['name'] ?? null,
            botCategory: $bot['category'] ?? null,
            userAgent: $userAgent,
            usedClientHints: $usedHints,
            mightBeIpad: $mightBeIpad,
        );
    }

    /**
     * Client Hints win over the User-Agent, which Chromium has frozen, except
     * where the User-Agent is more specific: Vivaldi sends only "Chromium".
     *
     * @param  array{name: string, version: string|null}|null  $browser
     * @param  array{name: string, version: string|null}|null  $os
     * @return array{0: array{name: string, version: string|null}|null, 1: array{name: string, version: string|null}|null, 2: bool}
     */
    private function applyHints(ClientHints $hints, ?array $browser, ?array $os): array
    {
        $used = false;
        $hinted = $hints->browser();

        if ($hinted !== null) {
            $generic = in_array($hinted['name'], ['Chrome', 'Chromium'], true);
            $uaIsMoreSpecific = $browser !== null
                && in_array($browser['name'], Browsers::CHROMIUM_BASED, true)
                && ! in_array($browser['name'], ['Chrome', 'Chromium'], true);

            if (! ($generic && $uaIsMoreSpecific)) {
                // Sec-CH-UA alone carries only the major version; the User-Agent says as much.
                if ($browser !== null && $browser['name'] === $hinted['name'] && ! str_contains((string) $hinted['version'], '.')) {
                    $hinted['version'] = $browser['version'] ?? $hinted['version'];
                }
                $browser = $hinted;
                $used = true;
            }
        }

        if ($hints->platform !== null) {
            $sameSystem = $os !== null && $os['name'] === $hints->platform;
            $os = ['name' => $hints->platform, 'version' => $hints->osVersion() ?? ($sameSystem ? $os['version'] : null)];
            $used = true;
        }

        return [$browser, $os, $used];
    }

    private function androidType(string $userAgent, ClientHints $hints): DeviceType
    {
        foreach ($hints->formFactors as $factor) {
            $type = match ($factor) {
                'watch' => DeviceType::Wearable,
                'tablet' => DeviceType::Tablet,
                'mobile' => DeviceType::Phone,
                default => null,
            };

            if ($type !== null) {
                return $type;
            }
        }

        if ($hints->mobile !== null) {
            // Chrome on an Android tablet sends "?0".
            return $hints->mobile ? DeviceType::Phone : DeviceType::Tablet;
        }

        return match (true) {
            (bool) preg_match('~\bMobile\b|Opera Mini|Opera Mobi~i', $userAgent) => DeviceType::Phone,
            // By convention Android browsers drop "Mobile" on tablets.
            default => DeviceType::Tablet,
        };
    }

    /**
     * @return array{0: DeviceType, 1: string|null, 2: string|null}
     */
    private function legacyPhone(string $userAgent, string $os): array
    {
        if (preg_match('~RIM Tablet OS|PlayBook~i', $userAgent)) {
            return [DeviceType::Tablet, 'BlackBerry', 'PlayBook'];
        }

        if ($os === 'BlackBerry OS') {
            return [DeviceType::Phone, 'BlackBerry', preg_match('~BlackBerry ?(\d{4})~', $userAgent, $m) ? 'BlackBerry '.$m[1] : null];
        }

        if (preg_match('~(NOKIA|Microsoft|HTC|SAMSUNG); ?([^;)]+)~i', $userAgent, $m)) {
            return [DeviceType::Phone, ucfirst(strtolower($m[1])) === 'Htc' ? 'HTC' : ucfirst(strtolower($m[1])), trim($m[2])];
        }

        if ($os === 'Tizen') {
            return [$this->mobileDetectType($userAgent) === DeviceType::Tablet ? DeviceType::Tablet : DeviceType::Phone, 'Samsung', null];
        }

        return [DeviceType::Phone, null, null];
    }

    /** The long tail: feature phones and obscure tablets Mobile Detect knows about. */
    private function mobileDetectType(string $userAgent): DeviceType
    {
        if ($userAgent === '') {
            return DeviceType::Unknown;
        }

        $detect = $this->mobileDetect ??= new MobileDetect(null, ['autoInitOfHttpHeaders' => false]);
        $detect->setUserAgent($userAgent);

        return match (true) {
            $detect->isTablet() => DeviceType::Tablet,
            $detect->isMobile() => DeviceType::Phone,
            default => DeviceType::Unknown,
        };
    }

    /**
     * Behind Amazon CloudFront the User-Agent is replaced, but CloudFront can
     * forward its own verdict in CloudFront-Is-*-Viewer headers.
     *
     * @param  array<string, string>  $headers
     */
    private function cloudFront(array $headers): DeviceType
    {
        return match ('true') {
            $headers['cloudfront-is-smarttv-viewer'] ?? null => DeviceType::Tv,
            $headers['cloudfront-is-tablet-viewer'] ?? null => DeviceType::Tablet,
            $headers['cloudfront-is-mobile-viewer'] ?? null => DeviceType::Phone,
            $headers['cloudfront-is-desktop-viewer'] ?? null => DeviceType::Desktop,
            default => DeviceType::Unknown,
        };
    }

    private function engine(string $userAgent, ?string $os, ClientHints $hints): Engine
    {
        $version = fn (string $pattern): ?string => preg_match($pattern, $userAgent, $m) ? Version::clean($m[1]) : null;

        // Every browser on iOS and iPadOS has to use WebKit.
        if (($os === 'iOS' || $os === 'iPadOS') && str_contains($userAgent, 'AppleWebKit')) {
            return new Engine('WebKit', $version('~AppleWebKit/([\d.]+)~'));
        }

        return match (true) {
            (bool) preg_match('~\bEdge/[\d.]+~', $userAgent) => new Engine('EdgeHTML', $version('~\bEdge/([\d.]+)~')),
            (bool) preg_match('~Trident/|MSIE ~', $userAgent) => new Engine('Trident', $version('~Trident/([\d.]+)~')),
            (bool) preg_match('~Presto/~', $userAgent) => new Engine('Presto', $version('~Presto/([\d.]+)~')),
            $hints->chromiumVersion() !== null => new Engine('Blink', $hints->chromiumVersion()),
            (bool) preg_match('~\b(?:Chrome|CrMo|Chromium)/(\d+)~', $userAgent, $m) && (int) $m[1] >= 28 => new Engine('Blink', $version('~\b(?:Chrome|CrMo|Chromium)/([\d.]+)~')),
            (bool) preg_match('~Gecko/~', $userAgent) && ! str_contains($userAgent, 'like Gecko') => new Engine('Gecko', $version('~\brv:([\d.]+)~')),
            str_contains($userAgent, 'AppleWebKit') => new Engine('WebKit', $version('~AppleWebKit/([\d.]+)~')),
            str_contains($userAgent, 'KHTML') => new Engine('KHTML'),
            default => new Engine,
        };
    }
}
