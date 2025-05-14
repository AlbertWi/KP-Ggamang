<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Log details of this middleware check
        Log::info('Role Middleware Check', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_authenticated' => !is_null($user),
            'user_id' => $user ? $user->id : 'not authenticated',
            'user_role' => $user ? $user->role : 'not authenticated',
            'required_roles' => $roles,
            'has_required_role' => $user ? in_array($user->role, $roles) : false
        ]);

        // Extended error checking to help diagnose the issue
        if (!$user) {
            Log::warning('Role Middleware: User not authenticated');
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'Authentication required',
                'debug' => 'User is not authenticated'
            ], 401);
        }

        if (!in_array($user->role, $roles)) {
            Log::warning('Role Middleware: Insufficient permissions', [
                'user_role' => $user->role,
                'required_roles' => $roles
            ]);
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'Insufficient permissions',
                'debug' => [
                    'your_role' => $user->role,
                    'required_roles' => $roles,
                    'url' => $request->fullUrl()
                ]
            ], 403);
        }

        return $next($request);
    }
}
