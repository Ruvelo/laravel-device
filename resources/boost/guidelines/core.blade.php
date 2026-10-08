## ruvelo/laravel-device

Detects the visitor's device type, browser, operating system, engine, brand/model and bots (including AI crawlers) from the User-Agent and, when present, User-Agent Client Hints (`Sec-CH-UA-*`). It replaces `jenssegers/agent`: `Ruvelo\Device\Agent` and the `Agent` facade keep that package's method names and return values.

### Reading the device

Prefer the typed, immutable `Ruvelo\Device\Device` in new code. Never parse `$request->userAgent()` with your own regular expressions.

@verbatim
<code-snippet name="The current visitor" lang="php">
use Ruvelo\Device\Device;
use Ruvelo\Device\DeviceType;

$device = $request->device();               // Request macro; or Device::fromRequest($request)

$device->type;                              // DeviceType::Phone|Tablet|Desktop|Tv|Console|Wearable|Bot|Unknown
$device->isMobile();                        // phones and tablets
$device->is('tv', 'console');               // any of these types ("mobile" also works)
$device->browser->name;                     // "Chrome", "Edge", "Safari", "Samsung Internet"... or null
$device->browser->major();                  // 141 (int|null)
$device->os->name;                          // "Windows", "macOS", "iOS", "iPadOS", "Android"... or null
$device->os->atLeast(17);                   // compare major versions
$device->brand; $device->model;             // "Samsung", "SM-S938B" when knowable, else null
$device->isBot; $device->botName;           // true, "GPTBot"
$device->botCategory;                       // BotCategory::AiCrawler, Search, Social, Tool...
$device->isAiBot();                         // AiCrawler, AiSearch or AiAssistant
$device->languages;                         // ['fr-be', 'fr', 'en'] from Accept-Language
</code-snippet>
@endverbatim

- Parse a stored string with `Device::fromUserAgent($ua, $headers = [])`; headers may be `['Sec-CH-UA' => ...]` or `$_SERVER` style.
- `$device->isDesktop()` is false for TVs, consoles, bots and unknown devices; check `$device->type` when that matters.
- `$device->mightBeIpad` is true for Safari on a "Mac": iPads ask for desktop sites with a Mac User-Agent. Put `@detectIpad` in the layout's `<head>` so later requests from iPads are recognised (it sets the `device_ipad` cookie, which the package excludes from cookie encryption).
- To block or allow AI crawlers, branch on `$device->botCategory` (training crawlers are `BotCategory::AiCrawler`; fetches made for a person are `BotCategory::AiAssistant`), not on the bot's name.

### Blade

@verbatim
<code-snippet name="Directives" lang="blade">
@mobile <a href="/app">Get the app</a> @endmobile   {{-- phones and tablets --}}
@phone ... @endphone  @tablet ... @endtablet  @desktop ... @enddesktop
@bot ... @endbot  @unlessbot ... @endbot
@device('tv', 'console') ... @enddevice
@detectIpad   {{-- once, in the <head> --}}
</code-snippet>
@endverbatim

Middleware aliases: `device.share` shares `$device` with every view; `device.hints` sends `Accept-CH` so Chromium browsers send the high-entropy hints (platform version, model, full version, form factor) from the next HTTPS request on.

### Code written for jenssegers/agent

Change `Jenssegers\Agent\Agent` to `Ruvelo\Device\Agent` (and the facade to `Ruvelo\Device\Facades\Agent`); everything else keeps working, including magic `isIphone()`/`isAndroidOS()` calls. Browser checks are exact: `isChrome()` is false on Edge, Opera and Samsung Internet. Get the typed object with `$agent->toDevice()`.

### Tests

Use the real strings in `Ruvelo\Device\Testing\UserAgents` rather than inventing User-Agents:

@verbatim
<code-snippet name="Pretend to be a phone" lang="php">
use Ruvelo\Device\Testing\UserAgents;

$this->withHeader('User-Agent', UserAgents::IPHONE)->get('/')->assertSee('Get the app');
$this->withHeader('User-Agent', UserAgents::GPTBOT)->get('/')->assertForbidden();
</code-snippet>
@endverbatim

Config (`config/device.php`, publish with `--tag=device-config`): `client_hints.read`, `client_hints.request`, `client_hints.critical`, `cache.store`, `cache.ttl`, `ipad_cookie`, `max_length`, `blade_directives`, `request_macro`. Exceptions implement `Ruvelo\Device\Exceptions\DeviceException`.
