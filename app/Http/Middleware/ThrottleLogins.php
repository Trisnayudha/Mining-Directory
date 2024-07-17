<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLogins
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next)
    {
        $key = $this->resolveRequestKey($request);

        // if ($this->limiter->tooManyAttempts($key, 5)) {
        //     $retryAfter = ceil($this->limiter->availableIn($key) / 60); // Bulatkan ke atas dalam menit
        //     // Menggunakan format response custom
        //     return response()->json([
        //         'status' => 429, // HTTP status code
        //         'message' => 'Too many login attempts. Please try again in ' . $retryAfter . ' minutes.',
        //         'payload' => null
        //     ], 429);
        // }

        $response = $next($request);

        if ($response->status() == Response::HTTP_UNAUTHORIZED) {
            $this->limiter->hit($key, 3600); // Block for 1 hour
        }

        return $response;
    }

    protected function resolveRequestKey(Request $request)
    {
        return $request->ip();
    }
}
