<?php

declare(strict_types=1);

namespace Ruvelo\Device\Detection;

/** Small helpers for the version strings found in User-Agents and Client Hints. */
final class Version
{
    /**
     * The first non-empty capture group of a preg match, with "_" read as ".".
     *
     * @param  array<int|string, string>  $match
     */
    public static function firstGroup(array $match): ?string
    {
        foreach ($match as $key => $value) {
            if ($key !== 0 && is_int($key) && $value !== '') {
                return self::clean($value);
            }
        }

        return null;
    }

    /** "10_15_7" → "10.15.7"; trims stray dots; null when nothing is left. */
    public static function clean(?string $version): ?string
    {
        if ($version === null) {
            return null;
        }

        $version = trim(str_replace('_', '.', $version), ". \t\"");

        return $version === '' ? null : $version;
    }

    /** "14.0.0" → "14", "15.1.0" → "15.1": for Client Hints, which always send three parts. */
    public static function trimZeros(?string $version): ?string
    {
        $version = self::clean($version);

        return $version === null ? null : (string) preg_replace('/(?<=\d)(\.0)+$/', '', $version);
    }
}
