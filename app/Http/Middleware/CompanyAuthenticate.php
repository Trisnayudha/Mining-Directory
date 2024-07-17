<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class CompanyAuthenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = Auth::guard('company')->user();
            if (empty($user)) {
                $response = [
                    'status' => 401, // Unauthorized
                    'message' => 'Authorization Token not found',
                    'payload' => null
                ];
                return response()->json($response, 401);
            }
        } catch (Exception $e) {
            $response = [
                'status' => 401, // Unauthorized
                'message' => '',
                'payload' => null
            ];

            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                $response['message'] = 'Token is Invalid';
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                $response['message'] = 'Token is Expired';
            } else {
                $response['message'] = 'Authorization Token not found';
            }
            return response()->json($response, 401);
        }
        return $next($request);
    }
}
