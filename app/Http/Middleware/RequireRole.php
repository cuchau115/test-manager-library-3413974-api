<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userRole = $request->attributes->get('auth.role');

        if ($userRole === null) {
            return response()->json([
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'Sesión no válida o vencida',
                'details' => [],
            ], 401);
        }

        if (!in_array($userRole, $roles, true)) {
            return response()->json([
                'code' => 'FORBIDDEN_ROLE',
                'message' => 'Rol insuficiente',
                'details' => [],
            ], 403);
        }

        return $next($request);
    }
}