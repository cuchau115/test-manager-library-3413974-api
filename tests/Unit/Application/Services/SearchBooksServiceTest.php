<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\SearchBooksService;
use App\Domain\Model\Book;
use App\Domain\ValueObject\Isbn;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\FakeBookRepository;

final class SearchBooksServiceTest extends TestCase
{
    private const NOVELA = '11111111-1111-4111-8111-111111111111';
    private const CIENCIA = '22222222-2222-4222-8222-222222222222';

    private FakeBookRepository $books;

    protected function setUp(): void
    {
        $this->books = new FakeBookRepository();
        $this->seed('A', 'Cien años de soledad', 'García Márquez', '9780307474728', self::NOVELA);
        $this->seed('B', 'El amor en tiempos', 'García Márquez', '9780307474735', self::NOVELA);
        $this->seed('C', 'Breve historia del tiempo', 'Hawking', '9780307474742', self::CIENCIA);
    }

    private function seed(string $id, string $title, string $author, string $isbn, string $category): void
    {
        $book = Book::create($id, $title, $author, Isbn::fromString($isbn), $category, 1);
        $this->books->save($book);
    }

    private function service(): SearchBooksService
    {
        return new SearchBooksService($this->books);
    }

    public function test_filtra_por_texto_y_ordena_por_titulo(): void
    {
        $result = $this->service()->search('García', null, 1, 20);

        $this->assertSame(2, $result['total']);
        $this->assertSame('Cien años de soledad', $result['items'][0]->title());
    }

    public function test_filtra_por_categoria(): void
    {
        $result = $this->service()->search(null, self::CIENCIA, 1, 20);

        $this->assertSame(1, $result['total']);
        $this->assertSame('Breve historia del tiempo', $result['items'][0]->title());
    }

    public function test_tamano_se_acota_a_cincuenta(): void
    {
        $result = $this->service()->search(null, null, 1, 1000);

        $this->assertSame(3, $result['total']);
        $this->assertSame(3, count($result['items']));
        $this->assertSame(50, SearchBooksService::MAX_SIZE);
    }
}
