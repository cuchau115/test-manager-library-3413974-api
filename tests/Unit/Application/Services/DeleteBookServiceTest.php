<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\DeleteBookService;
use App\Domain\Exception\BookHasOpenLoans;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Model\Book;
use App\Domain\ValueObject\Isbn;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\FakeBookRepository;
use Tests\Support\Fakes\FakeClock;

final class DeleteBookServiceTest extends TestCase
{
    private const NOVELA = '11111111-1111-4111-8111-111111111111';

    private FakeBookRepository $books;

    protected function setUp(): void
    {
        $this->books = new FakeBookRepository();
        $this->books->save(Book::create('b1', 'Libro', 'Autor', Isbn::fromString('9780307474728'), self::NOVELA, 2));
    }

    private function service(): DeleteBookService
    {
        return new DeleteBookService($this->books, new FakeClock(new DateTimeImmutable('2026-10-14T00:00:00Z')));
    }

    public function test_baja_logica(): void
    {
        $this->service()->delete('b1');

        $this->assertNull($this->books->findActiveById('b1'));
        $this->assertNotNull($this->books->findById('b1'));
    }

    public function test_bloquea_si_hay_prestamos_abiertos(): void
    {
        $this->books->openLoanBookIds = ['b1'];

        $this->expectException(BookHasOpenLoans::class);
        $this->service()->delete('b1');
    }

    public function test_libro_inexistente(): void
    {
        $this->expectException(ResourceNotFound::class);
        $this->service()->delete('nope');
    }
}
