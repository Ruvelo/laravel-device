<?php

declare(strict_types=1);

namespace Ruvelo\Device;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use JsonSerializable;

/**
 * Everything the package could tell about one visitor. Immutable.
 *
 *     $device = $request->device();            // or Device::fromRequest($request)
 *     $device = Device::fromUserAgent($ua);    // any string, e.g. from a log
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class Device implements Arrayable, JsonSerializable
{
    /**
     * @param  list<string>  $languages  From Accept-Language, most preferred first, lowercased
     */
    public function __construct(
        public DeviceType $type,
        public Browser $browser,
        public OperatingSystem $os,
        public Engine $engine,
        public ?string $brand = null,
        public ?string $model = null,
        public bool $isBot = false,
        public ?string $botName = null,
        public ?BotCategory $botCategory = null,
        public array $languages = [],
        public string $userAgent = '',
        public bool $usedClientHints = false,
        public bool $mightBeIpad = false,
    ) {}

    /** The device behind a request: the current one when none is given. */
    public static function fromRequest(?Request $request = null): self
    {
        $request ??= app('request');

        return app(Detector::class)->fromRequest($request);
    }

    /**
     * Parse any User-Agent string, optionally with request headers
     * (Sec-CH-UA-*, Accept-Language...) in any common shape.
     *
     * @param  array<string, mixed>  $headers
     */
    public static function fromUserAgent(string $userAgent, array $headers = []): self
    {
        return app(Detector::class)->fromUserAgent($userAgent, $headers);
    }

    /** Phones and tablets. */
    public function isMobile(): bool
    {
        return $this->type->isMobile();
    }

    public function isPhone(): bool
    {
        return $this->type === DeviceType::Phone;
    }

    public function isTablet(): bool
    {
        return $this->type === DeviceType::Tablet;
    }

    public function isDesktop(): bool
    {
        return $this->type === DeviceType::Desktop;
    }

    public function isTv(): bool
    {
        return $this->type === DeviceType::Tv;
    }

    public function isConsole(): bool
    {
        return $this->type === DeviceType::Console;
    }

    public function isWearable(): bool
    {
        return $this->type === DeviceType::Wearable;
    }

    /** True for bots in any of the three AI categories. */
    public function isAiBot(): bool
    {
        return $this->botCategory?->isAi() ?? false;
    }

    /**
     * True when the device is any of the given types. Accepts DeviceType cases
     * or their values, plus "mobile" for phones and tablets.
     */
    public function is(DeviceType|string ...$types): bool
    {
        foreach ($types as $type) {
            $resolved = $type instanceof DeviceType ? [$type] : DeviceType::resolve($type);

            if (in_array($this->type, $resolved, true)) {
                return true;
            }
        }

        return false;
    }

    /** The visitor's preferred language ("fr-be"), or null when they sent none. */
    public function language(): ?string
    {
        return $this->languages[0] ?? null;
    }

    /** @param  list<string>  $languages */
    public function withLanguages(array $languages): self
    {
        return new self(
            $this->type, $this->browser, $this->os, $this->engine, $this->brand, $this->model,
            $this->isBot, $this->botName, $this->botCategory, $languages, $this->userAgent,
            $this->usedClientHints, $this->mightBeIpad,
        );
    }

    /**
     * @return array{type: string, browser: array{name: string|null, version: string|null}, os: array{name: string|null, version: string|null}, engine: array{name: string|null, version: string|null}, brand: string|null, model: string|null, is_bot: bool, bot: array{name: string|null, category: string|null}|null, languages: list<string>, used_client_hints: bool, might_be_ipad: bool, user_agent: string}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'browser' => $this->browser->toArray(),
            'os' => $this->os->toArray(),
            'engine' => $this->engine->toArray(),
            'brand' => $this->brand,
            'model' => $this->model,
            'is_bot' => $this->isBot,
            'bot' => $this->isBot ? ['name' => $this->botName, 'category' => $this->botCategory?->value] : null,
            'languages' => $this->languages,
            'used_client_hints' => $this->usedClientHints,
            'might_be_ipad' => $this->mightBeIpad,
            'user_agent' => $this->userAgent,
        ];
    }

    /**
     * Rebuild a Device from toArray() output. Null when the array isn't one.
     *
     * @param  array<array-key, mixed>  $data
     */
    public static function fromArray(array $data): ?self
    {
        $type = DeviceType::tryFrom(is_string($data['type'] ?? null) ? $data['type'] : '');
        if ($type === null) {
            return null;
        }

        $string = fn (mixed $value): ?string => is_string($value) ? $value : null;
        $pair = function (mixed $value) use ($string): array {
            $value = is_array($value) ? $value : [];

            return [$string($value['name'] ?? null), $string($value['version'] ?? null)];
        };
        $bot = is_array($data['bot'] ?? null) ? $data['bot'] : [];
        $languages = is_array($data['languages'] ?? null) ? array_values(array_filter($data['languages'], 'is_string')) : [];

        return new self(
            type: $type,
            browser: new Browser(...$pair($data['browser'] ?? null)),
            os: new OperatingSystem(...$pair($data['os'] ?? null)),
            engine: new Engine(...$pair($data['engine'] ?? null)),
            brand: $string($data['brand'] ?? null),
            model: $string($data['model'] ?? null),
            isBot: ($data['is_bot'] ?? false) === true,
            botName: $string($bot['name'] ?? null),
            botCategory: BotCategory::tryFrom($string($bot['category'] ?? null) ?? ''),
            languages: $languages,
            userAgent: $string($data['user_agent'] ?? null) ?? '',
            usedClientHints: ($data['used_client_hints'] ?? false) === true,
            mightBeIpad: ($data['might_be_ipad'] ?? false) === true,
        );
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
