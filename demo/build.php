<?php

declare(strict_types=1);

/*
 * Builds the demo published at https://ruvelo.github.io/laravel-device/.
 *
 * Boots a Laravel app with the package, runs ~30 real User-Agents (some with
 * Client Hints) through Device::fromUserAgent(), and renders the results with
 * a Blade view through the HTTP kernel, exactly as an app would. The "Blade"
 * section is rendered three times, as three different visitors, using the
 * package's real @mobile and @bot directives.
 *
 *   php demo/build.php <site-dir> [<shots-dir>]
 *
 * <shots-dir>, when given, receives the pages the screenshots are taken of:
 * without the demo banner, and the migration guide on its own.
 */

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Orchestra\Testbench\Foundation\Application;
use Ruvelo\Device\Device;
use Ruvelo\Device\DeviceServiceProvider;
use Ruvelo\Device\Testing\UserAgents;

require __DIR__.'/../vendor/autoload.php';

const HOST = 'https://ruvelo.github.io';
const PATH = 'laravel-device';
const REPO = 'https://github.com/Ruvelo/laravel-device';

$site = rtrim($argv[1] ?? __DIR__.'/../build/site', '/');
$shots = isset($argv[2]) ? rtrim($argv[2], '/') : null;

foreach ([
    'APP_ENV' => 'production',
    'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)),
    'APP_URL' => HOST,
    'SESSION_DRIVER' => 'array',
] as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $_SERVER[$key] = $value;
}

$app = Application::create(basePath: null, options: ['extra' => ['providers' => [DeviceServiceProvider::class]]]);
View::addNamespace('demo', __DIR__.'/views');

// --- The visitors -------------------------------------------------------------

$chrome = '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"';

$visitors = [
    ['Chrome on Windows 11', UserAgents::CHROME_WINDOWS, ['Sec-CH-UA' => $chrome, 'Sec-CH-UA-Mobile' => '?0', 'Sec-CH-UA-Platform' => '"Windows"', 'Sec-CH-UA-Platform-Version' => '"19.0.0"', 'Sec-CH-UA-Full-Version-List' => '"Google Chrome";v="141.0.7390.66", "Not?A_Brand";v="8.0.0.0", "Chromium";v="141.0.7390.66"']],
    ['Edge on Windows', UserAgents::EDGE_WINDOWS, []],
    ['Brave, which looks like Chrome', UserAgents::CHROME_WINDOWS, ['Sec-CH-UA' => '"Brave";v="141", "Not?A_Brand";v="8", "Chromium";v="141"', 'Sec-CH-UA-Platform' => '"Windows"']],
    ['Firefox on Windows', UserAgents::FIREFOX_WINDOWS, []],
    ['Safari on a Mac', UserAgents::SAFARI_MAC, []],
    ['Chrome on a Mac', UserAgents::CHROME_MAC, ['Sec-CH-UA' => $chrome, 'Sec-CH-UA-Platform' => '"macOS"', 'Sec-CH-UA-Platform-Version' => '"26.0.0"']],
    ['Firefox on Ubuntu', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:143.0) Gecko/20100101 Firefox/143.0', []],
    ['A Chromebook', 'Mozilla/5.0 (X11; CrOS x86_64 14541.0.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', []],
    ['Safari on an iPhone', UserAgents::IPHONE, []],
    ['Chrome on an iPhone', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/141.0.7390.41 Mobile/15E148 Safari/604.1', []],
    ['Instagram\'s in-app browser', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 Instagram 400.0.0.37.75 (iPhone15,3; iOS 18_6; en_US; en; scale=3.00; 1290x2796; 789654321)', []],
    ['Safari on an iPad', UserAgents::IPAD, []],
    ['Chrome on an iPad, desktop mode', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/141.0.7390.41 Safari/605.1.15', []],
    ['Chrome on a Pixel', UserAgents::CHROME_ANDROID, ['Sec-CH-UA' => $chrome, 'Sec-CH-UA-Mobile' => '?1', 'Sec-CH-UA-Platform' => '"Android"', 'Sec-CH-UA-Platform-Version' => '"16.0.0"', 'Sec-CH-UA-Model' => '"Pixel 9 Pro"']],
    ['Samsung Internet on a Galaxy', UserAgents::SAMSUNG_GALAXY, []],
    ['A Galaxy Tab', 'Mozilla/5.0 (Linux; Android 14; SM-X710) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Safari/537.36', []],
    ['Firefox on Android', 'Mozilla/5.0 (Android 15; Mobile; rv:143.0) Gecko/143.0 Firefox/143.0', []],
    ['An Amazon Fire tablet', 'Mozilla/5.0 (Linux; Android 9; KFTRWI) AppleWebKit/537.36 (KHTML, like Gecko) Silk/124.3.2 like Chrome/124.0.6367.219 Safari/537.36', []],
    ['A Huawei phone', 'Mozilla/5.0 (Linux; Android 12; HarmonyOS; NOH-NX9; HMSCore 6.13.0.302) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.5735.196 HuaweiBrowser/15.0.4.312 Mobile Safari/537.36', []],
    ['An LG TV', UserAgents::SMART_TV, []],
    ['A Samsung TV', 'Mozilla/5.0 (SMART-TV; LINUX; Tizen 7.0) AppleWebKit/537.36 (KHTML, like Gecko) 94.0.4606.31/7.0 TV Safari/537.36', []],
    ['A PlayStation 5', UserAgents::PLAYSTATION, []],
    ['An Xbox', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; Xbox; Xbox Series X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.82 Safari/537.36 Edge/20.02', []],
    ['A Nintendo Switch', 'Mozilla/5.0 (Nintendo Switch; WifiWebAuthApplet) AppleWebKit/606.4 (KHTML, like Gecko) NF/6.0.1.15.4 NintendoBrowser/5.1.0.20393', []],
    ['A Galaxy Watch', 'Mozilla/5.0 (Linux; Tizen 5.5; SAMSUNG SM-R820) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/1.0 Chrome/69.0.3497.106 Mobile Safari/537.36', []],
    ['Google, on a phone', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.7390.54 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', []],
    ['Bing', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36', []],
    ['OpenAI, training', UserAgents::GPTBOT, []],
    ['Anthropic, training', UserAgents::CLAUDEBOT, []],
    ['ChatGPT, for a person', UserAgents::CHATGPT_USER, []],
    ['Perplexity, for search', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; PerplexityBot/1.0; +https://perplexity.ai/perplexitybot)', []],
    ['ByteDance', 'Mozilla/5.0 (Linux; Android 5.0) AppleWebKit/537.36 (KHTML, like Gecko) Mobile Safari/537.36 (compatible; Bytespider; spider-feedback@bytedance.com)', []],
    ['A Slack link preview', 'Slackbot-LinkExpanding 1.0 (+https://api.slack.com/robots)', []],
    ['A curl script', UserAgents::CURL, []],
];

$rows = array_map(fn (array $visitor) => [
    'label' => $visitor[0],
    'userAgent' => $visitor[1],
    'hints' => $visitor[2],
    'device' => Device::fromUserAgent($visitor[1], $visitor[2]),
], $visitors);

// The Blade example, rendered by the real directives as three visitors.
$bladeExample = <<<'BLADE'
@mobile
    <a href="/app">Get the Halyard app</a>
@elsebot
    {{-- Crawlers get the plain page --}}
@else
    <a href="/download">Halyard for desktop</a>
@endmobile
BLADE;

$asVisitor = function (string $userAgent, callable $render) use ($app) {
    $previous = $app['request'];
    $app->instance('request', Request::create('/', 'GET', server: ['HTTP_USER_AGENT' => $userAgent]));
    try {
        return $render();
    } finally {
        $app->instance('request', $previous);
    }
};

$bladeOutputs = [];
foreach (['An iPhone' => UserAgents::IPHONE, 'A Mac' => UserAgents::SAFARI_MAC, 'GPTBot' => UserAgents::GPTBOT] as $who => $ua) {
    $html = trim((string) $asVisitor($ua, fn () => Blade::render($bladeExample)));
    $bladeOutputs[$who] = $html === '' ? null : strip_tags($html);
}

Route::middleware('web')->get('/'.PATH, fn (Request $request) => view('demo::page', [
    'rows' => $rows,
    'page' => $request->device(),
    'bladeExample' => $bladeExample,
    'bladeOutputs' => $bladeOutputs,
    'only' => $request->query('only'),
    'shot' => $request->boolean('shot'),
    'repo' => REPO,
]));

// --- Render -------------------------------------------------------------------

$http = $app->make(HttpKernel::class);

$fetch = function (string $query = '') use ($http): string {
    $request = Request::create(HOST.'/'.PATH.'/'.$query, server: [
        'HTTP_USER_AGENT' => UserAgents::CHROME_WINDOWS,
        'HTTP_ACCEPT_LANGUAGE' => 'en-GB,en;q=0.9',
    ]);
    $response = $http->handle($request);
    $http->terminate($request, $response);

    if ($response->getStatusCode() !== 200) {
        throw new RuntimeException('The demo page failed to render: '.$response->getStatusCode()."\n".$response->getContent());
    }

    return str_replace(HOST.'/', '/', (string) $response->getContent());
};

$banner = '<div class="demo-banner">'
    .'You’re looking at the demo of <a href="'.REPO.'">ruvelo/laravel-device</a>, built by the package itself. '
    .'<a href="'.REPO.'#readme">Read the docs</a></div>';

$write = function (string $root, string $path, string $html, bool $withBanner = true) use ($banner): void {
    if ($withBanner) {
        $html = preg_replace('/<body([^>]*)>/', '<body$1>'.$banner, $html, 1);
    }
    $file = $root.'/'.($path === '' ? '' : $path.'/').'index.html';
    is_dir(dirname($file)) || mkdir(dirname($file), 0777, true);
    file_put_contents($file, $html);
};

$write($site, '', $fetch());

if ($shots !== null) {
    // Screenshots are taken by a headless browser: skip the "your browser" script.
    $write($shots, 'page', $fetch('?shot=1'), false);
    $write($shots, 'migration', $fetch('?only=migration&shot=1'), false);
    $write($shots, 'table', $fetch('?only=table&shot=1'), false);
}

fwrite(STDOUT, sprintf("Built the demo (%d visitors) into %s\n", count($rows), $site));
