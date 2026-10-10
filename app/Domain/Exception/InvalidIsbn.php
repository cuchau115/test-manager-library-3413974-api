<?php

namespace App\Domain\Exception;

class InvalidIsbn extends ValidationError
{
    public static function forValue(string $value): self
    {
        return new self(
            sprintf('El ISBN «%s» no es válido: debe tener exactamente 13 dígitos.', $value),
            [['field' => 'isbn', 'issue' => 'debe tener 13 dígitos']],
        );
    }
}
