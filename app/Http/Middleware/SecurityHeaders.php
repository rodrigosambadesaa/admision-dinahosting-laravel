<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);
        $browserFibonacci = in_array($request->path(), ['javascript', 'JAVASCRIPT/index.html'], true);
        $scriptPolicy = $browserFibonacci ? "script-src 'self'" : "script-src 'none'";
        $loginExercise = in_array($request->path(), ['login', 'CSS/index.html'], true);
        $formPolicy = $loginExercise ? 'form-action https://dinahosting.com' : "form-action 'self'";

        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; {$scriptPolicy}; "
            . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; "
            . "font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; "
            . "connect-src 'self'; object-src 'none'; base-uri 'none'; "
            . "{$formPolicy}; frame-ancestors 'none'");
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Permissions-Policy',
            'accelerometer=(), camera=(), geolocation=(), gyroscope=(), microphone=(), payment=(), usb=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        return $response;
    }
}
