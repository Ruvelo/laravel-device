<?php

declare(strict_types=1);

namespace Ruvelo\Device\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Ruvelo\Device\Detector;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes $device available in every view rendered for the request.
 */
final class ShareDevice
{
    public function __construct(private readonly Detector $detector) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $as = 'device'): Response
    {
        View::share($as, $this->detector->fromRequest($request));

        return $next($request);
    }
}
