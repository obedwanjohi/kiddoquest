<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'admin.guest' => \App\Http\Middleware\AdminGuest::class,
            'guardian.auth' => \App\Http\Middleware\GuardianAuth::class,
            'guardian.guest' => \App\Http\Middleware\GuardianGuest::class,
            'ensure.child.session' => \App\Http\Middleware\EnsureChildSession::class,
        ]);

        // ✅ MOBILE / TUNNEL TESTING: Trust all proxies
        // Required so HTTPS tunnels (ngrok, cloudflared) pass the correct
        // protocol to Laravel via X-Forwarded-Proto.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// Point storage to writable /tmp only on serverless environments (e.g. Vercel)
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('APP_STORAGE')) {
    $app->useStoragePath(getenv('APP_STORAGE') ?: '/tmp/storage');
}

return $app;
