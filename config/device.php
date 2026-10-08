<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | User-Agent Client Hints
    |--------------------------------------------------------------------------
    |
    | Chromium browsers (Chrome, Edge, Opera, Samsung Internet, Brave...) send
    | Sec-CH-UA-* headers that are more reliable than their frozen User-Agent.
    | "read" uses them when present. "request" is what the RequestClientHints
    | middleware asks for with Accept-CH; "critical" is also sent as
    | Critical-CH, which makes the browser retry the first request with those
    | hints (one extra round trip, once per site).
    |
    */

    'client_hints' => [
        'read' => env('DEVICE_CLIENT_HINTS', true),
        'request' => [
            'Sec-CH-UA-Platform-Version',
            'Sec-CH-UA-Model',
            'Sec-CH-UA-Full-Version-List',
            'Sec-CH-UA-Form-Factors',
        ],
        'critical' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Every answer is kept in memory for the rest of the process. Name a cache
    | store to also share answers between requests; null keeps memory only.
    |
    */

    'cache' => [
        'store' => env('DEVICE_CACHE_STORE'),
        'ttl' => 60 * 60 * 24 * 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | iPad detection
    |--------------------------------------------------------------------------
    |
    | iPads ask for desktop sites with a Mac User-Agent. The @detectIpad Blade
    | directive prints a tiny script that sets this cookie on touch-screen
    | "Macs", so later requests are recognised as iPads. Null turns it off.
    |
    */

    'ipad_cookie' => 'device_ipad',

    /*
    |--------------------------------------------------------------------------
    | Integrations
    |--------------------------------------------------------------------------
    |
    | User-Agents longer than max_length are cut before parsing. Turn off the
    | Blade directives or the $request->device() macro if they clash with
    | something in your app.
    |
    */

    'max_length' => 1000,

    'blade_directives' => true,

    'request_macro' => true,

];
