<?php

declare(strict_types=1);

namespace Ruvelo\Device\Detection;

/**
 * Operating systems, most specific first: Android and iOS say "like Mac OS X"
 * and "Linux", Windows Phone says "Android", and so on.
 *
 * Each rule is [name, pattern]; the first non-empty capture group is the version.
 */
final class OperatingSystems
{
    /** Windows NT kernel versions and the names people know them by. */
    private const WINDOWS_NT = [
        '10.0' => '10', '6.4' => '10', '6.3' => '8.1', '6.2' => '8', '6.1' => '7', '6.0' => 'Vista',
        '5.2' => 'XP', '5.1' => 'XP', '5.01' => '2000', '5.0' => '2000', '4.0' => 'NT 4.0',
    ];

    /** @var list<array{0: string, 1: string}>|null */
    private static ?array $rules = null;

    /**
     * @return array{name: string, version: string|null}|null
     */
    public static function match(string $userAgent): ?array
    {
        foreach (self::rules() as [$name, $pattern]) {
            if (! preg_match('~'.$pattern.'~i', $userAgent, $match)) {
                continue;
            }

            $version = Version::firstGroup($match);

            return match ($name) {
                'Windows' => ['name' => 'Windows', 'version' => self::windows($version, $userAgent)],
                'iOS' => self::ios($version, $userAgent),
                // Chrome's reduced User-Agent freezes Android at "10" and the model at "K".
                'Android' => ['name' => 'Android', 'version' => preg_match('~Android 10; K\)~', $userAgent) ? null : $version],
                default => ['name' => $name, 'version' => $version],
            };
        }

        return null;
    }

    /**
     * iPads moved to "iPadOS" with version 13. Since iOS 26, Safari freezes
     * the system version at 18.6 while Version/ carries the real one.
     *
     * @return array{name: string, version: string|null}
     */
    private static function ios(?string $version, string $userAgent): array
    {
        if ($version !== null && str_starts_with($version, '18.6')
            && preg_match('~Version/(\d+(?:\.\d+)*)~', $userAgent, $match) && (int) $match[1] >= 26) {
            $version = $match[1];
        }

        return [
            'name' => preg_match('~iPad~i', $userAgent) && (int) $version >= 13 ? 'iPadOS' : 'iOS',
            'version' => $version,
        ];
    }

    /** The name people know a Windows version by, from "6.1" (NT) or "98". */
    public static function windowsName(?string $version): ?string
    {
        return $version === null ? null : (self::WINDOWS_NT[$version] ?? $version);
    }

    private static function windows(?string $version, string $userAgent): ?string
    {
        if ($version !== null) {
            return self::windowsName($version);
        }

        return match (true) {
            (bool) preg_match('~Win(?:dows)? ?9x 4\.90|Windows ME~i', $userAgent) => 'ME',
            (bool) preg_match('~Windows 98|Win98~i', $userAgent) => '98',
            (bool) preg_match('~Windows 95|Win95~i', $userAgent) => '95',
            (bool) preg_match('~Windows XP~i', $userAgent) => 'XP',
            default => null,
        };
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function rules(): array
    {
        return self::$rules ??= [
            ['Windows Phone', 'Windows Phone(?: OS)?[ /]?([\d.]+)?|WPDesktop|IEMobile'],
            ['Windows Mobile', 'Windows CE|Windows Mobile'],
            ['HarmonyOS', '(?:Open)?Harmony(?:OS)?(?:[ /]([\d.]+))?'],
            ['KaiOS', 'KAIOS(?:/([\d.]+))?'],
            ['Fire OS', '(?-i:\b(?:KF[A-Z]{2,}|AFT[A-Z0-9]+)\b)|Kindle|\bSilk/'],
            ['iOS', '(?:iPhone|iPad|iPod).*?OS ([\d_]+)'],
            ['iOS', '\b(?:iPhone|iPad|iPod)\b'],
            ['watchOS', 'watchOS(?:[ /]([\d.]+))?'],
            ['tvOS', 'tvOS(?:[ /]([\d.]+))?|AppleTV'],
            ['Android', 'Android(?:[ /-]?([\d.]+))?|\bAdr ([\d.]+)'],
            ['Tizen', 'Tizen[ /]?([\d.]+)?'],
            ['webOS', '(?:Web0S|webOS|hpwOS)(?:/([\d.]+))?'],
            ['BlackBerry OS', 'BlackBerry\w*/([\d.]+)'],
            ['BlackBerry OS', '(?:BlackBerry|BB10|RIM Tablet OS).*?Version/([\d.]+)'],
            ['BlackBerry OS', 'BlackBerry|BB10'],
            ['Symbian', 'Symbian(?:OS)?/([\d.]+)'],
            ['Symbian', 'Symbian|Series ?60'],
            ['PlayStation', 'PlayStation (?:\d|Vita|Portable)[^;)]*?([\d.]+)?\)'],
            ['Nintendo', 'Nintendo (?:Switch|WiiU|Wii|3DS)'],
            ['Xbox', 'Xbox(?! Series)'],
            ['Roku OS', 'Roku'],
            ['Chrome OS', 'CrOS [\w]+ ([\d.]+)'],
            ['Windows', 'Windows NT ([\d.]+)'],
            ['Windows', 'Windows (?:98|95|ME|XP)|Win(?:98|95|dows|16|32|64| ?9x)'],
            ['macOS', 'Mac OS X ([\d_.]+)'],
            ['macOS', 'Macintosh|Mac_PowerPC|Mac OS X'],
            ['Ubuntu', 'Ubuntu(?:[/ ]([\d.]+))?'],
            ['Debian', 'Debian'],
            ['Fedora', 'Fedora'],
            ['openSUSE', 'openSUSE|SUSE'],
            ['Arch Linux', 'Arch Linux'],
            ['FreeBSD', 'FreeBSD'],
            ['OpenBSD', 'OpenBSD'],
            ['NetBSD', 'NetBSD'],
            ['Solaris', 'SunOS|Solaris'],
            ['Linux', 'Linux|X11'],
        ];
    }
}
