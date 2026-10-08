<?php

declare(strict_types=1);

namespace Ruvelo\Device\Detection;

/**
 * User-Agent Client Hints: the Sec-CH-UA-* request headers Chromium browsers
 * send instead of putting details in the (now frozen) User-Agent string.
 *
 * Sec-CH-UA, Sec-CH-UA-Mobile and Sec-CH-UA-Platform arrive on every HTTPS
 * request. The rest (platform version, model, full versions, form factors)
 * only after the site asks for them with an Accept-CH response header: see
 * the RequestClientHints middleware.
 *
 * @see https://wicg.github.io/ua-client-hints/
 */
final readonly class ClientHints
{
    /** The headers this class reads, lowercased. */
    public const HEADERS = [
        'sec-ch-ua', 'sec-ch-ua-full-version-list', 'sec-ch-ua-mobile', 'sec-ch-ua-platform',
        'sec-ch-ua-platform-version', 'sec-ch-ua-model', 'sec-ch-ua-form-factors',
    ];

    /** Brand names as sent in Sec-CH-UA, and what we call them. */
    private const BRANDS = [
        'google chrome' => 'Chrome',
        'microsoft edge' => 'Edge',
        'opera' => 'Opera',
        'opera gx' => 'Opera',
        'brave' => 'Brave',
        'samsung internet' => 'Samsung Internet',
        'yandex' => 'Yandex Browser',
        'yabrowser' => 'Yandex Browser',
        'vivaldi' => 'Vivaldi',
        'duckduckgo' => 'DuckDuckGo',
        'whale' => 'Whale',
        'naver whale' => 'Whale',
        'coc coc' => 'Coc Coc',
        'android webview' => 'Android WebView',
        'headlesschrome' => 'Headless Chrome',
        'huawei browser' => 'Huawei Browser',
        'chromium' => 'Chromium',
    ];

    private const PLATFORMS = [
        'windows' => 'Windows',
        'macos' => 'macOS',
        'linux' => 'Linux',
        'android' => 'Android',
        'chrome os' => 'Chrome OS',
        'chromium os' => 'Chrome OS',
        'ios' => 'iOS',
        'fuchsia' => 'Fuchsia',
    ];

    /**
     * @param  array<string, string>  $brands  Brand => version, GREASE removed, in the order sent
     * @param  list<string>  $formFactors
     */
    public function __construct(
        public array $brands = [],
        public ?bool $mobile = null,
        public ?string $platform = null,
        public ?string $platformVersion = null,
        public ?string $model = null,
        public array $formFactors = [],
    ) {}

    /**
     * @param  array<string, string>  $headers  Lowercased header names
     */
    public static function fromHeaders(array $headers): self
    {
        $brands = self::parseBrands($headers['sec-ch-ua-full-version-list'] ?? '');
        $major = self::parseBrands($headers['sec-ch-ua'] ?? '');
        $brands = $brands === [] ? $major : $brands + $major;

        $mobile = match (trim($headers['sec-ch-ua-mobile'] ?? '')) {
            '?1' => true,
            '?0' => false,
            default => null,
        };

        $platform = self::unquote($headers['sec-ch-ua-platform'] ?? null);
        $model = self::unquote($headers['sec-ch-ua-model'] ?? null);

        preg_match_all('/"([^"]*)"/', $headers['sec-ch-ua-form-factors'] ?? '', $factors);

        return new self(
            brands: $brands,
            mobile: $mobile,
            platform: $platform === null ? null : (self::PLATFORMS[strtolower($platform)] ?? ($platform === 'Unknown' ? null : $platform)),
            platformVersion: self::unquote($headers['sec-ch-ua-platform-version'] ?? null),
            model: $model,
            formFactors: array_values(array_map('strtolower', $factors[1])),
        );
    }

    /** True when the browser sent any hint worth reading. */
    public function present(): bool
    {
        return $this->brands !== [] || $this->platform !== null || $this->mobile !== null;
    }

    /**
     * The most specific brand: "Brave" rather than "Chromium".
     *
     * @return array{name: string, version: string|null}|null
     */
    public function browser(): ?array
    {
        $fallback = null;

        foreach ($this->brands as $brand => $version) {
            $name = self::BRANDS[strtolower($brand)] ?? $brand;

            if ($name === 'Chromium') {
                $fallback = ['name' => 'Chromium', 'version' => $version];

                continue;
            }

            return ['name' => $name, 'version' => $version];
        }

        return $fallback;
    }

    /** The Chromium version under the brand, for the engine. */
    public function chromiumVersion(): ?string
    {
        foreach ($this->brands as $brand => $version) {
            if (strcasecmp($brand, 'Chromium') === 0) {
                return $version;
            }
        }

        return null;
    }

    /**
     * The operating system version people know: Windows reports "15.0.0"
     * for Windows 11 and "10.0.0" for Windows 10.
     */
    public function osVersion(): ?string
    {
        if ($this->platformVersion === null || $this->platformVersion === '') {
            return null;
        }

        if ($this->platform === 'Windows') {
            $major = (int) $this->platformVersion;

            return match (true) {
                $major >= 13 => '11',
                $major >= 1 => '10',
                str_starts_with($this->platformVersion, '0.3') => '8.1',
                str_starts_with($this->platformVersion, '0.2') => '8',
                str_starts_with($this->platformVersion, '0.1') => '7',
                default => null,
            };
        }

        return Version::trimZeros($this->platformVersion);
    }

    /**
     * Brand => version pairs from a Structured Header list like
     * `"Chromium";v="130", "Not?A_Brand";v="99"`, without GREASE brands.
     *
     * @return array<string, string>
     */
    private static function parseBrands(string $header): array
    {
        if ($header === '' || ! preg_match_all('/"([^"]*)"\s*;\s*v\s*=\s*"([^"]*)"/', $header, $matches, PREG_SET_ORDER)) {
            return [];
        }

        $brands = [];
        foreach ($matches as [, $brand, $version]) {
            $brand = trim($brand);

            // GREASE: deliberately odd brands like "Not;A=Brand" that keep parsers honest.
            if ($brand === '' || preg_match('/^not[^a-z]*a[^a-z]*brand$/i', $brand)) {
                continue;
            }

            $brands[$brand] = $version;
        }

        return $brands;
    }

    private static function unquote(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(trim($value), '"');

        return $value === '' ? null : $value;
    }
}
