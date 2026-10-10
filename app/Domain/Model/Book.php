<?php

namespace App\Domain\Model;

use App\Domain\Exception\BookNotAvailable;
use App\Domain\Exception\InvalidQuantity;
use App\Domain\Exception\ValidationError;
use App\Domain\ValueObject\Isbn;
use DateTimeImmutable;

/**
 * Agregado Libro. Invariantes: total >= 1 y 0 <= available <= total.
 */
final class Book
{
    private function __construct(
        private readonly string $id,
        private string $title,
        private string $author,
        private Isbn $isbn,
        private string $categoryId,
        private int $total,
        private int $available,
        private ?string $coverKey,
        private ?DateTimeImmutable $deletedAt,
    ) {
    }

    public static function create(
        string $id,
        string $title,
        string $author,
        Isbn $isbn,
        string $categoryId,
        int $total,
    ): self {
        if ($total < 1) {
            throw InvalidQuantity::notPositive($total);
        }

        return new self($id, trim($title), trim($author), $isbn, $categoryId, $total, $total, null, null);
    }

    public static function reconstitute(
        string $id,
        string $title,
        string $author,
        Isbn $isbn,
        string $categoryId,
        int $total,
        int $available,
        ?string $coverKey,
        ?DateTimeImmutable $deletedAt,
    ): self {
        return new self($id, $title, $author, $isbn, $categoryId, $total, $available, $coverKey, $deletedAt);
    }

    public function withdraw(int $quantity = 1): void
    {
        if ($quantity < 1) {
            throw InvalidQuantity::notPositive($quantity);
        }

        if ($this->deletedAt !== null || $this->available < $quantity) {
            throw BookNotAvailable::forTitle($this->title);
        }

        $this->available -= $quantity;
    }

    public function restock(int $quantity = 1): void
    {
        if ($quantity < 1) {
            throw InvalidQuantity::notPositive($quantity);
        }

        $this->available = min($this->available + $quantity, $this->total);
    }

    public function changeTotal(int $newTotal): void
    {
        if ($newTotal < 1) {
            throw InvalidQuantity::notPositive($newTotal);
        }

        $difference = $newTotal - $this->total;

        if ($this->available + $difference < 0) {
            throw new ValidationError(
                'No se puede dejar el total por debajo de los ejemplares prestados.',
                [['field' => 'total_copies', 'issue' => 'deja disponibles negativos']],
            );
        }

        $this->total = $newTotal;
        $this->available += $difference;
    }

    public function updateDetails(string $title, string $author, string $categoryId): void
    {
        $this->title = trim($title);
        $this->author = trim($author);
        $this->categoryId = $categoryId;
    }

    public function setCover(?string $key): void
    {
        $this->coverKey = ($key === null || $key === '') ? null : $key;
    }

    public function softDelete(DateTimeImmutable $at): void
    {
        $this->deletedAt = $at;
    }

    public function isActive(): bool
    {
        return $this->deletedAt === null;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function author(): string
    {
        return $this->author;
    }

    public function isbn(): Isbn
    {
        return $this->isbn;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function available(): int
    {
        return $this->available;
    }

    public function coverKey(): ?string
    {
        return $this->coverKey;
    }

    public function deletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }
}
