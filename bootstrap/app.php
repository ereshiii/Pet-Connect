<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\BlockIpMiddleware;
use App\Http\Middleware\CheckSubscriptionFeature;
use App\Http\Middleware\EnsureUserIsClinic;
use App\Http\Middleware\EnsureUserProfileComplete;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RequestLoggerMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Events\Failed;
use App\Listeners\LogFailedLoginAttempt;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/settings.php'));
            Route::middleware('web')
                ->group(base_path('routes/clinic-settings.php'));
            
            // Register event listeners
            Event::listen(Failed::class, LogFailedLoginAttempt::class);
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            BlockIpMiddleware::class,
            RequestLoggerMiddleware::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'clinic' => EnsureUserIsClinic::class,
            'admin' => AdminMiddleware::class,
            'subscription' => CheckSubscriptionFeature::class,
            'profile.complete' => EnsureUserProfileComplete::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
