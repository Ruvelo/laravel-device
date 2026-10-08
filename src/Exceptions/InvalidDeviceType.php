<?php

declare(strict_types=1);

namespace Ruvelo\Device\Exceptions;

use InvalidArgumentException;
use Ruvelo\Device\DeviceType;

final class InvalidDeviceType extends InvalidArgumentException implements DeviceException
{
    public static function named(string $name): self
    {
        $valid = implode(', ', array_map(fn (DeviceType $type) => $type->value, DeviceType::cases()));

        return new self("\"{$name}\" is not a device type. Use one of: {$valid}, or mobile.");
    }
}
