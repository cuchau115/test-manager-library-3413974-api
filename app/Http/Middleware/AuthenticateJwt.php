<?php

namespace App\Http\Middleware;

use App\Application\Ports\TokenIssuer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateJwt
{
    public function __construct(
        private readonly TokenIssuer $tokenIssuer,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (!str_starts_with($header, 'Bearer ')) {
            return $this->unauthorized('INVALID_CREDENTIALS', 'Sesión no válida o vencida');
        }

        $token = substr($header, 7);

        try {
            // Verify token using the same logic as the TokenIssuer
            // Since we don't have a verify method in the port, we'll decode and validate here
            $parser = (new \Lcobucci\JWT\Parser());
            $jwt = $parser->parse($token);

            $signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();
            $key = \Lcobucci\JWT\Signer\Key\InMemory::plainText(config('library.jwt.secret'));

            if (!$jwt->verify($signer, $key)) {
                return $this->unauthorized('INVALID_CREDENTIALS', 'Sesión no válida o vencida');
            }

            if ($jwt->isExpired()) {
                return $this->unauthorized('INVALID_CREDENTIALS', 'Sesión no válida o vencida');
            }

            // Attach user info to request
            $request->attributes->set('auth.user_id', $jwt->claims()->get('jti'));
            $request->attributes->set('auth.role', $jwt->claims()->get('role'));
            $request->attributes->set('auth.token', $token);

        } catch (\Throwable) {
            return $this->unauthorized('INVALID_CREDENTIALS', 'Sesión no válida o vencida');
        }

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