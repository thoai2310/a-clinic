<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->errorResponse(
                    'User not found',
                    401,
                    'invalid_token'
                );
            }
        } catch (TokenExpiredException $e) {
            return $this->errorResponse(
                'Token has expired',
                401,
                'invalid_token'
            );
        } catch (TokenInvalidException $e) {
            return $this->errorResponse(
                'Token is invalid',
                401,
                'invalid_token'
            );
        } catch (JWTException $e) {
            return $this->errorResponse(
                'Token could not be parsed from the request',
                400,
                'invalid_request'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Authorization token required',
                401,
                'invalid_token'
            );
        }

        return $next($request);
    }

    private function errorResponse(
        string $message,
        int $status = 401,
        string $error = 'invalid_token',
        string $realm = 'api'
    ) {
        $response = response()->json([
            'error'   => $status === 400 ? 'Bad Request' : 'Unauthenticated',
            'message' => $message,
        ], $status);

        if (in_array($status, [400, 401, 403], true)) {
            $response->header(
                'WWW-Authenticate',
                sprintf('Bearer realm="%s", error="%s", error_description="%s"', $realm, $error, $message)
            );
        }

        return $response;
    }
}
