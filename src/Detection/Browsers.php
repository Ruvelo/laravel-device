<?php

declare(strict_types=1);

namespace Ruvelo\Device\Detection;

/**
 * Browsers and in-app browsers, most specific first.
 *
 * Nearly every browser claims to be several others ("Mozilla/5.0 ...
 * AppleWebKit ... Chrome/130 ... Safari/537.36 Edg/130"), so the order is
 * the whole trick: a rebranded Chromium (Edge, Opera, Samsung Internet)
 * must be tested before Chrome, Chrome before Safari, and so on.
 * Each rule is [name, pattern]; the first non-empty capture group is the version.
 */
final class Browsers
{
    /** Browsers built on Chromium that keep Chrome's token in their User-Agent. */
    public const CHROMIUM_BASED = [
        'Edge', 'Opera', 'Samsung Internet', 'Yandex Browser', 'UC Browser', 'Vivaldi', 'Brave',
        'DuckDuckGo', 'Whale', 'Coc Coc', 'Huawei Browser', 'MIUI Browser', 'QQ Browser', 'Silk',
        'Android WebView', 'Headless Chrome', 'Chromium', 'Chrome', 'Opera Mini',
    ];

    /** @var list<array{0: string, 1: string}>|null */
    private static ?array $rules = null;

    /**
     * @return array{name: string, version: string|null}|null
     */
    public static function match(string $userAgent): ?array
    {
        foreach (self::rules() as [$name, $pattern]) {
            if (preg_match('~'.$pattern.'~i', $userAgent, $match)) {
                return ['name' => $name, 'version' => Version::firstGroup($match)];
            }
        }

        return null;
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function rules(): array
    {
        return self::$rules ??= [
            // Chromium-based browsers that identify themselves.
            ['Edge', '\b(?:Edg|EdgA|EdgiOS|Edge)/([\d.]+)'],
            ['Opera Mini', 'Opera Mini/([\d.]+)'],
            ['Opera', '\b(?:OPR|OPX|OPT|OPiOS|OPRGX)/([\d.]+)'],
            ['Opera', '^Opera/.*Version/([\d.]+)'],
            ['Opera', '\bOpera[ /]([\d.]+)'],
            ['Samsung Internet', 'SamsungBrowser/([\d.]+)'],
            ['Yandex Browser', '(?:YaBrowser|YaSearchBrowser)/([\d.]+)'],
            ['UC Browser', 'UC ?Browser/([\d.]+)|UCWEB/?([\d.]+)?'],
            ['Vivaldi', 'Vivaldi/([\d.]+)'],
            ['Brave', '\bBrave/([\d.]+)'],
            ['DuckDuckGo', '(?:DuckDuckGo|Ddg)/([\d.]+)'],
            ['Whale', '\bWhale/([\d.]+)'],
            ['Coc Coc', 'coc_coc_browser/([\d.]+)'],
            ['Huawei Browser', 'HuaweiBrowser/([\d.]+)'],
            ['MIUI Browser', 'MiuiBrowser/([\d.]+)'],
            ['QQ Browser', 'M?QQBrowser/([\d.]+)'],
            ['Silk', '\bSilk/([\d.]+)'],

            // In-app browsers: links opened inside a social or chat app.
            ['WeChat', 'MicroMessenger/([\d.]+)'],
            ['Facebook', '\bFB(?:AV|_IAB)/([\d.]+)|\bFBAN/'],
            ['Instagram', '\bInstagram ([\d.]+)'],
            ['TikTok', '\b(?:musical_ly|TikTok)[_ /]([\d.]+)|BytedanceWebview'],
            ['Snapchat', '\bSnapchat/([\d.]+)'],
            ['LinkedIn', '\bLinkedInApp(?:/([\d.]+))?'],
            ['Line', '\bLine/([\d.]+)'],

            // The big ones.
            ['Firefox', '\b(?:Firefox|FxiOS)/([\d.]+)'],
            ['Chrome', '\bCriOS/([\d.]+)'],
            ['Headless Chrome', 'HeadlessChrome/([\d.]+)'],
            ['Android WebView', '; wv\).*?Chrome/([\d.]+)'],
            ['Chromium', '\bChromium/([\d.]+)'],
            ['Chrome', '\b(?:Chrome|CrMo)/([\d.]+)'],
            ['Internet Explorer', 'MSIE ([\d.]+)|IEMobile[/ ]([\d.]+)|Trident/.*?rv:([\d.]+)'],
            ['BlackBerry Browser', '(?:BlackBerry|BB10).*?Version/([\d.]+)'],
            ['Android Browser', 'Android.*?Version/([\d.]+).*?Safari/'],
            ['Netscape', '\bNetscape\d?/([\d.]+)'],
            ['Safari', 'Version/([\d.]+).*?Safari/'],
            ['Safari', '(?:iPhone|iPad|iPod).*?Version/([\d.]+).*?Mobile/'],
            ['Safari', '^Mozilla/5\.0 \(Macintosh;.*?AppleWebKit/[\d.]+ \(KHTML, like Gecko\)$'],
            ['iOS WebView', '(?:iPhone|iPad|iPod).*?AppleWebKit/.*?Mobile/\w+$'],
            ['Mozilla', '^Mozilla/.*?\brv:([\d.]+)\) Gecko/\d+$'],
        ];
    }
}
