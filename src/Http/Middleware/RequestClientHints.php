<?php

declare(strict_types=1);

namespace Ruvelo\Device\Http\Middleware;

use Closure;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Asks Chromium browsers for the Client Hints that aren't sent by default
 * (platform version, model, full browser version, form factor) with an
 * Accept-CH header. Browsers send them from the next request on; list a hint
 * in Critical-CH as well to have the browser retry the first request with it.
 *
 * Browsers only honour Accept-CH over HTTPS (and on localhost).
 */
final class RequestClientHints
{
    public function __construct(private readonly Config $config) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$hints): Response
    {
        $response = $next($request);

        $requested = $hints !== [] ? $hints : $this->list('device.client_hints.request');
        $critical = $this->list('device.client_hints.critical');

        if ($requested !== []) {
            $response->headers->set('Accept-CH', implode(', ', array_unique([...$requested, ...$critical])));
        }

        if ($critical !== []) {
            $response->headers->set('Critical-CH', implode(', ', $critical));
        }

        return $response;
    }

    /** @return list<string> */
    private function list(string $key): array
    {
        $value = $this->config->get($key, []);

        return is_array($value) ? array_values(array_filter($value, 'is_string')) : [];
    }
}
