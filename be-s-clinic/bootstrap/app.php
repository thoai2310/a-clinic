<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwt.custom' => \App\Http\Middleware\JwtMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'webhook',
            'webhook/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle Authentication Exception (ngăn redirect đến login route)
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'Authentication required'
                ], 401);
            }
            abort(401);
        });

        // Handle Route Not Found Exception
        $exceptions->render(function (\Symfony\Component\Routing\Exception\RouteNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Route not found',
                    'message' => 'The requested route does not exist'
                ], 404);
            }
        });

        // Handle JWT Token Invalid Exception
        $exceptions->render(function (TokenInvalidException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Token is invalid',
                    'message' => 'Authentication token is invalid'
                ], 401);
            }
            abort(401);
        });

        // Handle JWT Token Expired Exception
        $exceptions->render(function (TokenExpiredException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Token has expired',
                    'message' => 'Authentication token has expired'
                ], 401);
            }
            abort(401);
        });

        // Handle JWT Token Blacklisted Exception
        $exceptions->render(function (TokenBlacklistedException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Token is blacklisted',
                    'message' => 'Authentication token is blacklisted'
                ], 401);
            }
            abort(401);
        });

        // Handle General JWT Exception
        $exceptions->render(function (JWTException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'error' => 'JWT Error',
                    'message' => 'Token could not be parsed from the request'
                ], 400);
            }
            abort(400);
        });
    })
    ->withProviders([
    ])
    ->create();
