<?php

namespace App\Application\Services;

use App\Application\Ports\BookRepository;

/**
 * HU-04: buscar libros (texto, categoría, activos, orden por título, paginado).
 */
final class SearchBooksService
{
    public const MAX_SIZE = 50;

    public function __construct(private readonly BookRepository $books)
    {
    }

    /**
     * @return array{items: list<\App\Domain\Model\Book>, total: int}
     */
    public function search(?string $text, ?string $categoryId, int $page, int $size): array
    {
        $page = max(1, $page);
        $size = min(max(1, $size), self::MAX_SIZE);
        $text = ($text === null || trim($text) === '') ? null : trim($text);
        $categoryId = ($categoryId === null || $categoryId === '') ? null : $categoryId;

        return $this->books->search($text, $categoryId, $page, $size);
    }
}
