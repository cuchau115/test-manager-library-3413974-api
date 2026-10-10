<?php

namespace App\Application\Services;

use App\Application\Ports\BookRepository;
use App\Application\Ports\CategoryReader;
use App\Domain\Exception\IsbnTaken;
use App\Domain\Exception\ValidationError;
use App\Domain\Model\Book;
use App\Domain\ValueObject\Isbn;

/**
 * HU-03: crear un libro.
 */
final class CreateBookService
{
    public function __construct(
        private readonly BookRepository $books,
        private readonly CategoryReader $categories,
    ) {
    }

    public function create(
        string $title,
        string $author,
        string $isbn,
        string $categoryId,
        int $totalCopies,
    ): Book {
        $title = trim($title);
        $author = trim($author);

        if ($title === '' || $author === '') {
            throw new ValidationError(
                'Título y autor son obligatorios.',
                [['issue' => 'título y autor obligatorios']],
            );
        }

        $isbnValue = Isbn::fromString($isbn);

        if (! $this->categories->exists($categoryId)) {
            throw new ValidationError(
                'La categoría indicada no existe.',
                [['field' => 'category_id', 'issue' => 'no existe']],
            );
        }

        if ($this->books->findActiveByIsbn($isbnValue->value()) !== null) {
            throw IsbnTaken::forValue($isbnValue->value());
        }

        $book = Book::create(
            $this->books->nextIdentity(),
            $title,
            $author,
            $isbnValue,
            $categoryId,
            $totalCopies,
        );

        $this->books->save($book);

        return $book;
    }
}
