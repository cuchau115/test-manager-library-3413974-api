<?php

namespace App\Domain\Exception;

class BookHasOpenLoans extends DomainError
{
    public function errorCode(): string
    {
        return 'BOOK_HAS_OPEN_LOANS';
    }

    public static function forBook(string $bookId): self
    {
        return new self(
            'El libro tiene préstamos abiertos y no puede darse de baja.',
            [['field' => 'book_id', 'issue' => 'tiene préstamos abiertos']],
        );
    }
}
