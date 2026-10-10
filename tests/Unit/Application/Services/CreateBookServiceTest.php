<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\CreateBookService;
use App\Domain\Exception\InvalidIsbn;
use App\Domain\Exception\InvalidQuantity;
use App\Domain\Exception\IsbnTaken;
use App\Domain\Exception\ValidationError;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\FakeBookRepository;
use Tests\Support\Fakes\FakeCategoryReader;

final class CreateBookServiceTest extends TestCase
{
    private const NOVELA = '11111111-1111-4111-8111-111111111111';

    private FakeBookRepository $books;

    protected function setUp(): void
    {
        $this->books = new FakeBookRepository();
    }

    private function service(): CreateBookService
    {
        return new CreateBookService($this->books, new FakeCategoryReader());
    }

    public function test_crea_libro_con_disponibles_igual_al_total(): void
    {
        $book = $this->service()->create('Cien años de soledad', 'G. García Márquez', '9780307474728', self::NOVELA, 4);

        $this->assertSame(4, $book->total());
        $this->assertSame(4, $book->available());
        $this->assertNotNull($this->books->findActiveById($book->id()));
    }

    public function test_isbn_repetido_entre_activos(): void
    {
        $service = $this->service();
        $service->create('Uno', 'Autor', '9780307474728', self::NOVELA, 1);

        $this->expectException(IsbnTaken::class);
        $service->create('Dos', 'Autor', '9780307474728', self::NOVELA, 1);
    }

    public function test_categoria_inexistente(): void
    {
        $this->expectException(ValidationError::class);
        $this->service()->create('Uno', 'Autor', '9780307474728', 'no-existe', 1);
    }

    public function test_total_invalido(): void
    {
        $this->expectException(InvalidQuantity::class);
        $this->service()->create('Uno', 'Autor', '9780307474728', self::NOVELA, 0);
    }

    public function test_isbn_invalido(): void
    {
        $this->expectException(InvalidIsbn::class);
        $this->service()->create('Uno', 'Autor', '123', self::NOVELA, 1);
    }
}
