<?php

use App\Http\Middleware\SetDefaultLanguage;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([

    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            SetDefaultLanguage::class,
            SecurityHeaders::class,
        ]);
        // user_locale is set by a tiny client-side script in the header
        // pill switcher; Laravel's EncryptCookies middleware would
        // otherwise try to decrypt it and silently drop the value.
        $middleware->encryptCookies(except: ['user_locale']);
        $middleware->alias([
            'fixedLocaleAdmin' => \App\Http\Middleware\SetFixedLocaleAdmin::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'IsActive' => \App\Http\Middleware\IsActive::class,
            'VersionCheck' => \App\Http\Middleware\VersionCheck::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
