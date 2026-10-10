<?php

namespace App\Domain\Model;

use App\Domain\Exception\ItemAlreadyReturned;
use App\Domain\ValueObject\Isbn;
use DateTimeImmutable;

/**
 * Línea de préstamo. Copia congelada del libro en el instante del préstamo.
 */
final class LoanItem
{
    public function __construct(
        private readonly string $id,
        private readonly string $bookId,
        private readonly string $bookTitle,
        private readonly Isbn $isbn,
        private readonly string $categoryName,
        private readonly DateTimeImmutable $dueAt,
        private ?DateTimeImmutable $returnedAt,
    ) {
    }

    public function return(DateTimeImmutable $at): void
    {
        if ($this->returnedAt !== null) {
            throw ItemAlreadyReturned::forItem($this->id);
        }

        $this->returnedAt = $at;
    }

    public function isReturned(): bool
    {
        return $this->returnedAt !== null;
    }

    public function isOverdue(DateTimeImmutable $now): bool
    {
        return ! $this->isReturned() && $this->dueAt < $now;
    }

    public function daysLate(DateTimeImmutable $now): int
    {
        if (! $this->isOverdue($now)) {
            return 0;
        }

        return (int) $this->dueAt->diff($now)->days;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function bookId(): string
    {
        return $this->bookId;
    }

    public function bookTitle(): string
    {
        return $this->bookTitle;
    }

    public function isbn(): Isbn
    {
        return $this->isbn;
    }

    public function categoryName(): string
    {
        return $this->categoryName;
    }

    public function dueAt(): DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function returnedAt(): ?DateTimeImmutable
    {
        return $this->returnedAt;
    }
}
