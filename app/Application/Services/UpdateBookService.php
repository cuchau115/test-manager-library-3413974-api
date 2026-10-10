<?php

namespace App\Application\Services;

use App\Application\Ports\BookRepository;
use App\Application\Ports\CategoryReader;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Exception\ValidationError;
use App\Domain\Model\Book;

/**
 * HU-03 (CA-03.3): reemplazar título, autor, categoría y total.
 */
final class UpdateBookService
{
    public function __construct(
        private readonly BookRepository $books,
        private readonly CategoryReader $categories,
    ) {
    }

    public function update(
        string $id,
        string $title,
        string $author,
        string $categoryId,
        int $totalCopies,
    ): Book {
        $book = $this->books->findActiveById($id) ?? throw ResourceNotFound::forResource('book');

        $title = trim($title);
        $author = trim($author);

        if ($title === '' || $author === '') {
            throw new ValidationError(
                'Título y autor son obligatorios.',
                [['issue' => 'título y autor obligatorios']],
            );
        }

        if (! $this->categories->exists($categoryId)) {
            throw new ValidationError(
                'La categoría indicada no existe.',
                [['field' => 'category_id', 'issue' => 'no existe']],
            );
        }

        $book->updateDetails($title, $author, $categoryId);
        $book->changeTotal($totalCopies);
        $this->books->save($book);

        return $book;
    }
}
