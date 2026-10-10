<?php

namespace App\Domain\Exception;

class InvalidQuantity extends ValidationError
{
    public static function notPositive(int $value): self
    {
        return new self(
            sprintf('La cantidad debe ser mayor que cero; se recibió %d.', $value),
            [['field' => 'quantity', 'issue' => 'debe ser mayor que cero']],
        );
    }
}
