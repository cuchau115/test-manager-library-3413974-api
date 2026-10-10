<?php

namespace App\Domain\Exception;

class DuplicateBookInLoan extends DomainError
{
    public function errorCode(): string
    {
        return 'DUPLICATE_BOOK_IN_LOAN';
    }

    public static function forBook(string $bookId): self
    {
        return new self(
            'Un préstamo no puede repetir el mismo libro.',
            [['issue' => 'libro repetido en el préstamo']],
        );
    }
}
