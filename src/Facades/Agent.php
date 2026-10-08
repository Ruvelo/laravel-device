<?php

declare(strict_types=1);

namespace Ruvelo\Device\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Drop-in for Jenssegers\Agent\Facades\Agent, bound to the current request.
 *
 * @method static bool isMobile(?string $userAgent = null, ?array<array-key, mixed> $httpHeaders = null)
 * @method static bool isTablet(?string $userAgent = null, ?array<array-key, mixed> $httpHeaders = null)
 * @method static bool isPhone(?string $userAgent = null, ?array<array-key, mixed> $httpHeaders = null)
 * @method static bool isDesktop(?string $userAgent = null, ?array<array-key, mixed> $httpHeaders = null)
 * @method static bool isRobot(?string $userAgent = null)
 * @method static string deviceType(?string $userAgent = null)
 * @method static string|false browser(?string $userAgent = null)
 * @method static string|false platform(?string $userAgent = null)
 * @method static string|false device(?string $userAgent = null)
 * @method static string|false robot(?string $userAgent = null)
 * @method static string|float|false version(string $propertyName, string $type = 'text')
 * @method static list<string> languages(?string $acceptLanguage = null)
 * @method static bool is(string $key)
 * @method static bool match(string $regex, ?string $userAgent = null)
 * @method static string|null setUserAgent(?string $userAgent = null)
 * @method static string|null getUserAgent()
 * @method static void setHttpHeaders(?array<array-key, mixed> $httpHeaders = null)
 * @method static array<string, string> getHttpHeaders()
 * @method static string|null getHttpHeader(string $header)
 * @method static \Ruvelo\Device\Device toDevice()
 *
 * @see \Ruvelo\Device\Agent
 */
final class Agent extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Ruvelo\Device\Agent::class;
    }
}
