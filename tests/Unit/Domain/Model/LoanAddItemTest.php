<?php

namespace Tests\Unit\Domain\Model;

use App\Domain\Exception\DuplicateBookInLoan;
use App\Domain\Exception\ValidationError;
use App\Domain\Model\Book;
use App\Domain\Model\Loan;
use App\Domain\ValueObject\Isbn;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class LoanAddItemTest extends TestCase
{
    private function loan(): Loan
    {
        return Loan::create(
            'L0000000-0000-4000-8000-000000000001',
            'M0000000-0000-4000-8000-000000000001',
            'U0000000-0000-4000-8000-000000000001',
            new DateTimeImmutable('2026-10-14T14:15:00Z'),
        );
    }

    private function book(string $id, string $title = 'Cien años de soledad', int $total = 3): Book
    {
        return Book::create(
            $id,
            $title,
            'Autor',
            Isbn::fromString('9780307474728'),
            '11111111-1111-4111-8111-111111111111',
            $total,
        );
    }

    public function test_agrega_hasta_tres_lineas_y_descuenta_ejemplares(): void
    {
        $loan = $this->loan();
        $bookA = $this->book('b0000000-0000-4000-8000-00000000000a');
        $bookB = $this->book('b0000000-0000-4000-8000-00000000000b');
        $bookC = $this->book('b0000000-0000-4000-8000-00000000000c');

        $loan->addItem('i1', $bookA, 'Novela');
        $loan->addItem('i2', $bookB, 'Novela');
        $loan->addItem('i3', $bookC, 'Novela');

        $this->assertCount(3, $loan->items());
        $this->assertSame(2, $bookA->available());
    }

    public function test_no_admite_mas_de_tres_lineas(): void
    {
        $loan = $this->loan();

        foreach (['a', 'b', 'c'] as $suffix) {
            $loan->addItem('i' . $suffix, $this->book("b0000000-0000-4000-8000-00000000000{$suffix}"), 'Novela');
        }

        $this->expectException(ValidationError::class);
        $loan->addItem('i4', $this->book('b0000000-0000-4000-8000-00000000000d'), 'Novela');
    }

    public function test_no_repite_el_mismo_libro(): void
    {
        $loan = $this->loan();
        $book = $this->book('b0000000-0000-4000-8000-00000000000a');

        $loan->addItem('i1', $book, 'Novela');

        $this->expectException(DuplicateBookInLoan::class);
        $loan->addItem('i2', $book, 'Novela');
    }

    public function test_congela_titulo_isbn_y_categoria_del_instante(): void
    {
        $loan = $this->loan();
        $book = $this->book('b0000000-0000-4000-8000-00000000000a', 'Título original');

        $item = $loan->addItem('i1', $book, 'Novela');

        $book->updateDetails('Título nuevo', 'Otro autor', '22222222-2222-4222-8222-222222222222');

        $this->assertSame('Título original', $item->bookTitle());
        $this->assertSame('9780307474728', $item->isbn()->value());
        $this->assertSame('Novela', $item->categoryName());
    }

    public function test_vencimiento_es_loaned_at_mas_catorce_dias(): void
    {
        $loan = $this->loan();
        $item = $loan->addItem('i1', $this->book('b0000000-0000-4000-8000-00000000000a'), 'Novela');

        $this->assertEquals(
            new DateTimeImmutable('2026-10-28T14:15:00Z'),
            $item->dueAt(),
        );
    }
}
