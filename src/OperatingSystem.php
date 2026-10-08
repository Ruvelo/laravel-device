<?php

declare(strict_types=1);

namespace Ruvelo\Device;

use JsonSerializable;
use Stringable;

/**
 * An operating system (Windows, macOS, iOS, Android...).
 */
final readonly class OperatingSystem implements JsonSerializable, Stringable
{
    use Concerns\HasNameAndVersion;

    public function __construct(
        public ?string $name = null,
        public ?string $version = null,
    ) {}
}
