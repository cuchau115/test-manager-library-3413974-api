<?php

namespace App\Application\Services;

use App\Application\Ports\BookRepository;
use App\Application\Ports\CoverStorage;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Model\Book;

/**
 * HU-03 (CA-03.4): quitar la portada. Orden: anular clave, confirmar, borrar binario.
 */
final class RemoveCoverService
{
    public function __construct(
        private readonly BookRepository $books,
        private readonly CoverStorage $storage,
    ) {
    }

    public function remove(string $id): Book
    {
        $book = $this->books->findActiveById($id) ?? throw ResourceNotFound::forResource('book');

        $key = $book->coverKey();

        $book->setCover(null);
        $this->books->save($book);

        if ($key !== null) {
            $this->storage->delete($key);
        }

        return $book;
    }
}
