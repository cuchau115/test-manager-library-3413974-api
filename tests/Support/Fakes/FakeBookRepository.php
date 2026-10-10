<?php

namespace Tests\Support\Fakes;

use App\Application\Ports\BookRepository;
use App\Domain\Model\Book;

final class FakeBookRepository implements BookRepository
{
    /** @var array<string, Book> */
    public array $items = [];

    /** @var list<string> */
    public array $openLoanBookIds = [];

    private int $sequence = 0;

    public function nextIdentity(): string
    {
        return 'book-' . (++$this->sequence);
    }

    public function save(Book $book): void
    {
        $this->items[$book->id()] = $book;
    }

    public function findById(string $id): ?Book
    {
        return $this->items[$id] ?? null;
    }

    public function findActiveById(string $id): ?Book
    {
        $book = $this->items[$id] ?? null;

        return $book !== null && $book->isActive() ? $book : null;
    }

    public function findActiveByIsbn(string $isbn): ?Book
    {
        foreach ($this->items as $book) {
            if ($book->isActive() && $book->isbn()->value() === $isbn) {
                return $book;
            }
        }

        return null;
    }

    public function findManyActiveByIds(array $ids): array
    {
        $out = [];

        foreach ($ids as $id) {
            $book = $this->findActiveById($id);

            if ($book !== null) {
                $out[] = $book;
            }
        }

        return $out;
    }

    public function search(?string $text, ?string $categoryId, int $page, int $size): array
    {
        $list = array_values(array_filter($this->items, fn (Book $b): bool => $b->isActive()));

        if ($categoryId !== null) {
            $list = array_values(array_filter($list, fn (Book $b): bool => $b->categoryId() === $categoryId));
        }

        if ($text !== null) {
            $needle = mb_strtolower($text);
            $list = array_values(array_filter($list, fn (Book $b): bool => str_contains(mb_strtolower($b->title()), $needle)
                || str_contains(mb_strtolower($b->author()), $needle)));
        }

        usort($list, fn (Book $a, Book $b): int => strcmp($a->title(), $b->title()));

        $total = count($list);

        return ['items' => array_slice($list, ($page - 1) * $size, $size), 'total' => $total];
    }

    public function hasOpenLoans(string $bookId): bool
    {
        return in_array($bookId, $this->openLoanBookIds, true);
    }
}
