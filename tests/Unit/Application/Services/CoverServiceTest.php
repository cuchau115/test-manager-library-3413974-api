<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\RemoveCoverService;
use App\Application\Services\SetCoverService;
use App\Domain\Model\Book;
use App\Domain\ValueObject\Isbn;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\FakeBookRepository;
use Tests\Support\Fakes\FakeCoverStorage;

final class CoverServiceTest extends TestCase
{
    private const NOVELA = '11111111-1111-4111-8111-111111111111';

    private FakeBookRepository $books;

    private FakeCoverStorage $storage;

    protected function setUp(): void
    {
        $this->books = new FakeBookRepository();
        $this->storage = new FakeCoverStorage();
        $this->books->save(Book::create('b1', 'Libro', 'Autor', Isbn::fromString('9780307474728'), self::NOVELA, 1));
    }

    public function test_guarda_clave_opaca(): void
    {
        $book = (new SetCoverService($this->books, $this->storage))->set('b1', 'bytes', 'image/png');

        $this->assertSame('key-1', $book->coverKey());
        $this->assertSame('bytes', $this->storage->get('key-1'));
    }

    public function test_reemplazar_borra_la_anterior(): void
    {
        $service = new SetCoverService($this->books, $this->storage);
        $service->set('b1', 'uno', 'image/png');
        $service->set('b1', 'dos', 'image/png');

        $this->assertSame(['key-1'], $this->storage->deleted);
        $this->assertSame('key-2', $this->books->findActiveById('b1')->coverKey());
    }

    public function test_quitar_portada_anula_clave_y_borra_binario(): void
    {
        (new SetCoverService($this->books, $this->storage))->set('b1', 'uno', 'image/png');

        $book = (new RemoveCoverService($this->books, $this->storage))->remove('b1');

        $this->assertNull($book->coverKey());
        $this->assertSame(['key-1'], $this->storage->deleted);
    }
}
