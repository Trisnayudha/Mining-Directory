<?php

namespace App\Http\Middleware;

use Closure;
use App\Jobs\LogUserActivityJob;

class LogUserActivity
{
    public function handle($request, Closure $next)
    {
        if ($user = $request->user()) {
            $data = [
                'user_id' => $user->id,
                'method' => $request->method(),
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ];

            dispatch(new LogUserActivityJob($data));
        }

        return $next($request);
    }
}
