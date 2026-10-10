<?php

namespace App\Application\Ports;

use App\Domain\Model\Book;

interface BookRepository
{
    public function nextIdentity(): string;

    public function save(Book $book): void;

    public function findById(string $id): ?Book;

    public function findActiveById(string $id): ?Book;

    public function findActiveByIsbn(string $isbn): ?Book;

    /**
     * @param list<string> $ids
     * @return list<Book>
     */
    public function findManyActiveByIds(array $ids): array;

    /**
     * @return array{items: list<Book>, total: int}
     */
    public function search(?string $text, ?string $categoryId, int $page, int $size): array;

    public function hasOpenLoans(string $bookId): bool;
}
