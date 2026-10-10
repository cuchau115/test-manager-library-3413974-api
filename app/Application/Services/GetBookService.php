<?php

namespace App\Application\Services;

use App\Application\Ports\BookRepository;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Model\Book;

final class GetBookService
{
    public function __construct(private readonly BookRepository $books)
    {
    }

    public function get(string $id): Book
    {
        return $this->books->findActiveById($id) ?? throw ResourceNotFound::forResource('book');
    }
}
