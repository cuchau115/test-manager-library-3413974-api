<?php

namespace App\Http\Middleware;

use App\Application\Ports\TokenVerifier;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateJwt
{
    public function __construct(
        private readonly TokenVerifier $tokenVerifier,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if ($header === null || !str_starts_with($header, 'Bearer ')) {
            return $this->unauthorized('INVALID_CREDENTIALS', 'Sesión no válida o vencida');
        }

        $token = substr($header, 7);
        $identity = $this->tokenVerifier->verify($token);

        if ($identity === null) {
            return $this->unauthorized('INVALID_CREDENTIALS', 'Sesión no válida o vencida');
        }

        $request->attributes->set('auth.user_id', $identity['user_id']);
        $request->attributes->set('auth.role', $identity['role']);
        $request->attributes->set('auth.token', $token);

        return $next($request);
    }

    private function unauthorized(string $code, string $message): Response
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'details' => [],
        ], 401);
    }
}