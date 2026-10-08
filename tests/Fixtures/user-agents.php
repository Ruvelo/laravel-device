<?php

declare(strict_types=1);

/*
 * Real User-Agent strings and what the package should make of them.
 *
 * Each entry: [User-Agent, expected]. Only the keys given are checked:
 * type, browser, browser_version, os, os_version, engine, brand, model, bot, category.
 * A null value asserts that nothing was detected.
 */

return [
    // --- Desktop: Windows ---------------------------------------------------
    'Chrome on Windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', [
        'type' => 'desktop', 'browser' => 'Chrome', 'browser_version' => '141.0.0.0', 'os' => 'Windows', 'os_version' => '10', 'engine' => 'Blink', 'bot' => null,
    ]],
    'Edge on Windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', [
        'type' => 'desktop', 'browser' => 'Edge', 'browser_version' => '141.0.0.0', 'os' => 'Windows', 'engine' => 'Blink',
    ]],
    'Legacy Edge (EdgeHTML)' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.19045', [
        'type' => 'desktop', 'browser' => 'Edge', 'browser_version' => '18.19045', 'engine' => 'EdgeHTML',
    ]],
    'Firefox on Windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', [
        'type' => 'desktop', 'browser' => 'Firefox', 'browser_version' => '143.0', 'os' => 'Windows', 'engine' => 'Gecko', 'engine_version' => '143.0',
    ]],
    'Opera on Windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', [
        'type' => 'desktop', 'browser' => 'Opera', 'browser_version' => '124.0.0.0', 'engine' => 'Blink',
    ]],
    'Vivaldi on Windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Vivaldi/7.6.3797.52', [
        'type' => 'desktop', 'browser' => 'Vivaldi', 'browser_version' => '7.6.3797.52',
    ]],
    'Brave on Windows (identical to Chrome)' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', [
        'type' => 'desktop', 'browser' => 'Chrome',
    ]],
    'Yandex Browser' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 YaBrowser/25.8.0.0 Safari/537.36', [
        'type' => 'desktop', 'browser' => 'Yandex Browser', 'browser_version' => '25.8.0.0',
    ]],
    'Whale' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Whale/4.33.325.17 Safari/537.36', [
        'browser' => 'Whale',
    ]],
    'Internet Explorer 11 on Windows 8.1' => ['Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko', [
        'type' => 'desktop', 'browser' => 'Internet Explorer', 'browser_version' => '11.0', 'os' => 'Windows', 'os_version' => '8.1', 'engine' => 'Trident',
    ]],
    'Internet Explorer 9 on Windows 7' => ['Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)', [
        'type' => 'desktop', 'browser' => 'Internet Explorer', 'browser_version' => '9.0', 'os_version' => '7',
    ]],
    'Chrome on Windows 7' => ['Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', [
        'os' => 'Windows', 'os_version' => '7',
    ]],
    'Windows XP' => ['Mozilla/5.0 (Windows NT 5.1; rv:52.0) Gecko/20100101 Firefox/52.0', [
        'os' => 'Windows', 'os_version' => 'XP',
    ]],

    // --- Desktop: Apple, Linux, ChromeOS ------------------------------------
    'Safari on macOS' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Safari/605.1.15', [
        'type' => 'desktop', 'browser' => 'Safari', 'browser_version' => '26.0', 'os' => 'macOS', 'os_version' => '10.15.7', 'engine' => 'WebKit', 'brand' => 'Apple', 'model' => 'Mac',
    ]],
    'Chrome on macOS' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', [
        'type' => 'desktop', 'browser' => 'Chrome', 'os' => 'macOS', 'engine' => 'Blink',
    ]],
    'Firefox on macOS' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:143.0) Gecko/20100101 Firefox/143.0', [
        'type' => 'desktop', 'browser' => 'Firefox', 'os' => 'macOS', 'os_version' => '10.15', 'engine' => 'Gecko',
    ]],
    'Edge on macOS' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', [
        'type' => 'desktop', 'browser' => 'Edge', 'os' => 'macOS',
    ]],
    'Firefox on Ubuntu' => ['Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:143.0) Gecko/20100101 Firefox/143.0', [
        'type' => 'desktop', 'browser' => 'Firefox', 'os' => 'Ubuntu',
    ]],
    'Firefox on Linux' => ['Mozilla/5.0 (X11; Linux x86_64; rv:143.0) Gecko/20100101 Firefox/143.0', [
        'type' => 'desktop', 'browser' => 'Firefox', 'os' => 'Linux',
    ]],
    'Chrome on Linux' => ['Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', [
        'type' => 'desktop', 'browser' => 'Chrome', 'os' => 'Linux',
    ]],
    'Chrome on Fedora' => ['Mozilla/5.0 (X11; Fedora; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', [
        'os' => 'Fedora',
    ]],
    'ChromeOS' => ['Mozilla/5.0 (X11; CrOS x86_64 14541.0.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', [
        'type' => 'desktop', 'browser' => 'Chrome', 'os' => 'Chrome OS', 'os_version' => '14541.0.0',
    ]],
    'FreeBSD' => ['Mozilla/5.0 (X11; FreeBSD amd64; rv:140.0) Gecko/20100101 Firefox/140.0', [
        'type' => 'desktop', 'os' => 'FreeBSD',
    ]],

    // --- iOS and iPadOS -------------------------------------------------------
    'Safari on iPhone (iOS 26)' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1', [
        'type' => 'phone', 'browser' => 'Safari', 'browser_version' => '26.0', 'os' => 'iOS', 'os_version' => '26.0', 'engine' => 'WebKit', 'brand' => 'Apple', 'model' => 'iPhone',
    ]],
    'Safari on iPhone (iOS 17)' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 17_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.7 Mobile/15E148 Safari/604.1', [
        'type' => 'phone', 'os' => 'iOS', 'os_version' => '17.7',
    ]],
    'Chrome on iPhone' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/141.0.7390.41 Mobile/15E148 Safari/604.1', [
        'type' => 'phone', 'browser' => 'Chrome', 'browser_version' => '141.0.7390.41', 'os' => 'iOS', 'engine' => 'WebKit',
    ]],
    'Firefox on iPhone' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/143.0 Mobile/15E148 Safari/605.1.15', [
        'type' => 'phone', 'browser' => 'Firefox', 'os' => 'iOS', 'engine' => 'WebKit',
    ]],
    'Edge on iPhone' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 EdgiOS/141.0.3537.71 Mobile/15E148 Safari/605.1.15', [
        'type' => 'phone', 'browser' => 'Edge', 'os' => 'iOS', 'engine' => 'WebKit',
    ]],
    'Instagram in-app on iPhone' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 Instagram 400.0.0.37.75 (iPhone15,3; iOS 18_6; en_US; en; scale=3.00; 1290x2796; 789654321)', [
        'type' => 'phone', 'browser' => 'Instagram', 'os' => 'iOS',
    ]],
    'Facebook in-app on iPhone' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/520.0.0.41.106;FBBV/765432100;FBDV/iPhone15,2;FBMD/iPhone;FBSN/iOS;FBSV/18.6;FBSS/3;FBCR/;FBID/phone;FBLC/en_US;FBOP/80]', [
        'type' => 'phone', 'browser' => 'Facebook', 'os' => 'iOS',
    ]],
    'iOS web view' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148', [
        'type' => 'phone', 'browser' => 'iOS WebView', 'os' => 'iOS',
    ]],
    'Safari on iPad (mobile site)' => ['Mozilla/5.0 (iPad; CPU OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1', [
        'type' => 'tablet', 'browser' => 'Safari', 'os' => 'iPadOS', 'os_version' => '17.5', 'brand' => 'Apple', 'model' => 'iPad',
    ]],
    'Old iPad (iOS 9)' => ['Mozilla/5.0 (iPad; CPU OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G36 Safari/601.1', [
        'type' => 'tablet', 'os' => 'iOS', 'os_version' => '9.3.5',
    ]],
    'Chrome on iPad in desktop mode' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/141.0.7390.41 Safari/605.1.15', [
        'type' => 'tablet', 'browser' => 'Chrome', 'os' => 'iPadOS', 'brand' => 'Apple', 'model' => 'iPad', 'engine' => 'WebKit',
    ]],
    'iPod touch' => ['Mozilla/5.0 (iPod touch; CPU iPhone OS 15_8 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.6 Mobile/15E148 Safari/604.1', [
        'type' => 'phone', 'model' => 'iPod touch', 'os' => 'iOS',
    ]],

    // --- Android --------------------------------------------------------------
    'Chrome on Android (reduced User-Agent)' => ['Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', [
        'type' => 'phone', 'browser' => 'Chrome', 'os' => 'Android', 'os_version' => null, 'model' => null, 'brand' => null, 'engine' => 'Blink',
    ]],
    'Chrome on Android tablet (reduced User-Agent)' => ['Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', [
        'type' => 'tablet', 'browser' => 'Chrome', 'os' => 'Android',
    ]],
    'Samsung Internet on Galaxy S25 Ultra' => ['Mozilla/5.0 (Linux; Android 15; SM-S938B) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36', [
        'type' => 'phone', 'browser' => 'Samsung Internet', 'browser_version' => '28.0', 'os' => 'Android', 'os_version' => '15', 'brand' => 'Samsung', 'model' => 'SM-S938B', 'engine' => 'Blink',
    ]],
    'Samsung Internet on Galaxy Tab' => ['Mozilla/5.0 (Linux; Android 14; SM-X710) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Safari/537.36', [
        'type' => 'tablet', 'browser' => 'Samsung Internet', 'brand' => 'Samsung', 'model' => 'SM-X710',
    ]],
    'Firefox on Android' => ['Mozilla/5.0 (Android 15; Mobile; rv:143.0) Gecko/143.0 Firefox/143.0', [
        'type' => 'phone', 'browser' => 'Firefox', 'os' => 'Android', 'os_version' => '15', 'engine' => 'Gecko',
    ]],
    'Firefox on Android tablet' => ['Mozilla/5.0 (Android 14; Tablet; rv:143.0) Gecko/143.0 Firefox/143.0', [
        'type' => 'tablet', 'browser' => 'Firefox',
    ]],
    'Opera on Android' => ['Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36 OPR/91.0.0.0', [
        'type' => 'phone', 'browser' => 'Opera',
    ]],
    'Edge on Android' => ['Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 EdgA/141.0.0.0', [
        'type' => 'phone', 'browser' => 'Edge',
    ]],
    'DuckDuckGo on Android' => ['Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/141.0.0.0 Mobile DuckDuckGo/5 Safari/537.36', [
        'type' => 'phone', 'browser' => 'DuckDuckGo',
    ]],
    'Android WebView on a Pixel' => ['Mozilla/5.0 (Linux; Android 15; Pixel 9 Build/AP3A.241005.015; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/141.0.7390.43 Mobile Safari/537.36', [
        'type' => 'phone', 'browser' => 'Android WebView', 'brand' => 'Google', 'model' => 'Pixel 9',
    ]],
    'Chrome on a Xiaomi (full User-Agent)' => ['Mozilla/5.0 (Linux; Android 13; 2201117TG) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Mobile Safari/537.36', [
        'type' => 'phone', 'brand' => 'Xiaomi', 'model' => '2201117TG', 'os_version' => '13',
    ]],
    'MIUI Browser on Redmi' => ['Mozilla/5.0 (Linux; U; Android 13; en-us; Redmi Note 12 Build/TKQ1.221114.001) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/112.0.5615.136 Mobile Safari/537.36 XiaoMi/MiuiBrowser/14.7.0-gn', [
        'type' => 'phone', 'browser' => 'MIUI Browser', 'brand' => 'Xiaomi', 'model' => 'Redmi Note 12',
    ]],
    'Motorola' => ['Mozilla/5.0 (Linux; Android 14; moto g54 5G) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Mobile Safari/537.36', [
        'type' => 'phone', 'brand' => 'Motorola', 'model' => 'moto g54 5G',
    ]],
    'OnePlus' => ['Mozilla/5.0 (Linux; Android 14; ONEPLUS A6003) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Mobile Safari/537.36', [
        'brand' => 'OnePlus',
    ]],
    'Huawei Browser' => ['Mozilla/5.0 (Linux; Android 12; HarmonyOS; NOH-NX9; HMSCore 6.13.0.302) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.5735.196 HuaweiBrowser/15.0.4.312 Mobile Safari/537.36', [
        'type' => 'phone', 'browser' => 'Huawei Browser', 'os' => 'HarmonyOS', 'brand' => 'Huawei', 'model' => 'NOH-NX9',
    ]],
    'UC Browser' => ['Mozilla/5.0 (Linux; U; Android 11; en-US; RMX2185 Build/QP1A.190711.020) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/100.0.4896.58 UCBrowser/13.4.0.1306 Mobile Safari/537.36', [
        'type' => 'phone', 'browser' => 'UC Browser', 'brand' => 'Realme', 'model' => 'RMX2185',
    ]],
    'Amazon Fire tablet (Silk)' => ['Mozilla/5.0 (Linux; Android 9; KFTRWI) AppleWebKit/537.36 (KHTML, like Gecko) Silk/124.3.2 like Chrome/124.0.6367.219 Safari/537.36', [
        'type' => 'tablet', 'browser' => 'Silk', 'os' => 'Fire OS', 'brand' => 'Amazon', 'model' => 'KFTRWI',
    ]],
    'Old Android stock browser' => ['Mozilla/5.0 (Linux; U; Android 4.0.4; en-us; GT-I9100 Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30', [
        'type' => 'phone', 'browser' => 'Android Browser', 'os_version' => '4.0.4', 'brand' => 'Samsung', 'model' => 'GT-I9100',
    ]],
    'Opera Mini on Android' => ['Opera/9.80 (Android; Opera Mini/7.6.40234/191.248; U; en) Presto/2.12.423 Version/12.16', [
        'type' => 'phone', 'browser' => 'Opera Mini', 'os' => 'Android', 'engine' => 'Presto',
    ]],
    'WeChat on Android' => ['Mozilla/5.0 (Linux; Android 13; V2227A Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/107.0.5304.141 Mobile Safari/537.36 XWEB/5235 MMWEBSDK/20230805 MMWEBID/2563 MicroMessenger/8.0.42.2460(0x28002A35) WeChat/arm64 Weixin NetType/WIFI Language/zh_CN ABI/arm64', [
        'type' => 'phone', 'browser' => 'WeChat', 'brand' => 'vivo',
    ]],

    // --- Other phones ---------------------------------------------------------
    'KaiOS feature phone' => ['Mozilla/5.0 (Mobile; Nokia 8110 4G; rv:48.0) Gecko/48.0 Firefox/48.0 KAIOS/2.5', [
        'type' => 'phone', 'os' => 'KaiOS', 'os_version' => '2.5',
    ]],
    'Windows Phone' => ['Mozilla/5.0 (Mobile; Windows Phone 8.1; Android 4.0; ARM; Trident/7.0; Touch; rv:11.0; IEMobile/11.0; NOKIA; Lumia 930) like iPhone OS 7_0_3 Mac OS X AppleWebKit/537 (KHTML, like Gecko) Mobile Safari/537', [
        'type' => 'phone', 'os' => 'Windows Phone', 'os_version' => '8.1', 'browser' => 'Internet Explorer', 'brand' => 'Nokia', 'model' => 'Lumia 930',
    ]],
    'BlackBerry 10' => ['Mozilla/5.0 (BB10; Touch) AppleWebKit/537.35+ (KHTML, like Gecko) Version/10.3.3.2205 Mobile Safari/537.35+', [
        'type' => 'phone', 'os' => 'BlackBerry OS', 'brand' => 'BlackBerry', 'browser' => 'BlackBerry Browser',
    ]],

    // --- TVs, consoles, wearables --------------------------------------------
    'Samsung Tizen TV' => ['Mozilla/5.0 (SMART-TV; LINUX; Tizen 7.0) AppleWebKit/537.36 (KHTML, like Gecko) 94.0.4606.31/7.0 TV Safari/537.36', [
        'type' => 'tv', 'os' => 'Tizen', 'brand' => 'Samsung',
    ]],
    'LG webOS TV' => ['Mozilla/5.0 (Web0S; Linux/SmartTV) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.5359.211 Safari/537.36 WebAppManager', [
        'type' => 'tv', 'os' => 'webOS', 'brand' => 'LG',
    ]],
    'Android TV (Sony Bravia)' => ['Mozilla/5.0 (Linux; Android 12; BRAVIA 4K VH22 Build/STT2.230616.003) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36', [
        'type' => 'tv', 'os' => 'Android', 'brand' => 'Sony',
    ]],
    'Chromecast with Google TV' => ['Mozilla/5.0 (Linux; Android 12.0; Build/STTL.240206.002) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 CrKey/1.56.500000 DeviceType/AndroidTV', [
        'type' => 'tv', 'brand' => 'Google',
    ]],
    'Amazon Fire TV' => ['Mozilla/5.0 (Linux; Android 9; AFTKA Build/PS7633.3445N; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/130.0.6723.102 Mobile Safari/537.36', [
        'type' => 'tv', 'brand' => 'Amazon', 'model' => 'Fire TV',
    ]],
    'Roku' => ['Roku/DVP-14.0 (14.0.4.2290-46)', [
        'type' => 'tv', 'brand' => 'Roku', 'os' => 'Roku OS',
    ]],
    'Apple TV' => ['AppleTV14,1/18.0 (com.apple.TVWebView)', [
        'type' => 'tv', 'brand' => 'Apple',
    ]],
    'PlayStation 5' => ['Mozilla/5.0 (PlayStation 5 10.40) AppleWebKit/605.1.15 (KHTML, like Gecko)', [
        'type' => 'console', 'brand' => 'Sony', 'model' => 'PlayStation 5', 'os' => 'PlayStation',
    ]],
    'PlayStation 4' => ['Mozilla/5.0 (PlayStation 4 11.00) AppleWebKit/605.1.15 (KHTML, like Gecko)', [
        'type' => 'console', 'model' => 'PlayStation 4',
    ]],
    'Xbox Series X' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64; Xbox; Xbox Series X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.82 Safari/537.36 Edge/20.02', [
        'type' => 'console', 'brand' => 'Microsoft', 'model' => 'Xbox Series X', 'browser' => 'Edge',
    ]],
    'Nintendo Switch' => ['Mozilla/5.0 (Nintendo Switch; WifiWebAuthApplet) AppleWebKit/606.4 (KHTML, like Gecko) NF/6.0.1.15.4 NintendoBrowser/5.1.0.20393', [
        'type' => 'console', 'brand' => 'Nintendo', 'model' => 'Switch',
    ]],
    'Galaxy Watch' => ['Mozilla/5.0 (Linux; Tizen 5.5; SAMSUNG SM-R820) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/1.0 Chrome/69.0.3497.106 Mobile Safari/537.36', [
        'type' => 'wearable', 'brand' => 'Samsung',
    ]],

    // --- Search engines -------------------------------------------------------
    'Googlebot' => ['Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', [
        'type' => 'bot', 'bot' => 'Googlebot', 'category' => 'search',
    ]],
    'Googlebot smartphone' => ['Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.7390.54 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', [
        'type' => 'bot', 'bot' => 'Googlebot', 'category' => 'search', 'brand' => null, 'model' => null,
    ]],
    'Bingbot' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36', [
        'type' => 'bot', 'bot' => 'Bingbot', 'category' => 'search',
    ]],
    'DuckDuckBot' => ['DuckDuckBot/1.1; (+http://duckduckgo.com/duckduckbot.html)', [
        'type' => 'bot', 'bot' => 'DuckDuckBot', 'category' => 'search',
    ]],
    'Applebot' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.1.1 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', [
        'type' => 'bot', 'bot' => 'Applebot', 'category' => 'search',
    ]],
    'YandexBot' => ['Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)', [
        'type' => 'bot', 'bot' => 'YandexBot', 'category' => 'search',
    ]],
    'Baiduspider' => ['Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)', [
        'type' => 'bot', 'bot' => 'Baiduspider',
    ]],

    // --- AI -------------------------------------------------------------------
    'GPTBot' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', [
        'type' => 'bot', 'bot' => 'GPTBot', 'category' => 'ai-crawler',
    ]],
    'ChatGPT-User' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; ChatGPT-User/1.0; +https://openai.com/bot', [
        'type' => 'bot', 'bot' => 'ChatGPT-User', 'category' => 'ai-assistant',
    ]],
    'OAI-SearchBot' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; OAI-SearchBot/1.3; +https://openai.com/searchbot', [
        'type' => 'bot', 'bot' => 'OAI-SearchBot', 'category' => 'ai-search',
    ]],
    'ClaudeBot' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ClaudeBot/1.0; +claudebot@anthropic.com)', [
        'type' => 'bot', 'bot' => 'ClaudeBot', 'category' => 'ai-crawler',
    ]],
    'Claude-User' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-User/1.0; +Claude-User@anthropic.com)', [
        'type' => 'bot', 'bot' => 'Claude-User', 'category' => 'ai-assistant',
    ]],
    'Claude-SearchBot' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-SearchBot/1.0; +Claude-SearchBot@anthropic.com)', [
        'type' => 'bot', 'bot' => 'Claude-SearchBot', 'category' => 'ai-search',
    ]],
    'PerplexityBot' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; PerplexityBot/1.0; +https://perplexity.ai/perplexitybot)', [
        'type' => 'bot', 'bot' => 'PerplexityBot', 'category' => 'ai-search',
    ]],
    'Perplexity-User' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Perplexity-User/1.0; +https://perplexity.ai/perplexity-user)', [
        'type' => 'bot', 'bot' => 'Perplexity-User', 'category' => 'ai-assistant',
    ]],
    'Google-Extended (spoofed: Google only uses it in robots.txt)' => ['Mozilla/5.0 (compatible; Google-Extended/1.0)', [
        'type' => 'bot', 'bot' => 'Google-Extended', 'category' => 'ai-crawler',
    ]],
    'Bytespider' => ['Mozilla/5.0 (Linux; Android 5.0) AppleWebKit/537.36 (KHTML, like Gecko) Mobile Safari/537.36 (compatible; Bytespider; spider-feedback@bytedance.com)', [
        'type' => 'bot', 'bot' => 'Bytespider', 'category' => 'ai-crawler',
    ]],
    'CCBot' => ['CCBot/2.0 (https://commoncrawl.org/faq/)', [
        'type' => 'bot', 'bot' => 'CCBot', 'category' => 'ai-crawler',
    ]],
    'Meta-ExternalAgent' => ['meta-externalagent/1.1 (+https://developers.facebook.com/docs/sharing/webmasters/crawler)', [
        'type' => 'bot', 'bot' => 'Meta-ExternalAgent', 'category' => 'ai-crawler',
    ]],
    'Amazonbot' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Amazonbot/0.1; +https://developer.amazon.com/support/amazonbot) Chrome/119.0.6045.214 Safari/537.36', [
        'type' => 'bot', 'bot' => 'Amazonbot', 'category' => 'ai-crawler',
    ]],
    'Applebot-Extended' => ['Mozilla/5.0 (compatible; Applebot-Extended/0.1)', [
        'type' => 'bot', 'bot' => 'Applebot-Extended', 'category' => 'ai-crawler',
    ]],
    'MistralAI-User' => ['Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; MistralAI-User/1.0; +https://docs.mistral.ai/robots)', [
        'type' => 'bot', 'bot' => 'MistralAI-User', 'category' => 'ai-assistant',
    ]],

    // --- Link previews, SEO, monitoring, tools ------------------------------
    'facebookexternalhit' => ['facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', [
        'type' => 'bot', 'bot' => 'Facebook', 'category' => 'social',
    ]],
    'Twitterbot' => ['Twitterbot/1.0', [
        'type' => 'bot', 'bot' => 'Twitterbot', 'category' => 'social',
    ]],
    'Slackbot' => ['Slackbot-LinkExpanding 1.0 (+https://api.slack.com/robots)', [
        'type' => 'bot', 'bot' => 'Slackbot', 'category' => 'social',
    ]],
    'Discordbot' => ['Mozilla/5.0 (compatible; Discordbot/2.0; +https://discordapp.com)', [
        'type' => 'bot', 'bot' => 'Discordbot', 'category' => 'social',
    ]],
    'LinkedInBot' => ['LinkedInBot/1.0 (compatible; Mozilla/5.0; Apache-HttpClient +http://www.linkedin.com)', [
        'type' => 'bot', 'bot' => 'LinkedInBot', 'category' => 'social',
    ]],
    'WhatsApp' => ['WhatsApp/2.23.20.0 A', [
        'type' => 'bot', 'bot' => 'WhatsApp', 'category' => 'social',
    ]],
    'AhrefsBot' => ['Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)', [
        'type' => 'bot', 'bot' => 'AhrefsBot', 'category' => 'seo',
    ]],
    'SemrushBot' => ['Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)', [
        'type' => 'bot', 'bot' => 'SemrushBot', 'category' => 'seo',
    ]],
    'UptimeRobot' => ['Mozilla/5.0+(compatible; UptimeRobot/2.0; http://www.uptimerobot.com/)', [
        'type' => 'bot', 'bot' => 'UptimeRobot', 'category' => 'monitoring',
    ]],
    'Lighthouse' => ['Mozilla/5.0 (Linux; Android 11; moto g power (2022)) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 Chrome-Lighthouse', [
        'type' => 'bot', 'bot' => 'Lighthouse', 'category' => 'monitoring',
    ]],
    'Feedly' => ['Feedly/1.0 (+http://www.feedly.com/fetcher.html; 12 subscribers; like FeedFetcher-Google)', [
        'type' => 'bot', 'bot' => 'Feedly', 'category' => 'feed',
    ]],
    'Headless Chrome' => ['Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/141.0.0.0 Safari/537.36', [
        'type' => 'bot', 'bot' => 'HeadlessChrome', 'category' => 'tool', 'browser' => 'Headless Chrome',
    ]],
    'curl' => ['curl/8.14.1', [
        'type' => 'bot', 'bot' => 'curl', 'category' => 'tool', 'browser' => null,
    ]],
    'python-requests' => ['python-requests/2.32.3', [
        'type' => 'bot', 'bot' => 'python-requests', 'category' => 'tool',
    ]],
    'Guzzle' => ['GuzzleHttp/7', [
        'type' => 'bot', 'bot' => 'Guzzle', 'category' => 'tool',
    ]],
    'Unknown bot that says so' => ['Mozilla/5.0 (compatible; ExampleBot/1.0; +https://example.com/bot)', [
        'type' => 'bot', 'bot' => 'ExampleBot', 'category' => 'other',
    ]],

    // --- Edge cases -----------------------------------------------------------
    'Empty' => ['', [
        'type' => 'unknown', 'browser' => null, 'os' => null, 'bot' => null,
    ]],
    'Garbage' => ['FAKE', [
        'type' => 'unknown', 'browser' => null, 'os' => null,
    ]],
    'CUBOT phone is not a bot' => ['Mozilla/5.0 (Linux; Android 11; CUBOT X50) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36', [
        'type' => 'phone', 'bot' => null,
    ]],
];
