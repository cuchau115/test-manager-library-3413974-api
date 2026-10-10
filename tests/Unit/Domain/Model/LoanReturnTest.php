<?php

namespace Tests\Unit\Domain\Model;

use App\Domain\Exception\ItemAlreadyReturned;
use App\Domain\Model\Book;
use App\Domain\Model\Loan;
use App\Domain\ValueObject\Isbn;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class LoanReturnTest extends TestCase
{
    private function loanWithItem(): array
    {
        $loan = Loan::create(
            'L0000000-0000-4000-8000-000000000001',
            'M0000000-0000-4000-8000-000000000001',
            'U0000000-0000-4000-8000-000000000001',
            new DateTimeImmutable('2026-10-14T14:15:00Z'),
        );

        $book = Book::create(
            'b0000000-0000-4000-8000-00000000000a',
            'Cien años de soledad',
            'Autor',
            Isbn::fromString('9780307474728'),
            '11111111-1111-4111-8111-111111111111',
            3,
        );

        $item = $loan->addItem('i1', $book, 'Novela');

        return [$loan, $item, $book];
    }

    public function test_devuelve_una_linea_una_sola_vez(): void
    {
        [$loan, $item] = $this->loanWithItem();

        $loan->returnItem('i1', new DateTimeImmutable('2026-10-20T10:00:00Z'));

        $this->assertTrue($item->isReturned());
        $this->assertEquals(new DateTimeImmutable('2026-10-20T10:00:00Z'), $item->returnedAt());
    }

    public function test_doble_devolucion_falla(): void
    {
        [$loan] = $this->loanWithItem();
        $loan->returnItem('i1', new DateTimeImmutable('2026-10-20T10:00:00Z'));

        $this->expectException(ItemAlreadyReturned::class);
        $loan->returnItem('i1', new DateTimeImmutable('2026-10-21T10:00:00Z'));
    }

    public function test_estado_open_y_cerrado(): void
    {
        [$loan, , $book] = $this->loanWithItem();
        $now = new DateTimeImmutable('2026-10-20T10:00:00Z');

        $this->assertSame('open', $loan->status($now));

        $loan->returnItem('i1', $now);
        $book->restock();

        $this->assertSame('closed', $loan->status($now));
        $this->assertSame(3, $book->available());
    }

    public function test_estado_overdue_cuando_pasa_el_vencimiento(): void
    {
        [$loan] = $this->loanWithItem();

        $this->assertSame('overdue', $loan->status(new DateTimeImmutable('2026-11-01T00:00:00Z')));
    }
}
