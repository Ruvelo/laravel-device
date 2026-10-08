<?php

declare(strict_types=1);

namespace Ruvelo\Device;

use Detection\MobileDetect;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Ruvelo\Device\Detection\Bots;
use Ruvelo\Device\Detection\Browsers;
use Ruvelo\Device\Detection\Headers;
use Ruvelo\Device\Exceptions\UnknownMethod;

/**
 * A drop-in replacement for Jenssegers\Agent\Agent: the same methods with the
 * same return values, so moving over is a change of import. Underneath it
 * runs this package's detection, so newer browsers, iPadOS, Edge, AI crawlers
 * and Client Hints are handled. For new code, prefer the typed Device object.
 *
 * Rule lists, version() and languages() follow jenssegers/agent by Jens
 * Segers (MIT), which this class is modelled on.
 *
 * @phpstan-consistent-constructor
 *
 * @method bool isAndroidOS()
 * @method bool isiOS()
 * @method bool isiPadOS()
 * @method bool isWindows()
 * @method bool isOSX()
 * @method bool isLinux()
 * @method bool isChromeOS()
 * @method bool isiPhone()
 * @method bool isiPad()
 * @method bool isMacintosh()
 * @method bool isSamsung()
 * @method bool isNexus()
 * @method bool isChrome()
 * @method bool isFirefox()
 * @method bool isSafari()
 * @method bool isEdge()
 * @method bool isOpera()
 * @method bool isIE()
 * @method bool isSamsungInternet()
 * @method bool isTV()
 * @method bool isConsole()
 * @method bool isWatch()
 */
class Agent
{
    public const VERSION_TYPE_STRING = 'text';

    public const VERSION_TYPE_FLOAT = 'float';

    /** @var array<string, string> */
    protected static array $desktopDevices = [
        'Macintosh' => 'Macintosh',
    ];

    /** @var array<string, string> */
    protected static array $additionalOperatingSystems = [
        'Windows' => 'Windows',
        'Windows NT' => 'Windows NT',
        'OS X' => 'Mac OS X',
        'Debian' => 'Debian',
        'Ubuntu' => 'Ubuntu',
        'Macintosh' => 'PPC',
        'OpenBSD' => 'OpenBSD',
        'Linux' => 'Linux',
        'ChromeOS' => 'CrOS',
    ];

    /** @var array<string, string> */
    protected static array $additionalBrowsers = [
        'Opera Mini' => 'Opera Mini',
        'Opera' => 'Opera|OPR',
        'Edge' => 'Edge|Edg',
        'Coc Coc' => 'coc_coc_browser',
        'UCBrowser' => 'UCBrowser',
        'Vivaldi' => 'Vivaldi',
        'Samsung Internet' => 'SamsungBrowser',
        'Brave' => 'Brave',
        'Yandex Browser' => 'YaBrowser',
        'DuckDuckGo' => 'DuckDuckGo|Ddg/',
        'Chrome' => 'Chrome',
        'Firefox' => 'Firefox',
        'Safari' => 'Safari',
        'IE' => 'MSIE|IEMobile|MSIEMobile|Trident/[.0-9]+',
        'Netscape' => 'Netscape',
        'Mozilla' => 'Mozilla',
        'WeChat' => 'MicroMessenger',
    ];

    /** @var array<string, string|list<string>> */
    protected static array $additionalProperties = [
        // Operating systems
        'Windows' => 'Windows NT [VER]',
        'Windows NT' => 'Windows NT [VER]',
        'OS X' => 'OS X [VER]',
        'BlackBerryOS' => ['BlackBerry[\w]+/[VER]', 'BlackBerry.*Version/[VER]', 'Version/[VER]'],
        'AndroidOS' => 'Android [VER]',
        'ChromeOS' => 'CrOS x86_64 [VER]',

        // Browsers
        'Opera Mini' => 'Opera Mini/[VER]',
        'Opera' => [' OPR/[VER]', 'Opera Mini/[VER]', 'Version/[VER]', 'Opera [VER]'],
        'Netscape' => 'Netscape/[VER]',
        'Mozilla' => 'rv:[VER]',
        'IE' => ['IEMobile/[VER];', 'IEMobile [VER]', 'MSIE [VER];', 'rv:[VER]'],
        'Edge' => ['Edge/[VER]', 'Edg/[VER]', 'EdgA/[VER]', 'EdgiOS/[VER]'],
        'Vivaldi' => 'Vivaldi/[VER]',
        'Coc Coc' => 'coc_coc_browser/[VER]',
        'Samsung Internet' => 'SamsungBrowser/[VER]',
        'Yandex Browser' => 'YaBrowser/[VER]',
        'DuckDuckGo' => ['DuckDuckGo/[VER]', 'Ddg/[VER]'],
        'Brave' => ['Brave/[VER]', 'Chrome/[VER]'],
        'Whale' => 'Whale/[VER]',
        'Silk' => 'Silk/[VER]',
        'Huawei Browser' => 'HuaweiBrowser/[VER]',
        'MIUI Browser' => 'MiuiBrowser/[VER]',
        'Headless Chrome' => 'HeadlessChrome/[VER]',
        'Android WebView' => 'Chrome/[VER]',
        'Chromium' => 'Chromium/[VER]',
        'Instagram' => 'Instagram [VER]',
        'Facebook' => ['FBAV/[VER]', 'FB_IAB/[VER]'],
        'iPadOS' => ['CPU OS [VER]', 'Version/[VER]'],
    ];

    /** Our browser names where jenssegers/agent used a different one. */
    private const BROWSER_NAMES = [
        'Internet Explorer' => 'IE',
        'UC Browser' => 'UCBrowser',
    ];

    /** Our operating system names where jenssegers/agent used a different one. */
    private const PLATFORM_NAMES = [
        'macOS' => 'OS X',
        'iPadOS' => 'iOS',
        'Android' => 'AndroidOS',
        'Fire OS' => 'AndroidOS',
        'Chrome OS' => 'ChromeOS',
        'BlackBerry OS' => 'BlackBerryOS',
        'Windows Phone' => 'WindowsPhoneOS',
        'Windows Mobile' => 'WindowsMobileOS',
        'Symbian' => 'SymbianOS',
    ];

    /** The headers Mobile Detect reads the User-Agent from, in order. */
    private const USER_AGENT_HEADERS = [
        'HTTP_USER_AGENT', 'HTTP_X_OPERAMINI_PHONE_UA', 'HTTP_X_DEVICE_USER_AGENT', 'HTTP_X_ORIGINAL_USER_AGENT',
        'HTTP_X_SKYFIRE_PHONE', 'HTTP_X_BOLT_PHONE_UA', 'HTTP_DEVICE_STOCK_UA', 'HTTP_X_UCBROWSER_DEVICE_UA',
    ];

    /** @var array<string, string> */
    protected array $httpHeaders = [];

    protected ?string $userAgent = null;

    protected ?string $matchingRegex = null;

    /** @var array<int|string, string>|null */
    protected ?array $matchesArray = null;

    private ?Device $device = null;

    private static ?Detector $fallbackDetector = null;

    /**
     * @param  array<array-key, mixed>|null  $headers  $_SERVER-style headers (HTTP_USER_AGENT...); the current request's when null
     */
    public function __construct(?array $headers = null, ?string $userAgent = null)
    {
        $this->setHttpHeaders($headers);
        $this->setUserAgent($userAgent);
    }

    public static function fromRequest(Request $request): static
    {
        return new static(self::serverHeaders($request));
    }

    /**
     * Set the headers to read from. Null reads the current request.
     *
     * @param  array<array-key, mixed>|null  $httpHeaders
     */
    public function setHttpHeaders(?array $httpHeaders = null): void
    {
        if ($httpHeaders === null) {
            $container = Container::getInstance();
            $httpHeaders = $container->bound('request') && ($request = $container->make('request')) instanceof Request
                ? self::serverHeaders($request)
                : $_SERVER;
        }

        $this->httpHeaders = [];
        foreach ($httpHeaders as $name => $value) {
            if (is_string($name) && is_scalar($value)) {
                $this->httpHeaders[$name] = (string) $value;
            }
        }

        $userAgent = '';
        foreach (self::USER_AGENT_HEADERS as $header) {
            if (($this->getHttpHeader($header) ?? '') !== '') {
                $userAgent .= $this->getHttpHeader($header).' ';
            }
        }

        if ($userAgent !== '') {
            $this->userAgent = trim($userAgent);
        }

        $this->device = null;
    }

    /** @return array<string, string> */
    public function getHttpHeaders(): array
    {
        return $this->httpHeaders;
    }

    /** A header by its HTTP name ("User-Agent") or $_SERVER name ("HTTP_USER_AGENT"). */
    public function getHttpHeader(string $header): ?string
    {
        if (! str_contains($header, '_')) {
            $header = strtoupper(str_replace('-', '_', $header));
        }

        return $this->httpHeaders[$header] ?? $this->httpHeaders['HTTP_'.$header] ?? null;
    }

    /**
     * Set the User-Agent to inspect. Null takes it from the headers again.
     */
    public function setUserAgent(?string $userAgent = null): ?string
    {
        if ($userAgent !== null) {
            $this->userAgent = trim($userAgent);
        } elseif ($this->userAgent === null) {
            $this->userAgent = '';
        }

        $this->device = null;

        return $this->userAgent;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    /** The typed Device behind this agent: the way forward from this class. */
    public function toDevice(): Device
    {
        return $this->device ??= $this->detect($this->userAgent ?? '');
    }

    /**
     * Phones and tablets.
     *
     * @param  array<array-key, mixed>|null  $httpHeaders  Ignored; kept for compatibility
     */
    public function isMobile(?string $userAgent = null, ?array $httpHeaders = null): bool
    {
        return $this->deviceFor($userAgent)->isMobile();
    }

    /** @param  array<array-key, mixed>|null  $httpHeaders  Ignored; kept for compatibility */
    public function isTablet(?string $userAgent = null, ?array $httpHeaders = null): bool
    {
        return $this->deviceFor($userAgent)->isTablet();
    }

    /** @param  array<array-key, mixed>|null  $httpHeaders  Ignored; kept for compatibility */
    public function isPhone(?string $userAgent = null, ?array $httpHeaders = null): bool
    {
        return $this->deviceFor($userAgent)->isPhone();
    }

    /**
     * Not a phone, tablet, robot, TV, console or watch. As in jenssegers/agent,
     * an empty or unrecognised User-Agent counts as a desktop.
     *
     * @param  array<array-key, mixed>|null  $httpHeaders  Ignored; kept for compatibility
     */
    public function isDesktop(?string $userAgent = null, ?array $httpHeaders = null): bool
    {
        $type = $this->deviceFor($userAgent)->type;

        return $type === DeviceType::Desktop || $type === DeviceType::Unknown;
    }

    public function isRobot(?string $userAgent = null): bool
    {
        return $this->deviceFor($userAgent)->isBot;
    }

    /** "desktop", "phone", "tablet", "robot" or "other". */
    public function deviceType(?string $userAgent = null): string
    {
        return match (true) {
            $this->isDesktop($userAgent) => 'desktop',
            $this->isPhone($userAgent) => 'phone',
            $this->isTablet($userAgent) => 'tablet',
            $this->isRobot($userAgent) => 'robot',
            default => 'other',
        };
    }

    /** The browser name ("Chrome", "Edge", "Safari", "IE"...), or false. */
    public function browser(?string $userAgent = null): string|false
    {
        $name = $this->deviceFor($userAgent)->browser->name;

        return $name === null ? false : (self::BROWSER_NAMES[$name] ?? $name);
    }

    /** The operating system name ("Windows", "OS X", "iOS", "AndroidOS"...), or false. */
    public function platform(?string $userAgent = null): string|false
    {
        $name = $this->deviceFor($userAgent)->os->name;

        return $name === null ? false : (self::PLATFORM_NAMES[$name] ?? $name);
    }

    /** The device name from Mobile Detect's lists ("iPhone", "iPad", "Macintosh", "Nexus"...), or false. */
    public function device(?string $userAgent = null): string|false
    {
        $rules = static::mergeRules(
            static::getDesktopDevices(),
            static::getPhoneDevices(),
            static::getTabletDevices(),
        );

        return $this->findDetectionRulesAgainstUA($rules, $userAgent);
    }

    /** The robot's name ("Googlebot", "Bingbot", "GPTBot"...), or false. */
    public function robot(?string $userAgent = null): string|false
    {
        $bot = Bots::match($userAgent ?? $this->userAgent ?? '');

        return $bot === null ? false : ucfirst($bot['token']);
    }

    /**
     * The version of a browser, operating system or device named like the
     * other methods return them: version('Chrome'), version($agent->platform()).
     */
    public function version(string $propertyName, string $type = self::VERSION_TYPE_STRING): string|float|false
    {
        if ($propertyName === '') {
            return false;
        }

        // Chromium freezes the User-Agent; Client Hints know the full version.
        $device = $this->toDevice();
        if ($device->usedClientHints && $propertyName === $this->browser() && str_contains((string) $device->browser->version, '.')
            && ! str_ends_with((string) $device->browser->version, '.0.0.0')) {
            return $this->formatVersion((string) $device->browser->version, $type);
        }

        $properties = static::getProperties();
        if (! isset($properties[$propertyName])) {
            return false;
        }

        foreach ((array) $properties[$propertyName] as $pattern) {
            $pattern = str_replace('[VER]', '([\w._\+]+)', $pattern);

            if (preg_match('#'.$pattern.'#is', (string) $this->userAgent, $match) && ! empty($match[1])) {
                return $this->formatVersion($match[1], $type);
            }
        }

        return false;
    }

    /**
     * Accept-Language as a list, most preferred first: ['nl-nl', 'nl', 'en-us', 'en'].
     *
     * @return list<string>
     */
    public function languages(?string $acceptLanguage = null): array
    {
        return Headers::languages($acceptLanguage ?? $this->getHttpHeader('HTTP_ACCEPT_LANGUAGE'));
    }

    /**
     * Check a property: is('Windows'), is('iPhone'), is('Chrome'), is('OS X').
     *
     * Browser names are exact: unlike jenssegers/agent, is('Chrome') is false
     * on Edge, Opera and Samsung Internet, and is('Safari') is false on Chrome.
     */
    public function is(string $key): bool
    {
        $device = $this->toDevice();
        $wanted = self::key($key);

        $type = match ($wanted) {
            'tv' => $device->isTv(),
            'console' => $device->isConsole(),
            'watch', 'wearable' => $device->isWearable(),
            'bot', 'robot' => $device->isBot,
            'mobile' => $device->isMobile(),
            'phone' => $device->isPhone(),
            'tablet' => $device->isTablet(),
            'desktop' => $this->isDesktop(),
            default => null,
        };

        if ($type !== null) {
            return $type;
        }

        if (in_array($wanted, self::browserKeys(), true)) {
            return self::key((string) $this->browser()) === $wanted;
        }

        // What we detected ("iPadOS", "Samsung", "Pixel 9") answers first...
        $detected = array_map(self::key(...), array_filter([
            (string) $this->platform(), $device->os->name, $device->browser->name, $device->brand, $device->model,
        ]));

        if (in_array($wanted, $detected, true)) {
            return true;
        }

        // ...then Mobile Detect's and jenssegers/agent's rules, as before.
        foreach (static::getRules() as $name => $regex) {
            if (self::key($name) === $wanted) {
                return $this->match(implode('|', (array) $regex));
            }
        }

        return false;
    }

    /** Test the User-Agent against a regular expression (without delimiters). */
    public function match(string $regex, ?string $userAgent = null): bool
    {
        $matched = (bool) preg_match('#'.$regex.'#is', $userAgent ?? (string) $this->userAgent, $matches);

        if ($matched) {
            $this->matchingRegex = $regex;
            $this->matchesArray = $matches;
        }

        return $matched;
    }

    public function getMatchingRegex(): ?string
    {
        return $this->matchingRegex;
    }

    /** @return array<int|string, string>|null */
    public function getMatchesArray(): ?array
    {
        return $this->matchesArray;
    }

    /**
     * Magic is-methods: isIphone(), isAndroidOS(), isChrome()...
     *
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): bool
    {
        if (! str_starts_with($name, 'is')) {
            throw UnknownMethod::named($name);
        }

        return $this->is(substr($name, 2));
    }

    /** @return array<string, string|list<string>> */
    public static function getBrowsers(): array
    {
        return static::mergeRules(static::$additionalBrowsers, MobileDetect::getBrowsers());
    }

    /** @return array<string, string|list<string>> */
    public static function getOperatingSystems(): array
    {
        return static::mergeRules(MobileDetect::getOperatingSystems(), static::$additionalOperatingSystems);
    }

    /** @return array<string, string|list<string>> */
    public static function getPlatforms(): array
    {
        return static::getOperatingSystems();
    }

    /** @return array<string, string> */
    public static function getDesktopDevices(): array
    {
        return static::$desktopDevices;
    }

    /** @return array<string, string|list<string>> */
    public static function getPhoneDevices(): array
    {
        return MobileDetect::getPhoneDevices();
    }

    /** @return array<string, string|list<string>> */
    public static function getTabletDevices(): array
    {
        return MobileDetect::getTabletDevices();
    }

    /** @return array<string, string|list<string>> */
    public static function getProperties(): array
    {
        return static::mergeRules(static::$additionalProperties, MobileDetect::getProperties());
    }

    /**
     * Every rule is() and the magic methods can check.
     *
     * @return array<string, string|list<string>>
     */
    public static function getRules(): array
    {
        static $rules = [];

        return $rules[static::class] ??= static::mergeRules(
            static::getDesktopDevices(),
            static::getPhoneDevices(),
            static::getTabletDevices(),
            MobileDetect::getOperatingSystems(),
            static::$additionalOperatingSystems,
            MobileDetect::getBrowsers(),
            static::$additionalBrowsers,
            ['WebKit' => '(webkit)[ /]([\w.]+)', 'DesktopMode' => 'WPDesktop'],
        );
    }

    /**
     * @param  array<array-key, mixed>  ...$all
     * @return array<string, string|list<string>>
     */
    protected static function mergeRules(array ...$all): array
    {
        $merged = [];

        foreach ($all as $rules) {
            foreach ($rules as $key => $value) {
                $key = (string) $key;
                $values = array_values(array_filter((array) $value, 'is_string'));

                if ($values === []) {
                    continue;
                }

                if (! isset($merged[$key])) {
                    $merged[$key] = is_array($value) ? $values : $values[0];
                } elseif (is_array($merged[$key])) {
                    $merged[$key] = [...$merged[$key], ...$values];
                } else {
                    $merged[$key] .= '|'.implode('|', $values);
                }
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, string|list<string>>  $rules
     */
    protected function findDetectionRulesAgainstUA(array $rules, ?string $userAgent = null): string|false
    {
        foreach ($rules as $key => $regex) {
            if ($this->match(implode('|', (array) $regex), $userAgent)) {
                return $key;
            }
        }

        return false;
    }

    private function deviceFor(?string $userAgent): Device
    {
        return $userAgent === null ? $this->toDevice() : $this->detect($userAgent);
    }

    private function detect(string $userAgent): Device
    {
        $headers = $this->httpHeaders;

        // Client Hints describe the browser that sent the headers: ignore
        // them when asked about some other User-Agent.
        if ($userAgent !== ($this->getHttpHeader('HTTP_USER_AGENT') ?? '')) {
            $headers = array_filter($headers, fn (string $name) => ! str_contains(strtoupper($name), 'SEC_CH_UA'), ARRAY_FILTER_USE_KEY);
        }

        return self::detector()->fromUserAgent($userAgent, $headers);
    }

    private function formatVersion(string $version, string $type): string|float
    {
        if ($type !== self::VERSION_TYPE_FLOAT) {
            return $version;
        }

        $parts = explode('.', str_replace(['_', ' ', '/'], '.', $version), 2);

        return (float) ($parts[0].(isset($parts[1]) ? '.'.str_replace('.', '', $parts[1]) : ''));
    }

    private static function detector(): Detector
    {
        $container = Container::getInstance();

        if ($container->bound(Detector::class)) {
            $detector = $container->make(Detector::class);

            if ($detector instanceof Detector) {
                return $detector;
            }
        }

        return self::$fallbackDetector ??= new Detector;
    }

    /** @return list<string> */
    private static function browserKeys(): array
    {
        static $keys = null;

        return $keys ??= array_values(array_unique(array_map(
            fn (array $rule) => self::key(self::BROWSER_NAMES[$rule[0]] ?? $rule[0]),
            Browsers::rules(),
        )));
    }

    private static function key(string $name): string
    {
        return strtolower(str_replace(' ', '', $name));
    }

    /**
     * A request's headers the way $_SERVER holds them.
     *
     * @return array<string, string>
     */
    private static function serverHeaders(Request $request): array
    {
        $headers = [];

        foreach ($request->headers->all() as $name => $values) {
            $value = is_array($values) ? reset($values) : $values;

            if (is_string($value)) {
                $headers['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
            }
        }

        return $headers;
    }
}
