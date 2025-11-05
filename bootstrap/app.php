<?php

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
        $middleware->validateCsrfTokens(except: [
            // Add routes that should be excluded from CSRF if needed
        ]);
        
        $middleware->alias([
            'webauth' => \App\Http\Middleware\WebAuth::class,
            'admin' => \App\Http\Middleware\Admin::class,
        ]);
    })
    ->withSchedule(function ($schedule): void {
        $schedule->command('shipments:check-updates')
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/scheduler.log'));
        
        $schedule->command('shipments:process-pending')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/scheduler.log'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
