<?php

namespace App\Domain\Exception;

class MemberLimitExceeded extends DomainError
{
    public function errorCode(): string
    {
        return 'MEMBER_LIMIT_EXCEEDED';
    }

    public static function withActiveLoans(int $activeLoans, int $max): self
    {
        return new self(
            sprintf('El socio ya tiene %d libros sin devolver (máximo %d).', $activeLoans, $max),
            [['field' => 'member_id', 'issue' => sprintf('ya tiene %d sin devolver', $activeLoans)]],
        );
    }
}
