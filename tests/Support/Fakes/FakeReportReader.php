<?php

namespace Tests\Support\Fakes;

use App\Application\Ports\ReportReader;
use App\Domain\ValueObject\DateRange;

final class FakeReportReader implements ReportReader
{
    /** @var list<array{book_id: string, book_title: string, category_name: string, times_borrowed: int}> */
    public array $rows = [];

    public ?DateRange $received = null;

    public function mostBorrowed(DateRange $range): array
    {
        $this->received = $range;

        return $this->rows;
    }
}
