<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\UpdateBookService;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Exception\ValidationError;
use App\Domain\Model\Book;
use App\Domain\ValueObject\Isbn;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\FakeBookRepository;
use Tests\Support\Fakes\FakeCategoryReader;

final class UpdateBookServiceTest extends TestCase
{
    private const NOVELA = '11111111-1111-4111-8111-111111111111';
    private const CIENCIA = '22222222-2222-4222-8222-222222222222';

    private FakeBookRepository $books;

    protected function setUp(): void
    {
        $this->books = new FakeBookRepository();
        $this->books->save(Book::create('b1', 'Viejo', 'Autor', Isbn::fromString('9780307474728'), self::NOVELA, 3));
    }

    private function service(): UpdateBookService
    {
        return new UpdateBookService($this->books, new FakeCategoryReader());
    }

    public function test_reemplaza_detalles_y_ajusta_disponibles(): void
    {
        $book = $this->service()->update('b1', 'Nuevo', 'Otro', self::CIENCIA, 5);

        $this->assertSame('Nuevo', $book->title());
        $this->assertSame(self::CIENCIA, $book->categoryId());
        $this->assertSame(5, $book->total());
        $this->assertSame(5, $book->available());
    }

    public function test_total_que_deja_disponibles_negativos(): void
    {
        $book = $this->books->findActiveById('b1');
        $book->withdraw();
        $book->withdraw();
        $book->withdraw();

        $this->expectException(ValidationError::class);
        $this->service()->update('b1', 'Nuevo', 'Otro', self::NOVELA, 2);
    }

    public function test_libro_inexistente(): void
    {
        $this->expectException(ResourceNotFound::class);
        $this->service()->update('nope', 'Nuevo', 'Otro', self::NOVELA, 1);
    }
}
