<?php

declare(strict_types=1);

namespace Ruvelo\Device\Detection;

/**
 * Reads headers in any of the shapes PHP code passes them around in:
 * ['User-Agent' => '...'], ['HTTP_USER_AGENT' => '...'] ($_SERVER), or
 * Symfony's ['user-agent' => ['...']]. Always returns lowercased, dashed names.
 */
final class Headers
{
    /**
     * @param  array<array-key, mixed>  $headers
     * @return array<string, string>
     */
    public static function normalize(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $value) {
            if (! is_string($name)) {
                continue;
            }

            if (is_array($value)) {
                $value = reset($value);
            }

            if (! is_scalar($value)) {
                continue;
            }

            $name = strtolower($name);
            if (str_starts_with($name, 'http_')) {
                $name = substr($name, 5);
            }

            $normalized[str_replace('_', '-', $name)] = (string) $value;
        }

        return $normalized;
    }

    /**
     * Accept-Language as a list of language tags, most preferred first:
     * "nl-NL,nl;q=0.8,en;q=0.4" → ['nl-nl', 'nl', 'en'].
     *
     * @return list<string>
     */
    public static function languages(?string $acceptLanguage): array
    {
        if ($acceptLanguage === null || trim($acceptLanguage) === '') {
            return [];
        }

        $languages = [];
        foreach (explode(',', $acceptLanguage) as $piece) {
            $parts = explode(';', $piece);
            $language = strtolower(trim($parts[0]));

            if ($language === '' || isset($languages[$language])) {
                continue;
            }

            $priority = 1.0;
            foreach (array_slice($parts, 1) as $parameter) {
                if (preg_match('/^\s*q\s*=\s*([\d.]+)/i', $parameter, $match)) {
                    $priority = (float) $match[1];
                }
            }

            $languages[$language] = $priority;
        }

        // A stable sort keeps the header's order between equal weights.
        arsort($languages);

        return array_values(array_map('strval', array_keys(array_filter($languages, fn (float $q) => $q > 0))));
    }
}
