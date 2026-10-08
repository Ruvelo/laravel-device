<?php

declare(strict_types=1);

namespace Ruvelo\Device;

use JsonSerializable;
use Stringable;

/**
 * A rendering engine (Blink, WebKit, Gecko...).
 */
final readonly class Engine implements JsonSerializable, Stringable
{
    use Concerns\HasNameAndVersion;

    public function __construct(
        public ?string $name = null,
        public ?string $version = null,
    ) {}
}
