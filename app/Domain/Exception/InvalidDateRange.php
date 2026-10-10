<?php

namespace App\Domain\Exception;

class InvalidDateRange extends DomainError
{
    public function errorCode(): string
    {
        return 'INVALID_DATE_RANGE';
    }

    public static function endBeforeStart(): self
    {
        return new self(
            'El fin del rango no puede ser anterior al inicio.',
            [['field' => 'to', 'issue' => 'no puede ser anterior a from']],
        );
    }
}
