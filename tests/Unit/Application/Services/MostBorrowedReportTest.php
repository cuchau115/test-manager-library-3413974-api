<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\MostBorrowedReportService;
use App\Domain\Exception\InvalidDateRange;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\FakeReportReader;

final class MostBorrowedReportTest extends TestCase
{
    public function test_entrega_el_rango_y_las_filas_del_lector(): void
    {
        $reader = new FakeReportReader();
        $reader->rows = [
            ['book_id' => 'b1', 'book_title' => 'Cien años de soledad', 'category_name' => 'Novela', 'times_borrowed' => 12],
        ];

        $rows = (new MostBorrowedReportService($reader))->generate(
            new DateTimeImmutable('2026-10-01T00:00:00Z'),
            new DateTimeImmutable('2026-10-31T23:59:59Z'),
        );

        $this->assertSame(12, $rows[0]['times_borrowed']);
        $this->assertNotNull($reader->received);
        $this->assertEquals(new DateTimeImmutable('2026-10-01T00:00:00Z'), $reader->received->start());
    }

    public function test_rango_invertido_es_invalido(): void
    {
        $this->expectException(InvalidDateRange::class);

        (new MostBorrowedReportService(new FakeReportReader()))->generate(
            new DateTimeImmutable('2026-10-31T00:00:00Z'),
            new DateTimeImmutable('2026-10-01T00:00:00Z'),
        );
    }
}
