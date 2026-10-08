<?php

declare(strict_types=1);

namespace Ruvelo\Device;

use Ruvelo\Device\Exceptions\InvalidDeviceType;

/**
 * What kind of device sent the request.
 */
enum DeviceType: string
{
    case Desktop = 'desktop';
    case Phone = 'phone';
    case Tablet = 'tablet';
    case Tv = 'tv';
    case Console = 'console';
    case Wearable = 'wearable';
    case Bot = 'bot';
    case Unknown = 'unknown';

    /**
     * Accepts a case value ("phone") or one of the friendly group names
     * "mobile" (phones and tablets) and "robot". Throws on anything else.
     *
     * @return list<self>
     */
    public static function resolve(string $name): array
    {
        $name = strtolower(trim($name));

        return match ($name) {
            'mobile' => [self::Phone, self::Tablet],
            'robot', 'crawler' => [self::Bot],
            default => [self::tryFrom($name) ?? throw InvalidDeviceType::named($name)],
        };
    }

    /** Phones and tablets: what jenssegers/agent called "mobile". */
    public function isMobile(): bool
    {
        return $this === self::Phone || $this === self::Tablet;
    }

    public function label(): string
    {
        return match ($this) {
            self::Desktop => 'Desktop',
            self::Phone => 'Phone',
            self::Tablet => 'Tablet',
            self::Tv => 'TV',
            self::Console => 'Games console',
            self::Wearable => 'Wearable',
            self::Bot => 'Bot',
            self::Unknown => 'Unknown',
        };
    }
}
