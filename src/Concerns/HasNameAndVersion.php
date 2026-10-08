<?php

declare(strict_types=1);

namespace Ruvelo\Device\Concerns;

/**
 * @property-read string|null $name
 * @property-read string|null $version
 */
trait HasNameAndVersion
{
    /** True when it was recognised at all. */
    public function known(): bool
    {
        return $this->name !== null;
    }

    /** The major version as a number: 17 for "17.4.1", null when unknown. */
    public function major(): ?int
    {
        if ($this->version === null || ! preg_match('/^\d+/', $this->version, $match)) {
            return null;
        }

        return (int) $match[0];
    }

    /** Case-insensitive name check: $device->browser->is('chrome'). */
    public function is(string $name): bool
    {
        return $this->name !== null && strcasecmp($this->name, $name) === 0;
    }

    /** True when the major version is at least $major: $device->os->atLeast(17). */
    public function atLeast(int $major): bool
    {
        $current = $this->major();

        return $current !== null && $current >= $major;
    }

    /** "Chrome 130", "macOS 14.5", or "" when unknown. Trailing ".0"s are dropped. */
    public function __toString(): string
    {
        if ($this->name === null) {
            return '';
        }

        $version = $this->version === null ? '' : (string) preg_replace('/(?<=\d)(\.0)+$/', '', $this->version);

        return trim($this->name.' '.$version);
    }

    /** @return array{name: string|null, version: string|null} */
    public function toArray(): array
    {
        return ['name' => $this->name, 'version' => $this->version];
    }

    /** @return array{name: string|null, version: string|null} */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
