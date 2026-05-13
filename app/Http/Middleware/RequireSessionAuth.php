<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSessionAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = $request->session()->get('auth_user');

        if (!is_array($authUser)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Unauthorized. Silakan login terlebih dahulu.',
                ], 401);
            }

            return redirect()->route('auth.login.form')->with('auth_error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}
