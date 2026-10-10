<?php

namespace App\Domain\Exception;

/**
 * Error de validación de entrada / regla de valor. Código VALIDATION_ERROR (422).
 */
class ValidationError extends DomainError
{
    public function errorCode(): string
    {
        return 'VALIDATION_ERROR';
    }
}
