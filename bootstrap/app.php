<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'error' => [
                        'type' => 'validation_error',
                        'code' => 422,
                    ],
                    'errors' => $e->errors(),
                    'path' => $request->path(),
                    'timestamp' => now()->toISOString(),
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'error' => [
                        'type' => 'authentication_error',
                        'code' => 401,
                    ],
                    'path' => $request->path(),
                    'timestamp' => now()->toISOString(),
                ], 401);
            }

            if ($e instanceof AuthorizationException) {
                return response()->json([
                    'message' => 'This action is unauthorized.',
                    'error' => [
                        'type' => 'authorization_error',
                        'code' => 403,
                    ],
                    'path' => $request->path(),
                    'timestamp' => now()->toISOString(),
                ], 403);
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            $message = $status >= 500 ? 'Server error.' : ($e->getMessage() ?: 'Request failed.');

            return response()->json([
                'message' => $message,
                'error' => [
                    'type' => class_basename($e),
                    'code' => $status,
                ],
                'path' => $request->path(),
                'timestamp' => now()->toISOString(),
            ], $status);
        });
    })->create();
