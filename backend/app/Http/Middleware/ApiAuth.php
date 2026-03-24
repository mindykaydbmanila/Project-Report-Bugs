<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * Usage:
     *   'api.auth'          – requires a valid token (401 if missing/invalid)
     *   'api.auth:optional' – sets the user if token is present, but allows unauthenticated requests
     */
    public function handle(Request $request, Closure $next, string $mode = ''): mixed
    {
        $token = $request->bearerToken();

        if ($token) {
            $user = User::where('api_token', $token)->first();
            if ($user) {
                $request->setUserResolver(fn () => $user);
            }
        }

        if ($mode !== 'optional' && ! $request->user()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return $next($request);
    }
}
