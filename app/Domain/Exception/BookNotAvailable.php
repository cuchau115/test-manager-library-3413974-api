<?php

namespace App\Domain\Exception;

class BookNotAvailable extends DomainError
{
    public function errorCode(): string
    {
        return 'BOOK_NOT_AVAILABLE';
    }

    public static function forTitle(string $title): self
    {
        return new self(
            sprintf('No quedan ejemplares disponibles de «%s».', $title),
            [['issue' => 'sin ejemplares']],
        );
    }
}
