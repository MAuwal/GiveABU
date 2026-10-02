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
        then: function () {
            \Illuminate\Support\Facades\Route::get('/ready', \App\Http\Controllers\ReadinessController::class);
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \App\Http\Middleware\ApiJsonResponse::class,
            \App\Http\Middleware\ApiSecurity::class,
        ]);

        $middleware->alias([
            'donor.auth' => \App\Http\Middleware\AuthenticateDonor::class,
            'role' => \App\Http\Middleware\RequireRole::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        $middleware->replace(
            \Illuminate\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\TrustConfiguredProxies::class
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                    'error' => 'Unauthenticated',
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint not found',
                    'error' => 'Not Found',
                ], 404);
            }
        });
    })->create();
