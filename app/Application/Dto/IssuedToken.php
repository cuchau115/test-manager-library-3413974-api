<?php

namespace App\Application\Dto;

/**
 * Resultado de emitir un token de acceso.
 */
final class IssuedToken
{
    public function __construct(
        public readonly string $token,
        public readonly string $tokenType,
        public readonly int $expiresIn,
        public readonly string $role,
    ) {
    }
}
