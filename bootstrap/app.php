<?php

use App\Http\Middleware\EnsureNotSuspended;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: env('TRUSTED_PROXIES') ? explode(',', env('TRUSTED_PROXIES')) : null);
        $middleware->append(SecurityHeaders::class);
        $middleware->validateCsrfTokens(except: [
            'webhooks/monnify',
            'webhooks/site-integrations/*',
        ]);
        $middleware->alias([
            'not_suspended' => EnsureNotSuspended::class,
            'has_wallet' => \App\Http\Middleware\EnsureHasWallet::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'marketplace.public' => \App\Http\Middleware\MarketplaceComingSoon::class,
        ]);
        $middleware->appendToGroup('web', EnsureNotSuspended::class);
        $middleware->appendToGroup('api', EnsureNotSuspended::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        if (class_exists(\Sentry\Laravel\Integration::class)) {
            \Sentry\Laravel\Integration::handles($exceptions);
        }
    })->create();
