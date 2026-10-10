<?php

namespace App\Application\Services;

use App\Application\Ports\BookRepository;
use App\Application\Ports\CoverStorage;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Model\Book;

/**
 * HU-03 (CA-03.4): subir portada y guardar una clave opaca.
 */
final class SetCoverService
{
    public function __construct(
        private readonly BookRepository $books,
        private readonly CoverStorage $storage,
    ) {
    }

    public function set(string $id, string $contents, string $mimeType): Book
    {
        $book = $this->books->findActiveById($id) ?? throw ResourceNotFound::forResource('book');

        $previous = $book->coverKey();
        $key = $this->storage->store($contents, $mimeType);

        $book->setCover($key);
        $this->books->save($book);

        if ($previous !== null && $previous !== $key) {
            $this->storage->delete($previous);
        }

        return $book;
    }
}
