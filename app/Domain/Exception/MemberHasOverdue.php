<?php

namespace App\Domain\Exception;

class MemberHasOverdue extends DomainError
{
    public function errorCode(): string
    {
        return 'MEMBER_HAS_OVERDUE';
    }

    public static function forMember(string $memberId): self
    {
        return new self(
            'El socio tiene libros vencidos sin devolver.',
            [['field' => 'member_id', 'issue' => 'tiene libros vencidos']],
        );
    }
}
