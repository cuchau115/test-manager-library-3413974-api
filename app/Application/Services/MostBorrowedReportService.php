<?php

namespace App\Application\Services;

use App\Application\Ports\ReportReader;
use App\Domain\ValueObject\DateRange;
use DateTimeImmutable;

/**
 * HU-10: reporte de libros más prestados en un rango (por valores congelados).
 */
final class MostBorrowedReportService
{
    public function __construct(private readonly ReportReader $reader)
    {
    }

    /**
     * @return list<array{book_id: string, book_title: string, category_name: string, times_borrowed: int}>
     */
    public function generate(DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $range = DateRange::of($from, $to);

        return $this->reader->mostBorrowed($range);
    }
}
