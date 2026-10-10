<?php

namespace App\Domain\Exception;

class InvalidCredentials extends DomainError
{
    public function errorCode(): string
    {
        return 'INVALID_CREDENTIALS';
    }

    public static function generic(): self
    {
        return new self('Usuario o contraseña incorrectos.');
    }
}
