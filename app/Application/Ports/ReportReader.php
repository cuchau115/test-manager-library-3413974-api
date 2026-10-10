<?php

namespace App\Application\Ports;

use App\Domain\ValueObject\DateRange;

interface ReportReader
{
    /**
     * Agrega por valores congelados de la línea (no lee el catálogo vivo).
     *
     * @return list<array{book_id: string, book_title: string, category_name: string, times_borrowed: int}>
     */
    public function mostBorrowed(DateRange $range): array;
}
