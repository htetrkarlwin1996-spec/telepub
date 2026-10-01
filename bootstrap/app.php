<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureArtistCanManage;
use App\Http\Middleware\EnsureHasArtist;
use App\Http\Middleware\PublicMaintenanceMode;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/stripe/releases',
            'webhooks/myanmyanpay/releases',
        ]);
        // This application-level mode keeps Laravel running so an administrator
        // can always reach the panel and turn maintenance mode back off.
        $middleware->append(PublicMaintenanceMode::class);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'artist' => EnsureHasArtist::class,
            'artist.manage' => EnsureArtistCanManage::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
