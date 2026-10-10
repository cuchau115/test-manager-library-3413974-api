<?php

namespace App\Domain\Exception;

class ResourceNotFound extends DomainError
{
    public function errorCode(): string
    {
        return 'NOT_FOUND';
    }

    public static function forItem(string $itemId): self
    {
        return new self(
            'La línea del préstamo no existe.',
            [['field' => 'item_id', 'issue' => 'no existe']],
        );
    }

    public static function forResource(string $resource): self
    {
        return new self(
            sprintf('El recurso solicitado no existe.'),
            [['field' => $resource, 'issue' => 'no existe']],
        );
    }
}
