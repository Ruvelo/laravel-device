<?php

declare(strict_types=1);

namespace Ruvelo\Device\Exceptions;

use BadMethodCallException;

final class UnknownMethod extends BadMethodCallException implements DeviceException
{
    public static function named(string $method): self
    {
        return new self("No such method exists: {$method}. Magic methods must start with \"is\", like isIphone() or isChrome().");
    }
}
