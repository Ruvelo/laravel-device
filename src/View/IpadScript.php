<?php

declare(strict_types=1);

namespace Ruvelo\Device\View;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;

/**
 * What @detectIpad prints: a one-line script that sets a cookie when a "Mac"
 * has a touch screen. Macs don't; iPads asking for desktop sites do. The
 * server reads the cookie from the next request on.
 */
final class IpadScript
{
    public static function render(?string $cookie): HtmlString
    {
        if ($cookie === null || $cookie === '' || ! preg_match('/^[A-Za-z0-9_-]+$/', $cookie)) {
            return new HtmlString('');
        }

        $nonce = Vite::cspNonce();
        $attribute = is_string($nonce) && $nonce !== '' ? ' nonce="'.e($nonce).'"' : '';

        return new HtmlString(
            '<script'.$attribute.'>(function(c){try{if(navigator.maxTouchPoints>1&&/Macintosh/.test(navigator.userAgent)&&document.cookie.indexOf(c+"=1")<0)'
            .'{document.cookie=c+"=1;path=/;max-age=31536000;samesite=lax"+(location.protocol==="https:"?";secure":"")}}catch(e){}})("'.$cookie.'")</script>'
        );
    }
}
