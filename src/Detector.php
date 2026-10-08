<?php

declare(strict_types=1);

namespace Ruvelo\Device;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Request;
use Ruvelo\Device\Detection\ClientHints;
use Ruvelo\Device\Detection\Headers;
use Ruvelo\Device\Detection\Parser;

/**
 * Detects devices, remembering each answer so a User-Agent is only parsed
 * once: in memory for the life of the process (bounded, so long-running
 * workers don't grow), and optionally in a Laravel cache store across requests.
 */
final class Detector
{
    /** Bump when the detection rules change, so cached answers are recomputed. */
    public const RULES_VERSION = '1.0.0';

    private const MEMORY_LIMIT = 500;

    /** @var array<string, Device> */
    private array $memory = [];

    private readonly Parser $parser;

    public function __construct(
        private readonly ?Cache $cache = null,
        private readonly int $ttl = 604800,
        private readonly int $maxLength = 1000,
        private readonly bool $useClientHints = true,
        private readonly ?string $ipadCookie = 'device_ipad',
    ) {
        $this->parser = new Parser;
    }

    public function fromRequest(Request $request): Device
    {
        $confirmed = $this->ipadCookie !== null && $request->cookies->get($this->ipadCookie) === '1';

        return $this->detect(
            (string) $request->headers->get('User-Agent', ''),
            Headers::normalize($request->headers->all()),
            $confirmed,
        );
    }

    /**
     * @param  array<array-key, mixed>  $headers  Any shape: see Headers::normalize()
     */
    public function fromUserAgent(string $userAgent, array $headers = []): Device
    {
        return $this->detect($userAgent, Headers::normalize($headers), false);
    }

    /**
     * @param  array<array-key, mixed>  $headers  Any shape, as long as it includes User-Agent
     */
    public function fromHeaders(array $headers): Device
    {
        $headers = Headers::normalize($headers);

        return $this->detect($headers['user-agent'] ?? '', $headers, false);
    }

    /** Forget every answer held in memory. */
    public function flush(): void
    {
        $this->memory = [];
    }

    /**
     * @param  array<string, string>  $headers  Normalized
     */
    private function detect(string $userAgent, array $headers, bool $ipadConfirmed): Device
    {
        $userAgent = mb_substr(trim($userAgent), 0, $this->maxLength);

        $relevant = ['ua' => $userAgent, 'ipad' => $ipadConfirmed];
        foreach ([...($this->useClientHints ? ClientHints::HEADERS : []), 'cloudfront-is-mobile-viewer', 'cloudfront-is-tablet-viewer', 'cloudfront-is-smarttv-viewer', 'cloudfront-is-desktop-viewer'] as $name) {
            if (isset($headers[$name])) {
                $relevant[$name] = $headers[$name];
            }
        }

        $key = 'device:'.self::RULES_VERSION.':'.hash('xxh128', serialize($relevant));

        $device = $this->memory[$key] ?? null;

        if ($device === null) {
            $device = $this->remember($key, fn () => $this->parser->parse(
                $userAgent,
                $this->useClientHints ? ClientHints::fromHeaders($headers) : null,
                $ipadConfirmed,
                $headers,
            ));

            if (count($this->memory) >= self::MEMORY_LIMIT) {
                array_shift($this->memory);
            }
            $this->memory[$key] = $device;
        }

        return $device->withLanguages(Headers::languages($headers['accept-language'] ?? null));
    }

    /**
     * @param  callable(): Device  $parse
     */
    private function remember(string $key, callable $parse): Device
    {
        if ($this->cache === null) {
            return $parse();
        }

        // Stored as an array: cache stores may refuse to unserialize objects.
        $cached = $this->cache->get($key);
        if (is_array($cached) && ($device = Device::fromArray($cached)) !== null) {
            return $device;
        }

        $device = $parse();
        $this->cache->put($key, $device->toArray(), $this->ttl);

        return $device;
    }
}
