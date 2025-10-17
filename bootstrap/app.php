<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',


        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', 'admin'])->prefix('admin')->group(base_path('routes/backend.php'));
        }
    )
     ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['auth:api']],
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->appendToGroup('web', [
            // CorsMiddleware::class, // for CORS

            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,

            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class, // for web
            'auth.jwt' => App\Http\Middleware\AuthCheckMiddleware::class, // for API
            'admin' => App\Http\Middleware\AdminMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'payment/stripe-webhook',
            'api/*'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

    //hello
