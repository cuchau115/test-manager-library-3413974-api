<?php

namespace App\Domain\Exception;

class ForbiddenRole extends DomainError
{
    public function errorCode(): string
    {
        return 'FORBIDDEN_ROLE';
    }

    public static function needed(string $role): self
    {
        return new self('No tiene permisos para realizar esta acción.');
    }
}
