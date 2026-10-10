<?php

namespace App\Domain\Exception;

class IsbnTaken extends DomainError
{
    public function errorCode(): string
    {
        return 'ISBN_TAKEN';
    }

    public static function forValue(string $isbn): self
    {
        return new self(
            sprintf('El ISBN «%s» ya pertenece a un libro activo.', $isbn),
            [['field' => 'isbn', 'issue' => 'ya existe entre activos']],
        );
    }
}
