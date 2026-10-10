<?php

namespace Tests\Unit\Domain\Model;

use App\Domain\Exception\BookNotAvailable;
use App\Domain\Exception\InvalidQuantity;
use App\Domain\Exception\ValidationError;
use App\Domain\Model\Book;
use App\Domain\ValueObject\Isbn;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BookTest extends TestCase
{
    private function book(int $total = 4): Book
    {
        return Book::create(
            'b0000000-0000-4000-8000-000000000001',
            'Cien años de soledad',
            'Gabriel García Márquez',
            Isbn::fromString('9780307474728'),
            '11111111-1111-4111-8111-111111111111',
            $total,
        );
    }

    public function test_crear_arranca_disponibles_igual_al_total(): void
    {
        $book = $this->book(4);

        $this->assertSame(4, $book->total());
        $this->assertSame(4, $book->available());
        $this->assertNull($book->coverKey());
        $this->assertTrue($book->isActive());
    }

    public function test_total_debe_ser_al_menos_uno(): void
    {
        $this->expectException(InvalidQuantity::class);
        $this->book(0);
    }

    public function test_prestar_descuenta_un_ejemplar(): void
    {
        $book = $this->book(2);
        $book->withdraw();
        $this->assertSame(1, $book->available());

        $book->withdraw();
        $this->assertSame(0, $book->available());
    }

    public function test_no_deja_disponibles_negativos(): void
    {
        $book = $this->book(1);
        $book->withdraw();

        $this->expectException(BookNotAvailable::class);
        $book->withdraw();
    }

    public function test_devolver_no_supera_el_total(): void
    {
        $book = $this->book(2);
        $book->restock(5);

        $this->assertSame(2, $book->available());
    }

    public function test_cambiar_total_ajusta_disponibles_en_la_diferencia(): void
    {
        $book = $this->book(3);
        $book->withdraw();

        $book->changeTotal(5);

        $this->assertSame(5, $book->total());
        $this->assertSame(4, $book->available());
    }

    public function test_cambiar_total_que_dejaria_disponibles_negativos_falla(): void
    {
        $book = $this->book(3);
        $book->withdraw();
        $book->withdraw();
        $book->withdraw();

        $this->expectException(ValidationError::class);
        $book->changeTotal(2);
    }

    public function test_baja_logica_marca_inactivo_y_bloquea_prestamo(): void
    {
        $book = $this->book(1);
        $book->softDelete(new DateTimeImmutable('2026-10-14T00:00:00Z'));

        $this->assertFalse($book->isActive());

        $this->expectException(BookNotAvailable::class);
        $book->withdraw();
    }

    public function test_portada_vacia_es_nula(): void
    {
        $book = $this->book(1);
        $book->setCover('');
        $this->assertNull($book->coverKey());

        $book->setCover('covers/abc.png');
        $this->assertSame('covers/abc.png', $book->coverKey());
    }
}
