<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Middleware\HandleCors;
use App\Http\Middleware\EnsureAgent;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(__DIR__.'/../routes/channels.php')
    ->withMiddleware(function (Middleware $middleware): void {

    $middleware->append(HandleCors::class);
    $middleware->web(
        prepend: [\App\Http\Middleware\SelectPortalGuard::class],
        append: [\App\Http\Middleware\PortalAuthenticateSession::class, \App\Http\Middleware\RequireClientPasswordChange::class],
    );
    $middleware->redirectGuestsTo(fn (Request $request) => match (true) {
        $request->is('admin', 'admin/*') => '/admin/login',
        $request->is('client', 'client/*') => '/client/login',
        $request->is('agent', 'agent/*') => '/agent/login',
        default => '/',
    });

    $middleware->alias([
        'agent' => EnsureAgent::class,
    ]);

})
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
