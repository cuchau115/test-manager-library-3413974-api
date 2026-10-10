<?php

namespace App\Domain\Exception;

class UsernameTaken extends DomainError
{
    public function errorCode(): string
    {
        return 'USERNAME_TAKEN';
    }

    public static function forValue(string $username): self
    {
        return new self(
            sprintf('El nombre de usuario «%s» ya está en uso.', $username),
            [['field' => 'username', 'issue' => 'ya existe']],
        );
    }
}
