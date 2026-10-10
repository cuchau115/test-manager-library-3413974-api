<?php

namespace App\Application\Services;

use App\Application\Ports\BookRepository;
use App\Application\Ports\Clock;
use App\Domain\Exception\BookHasOpenLoans;
use App\Domain\Exception\ResourceNotFound;

/**
 * HU-03 (CA-03.5): baja lógica; bloquea si hay préstamos abiertos.
 */
final class DeleteBookService
{
    public function __construct(
        private readonly BookRepository $books,
        private readonly Clock $clock,
    ) {
    }

    public function delete(string $id): void
    {
        $book = $this->books->findActiveById($id) ?? throw ResourceNotFound::forResource('book');

        if ($this->books->hasOpenLoans($id)) {
            throw BookHasOpenLoans::forBook($id);
        }

        $book->softDelete($this->clock->now());
        $this->books->save($book);
    }
}
