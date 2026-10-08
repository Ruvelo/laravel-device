<?php

declare(strict_types=1);

namespace Ruvelo\Device;

use JsonSerializable;
use Stringable;

/**
 * A browser, or the app that embeds one (Chrome, Safari, Instagram...).
 */
final readonly class Browser implements JsonSerializable, Stringable
{
    use Concerns\HasNameAndVersion;

    public function __construct(
        public ?string $name = null,
        public ?string $version = null,
    ) {}
}
