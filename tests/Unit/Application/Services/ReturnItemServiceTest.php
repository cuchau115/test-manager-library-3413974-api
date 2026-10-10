<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\LendBooksService;
use App\Application\Services\ReturnItemService;
use App\Domain\Exception\ItemAlreadyReturned;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Model\Book;
use App\Domain\Model\Member;
use App\Domain\ValueObject\Isbn;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\FakeBookRepository;
use Tests\Support\Fakes\FakeCategoryReader;
use Tests\Support\Fakes\FakeClock;
use Tests\Support\Fakes\FakeLoanRepository;
use Tests\Support\Fakes\FakeMemberRepository;
use Tests\Support\Fakes\FakeTransactionManager;

final class ReturnItemServiceTest extends TestCase
{
    private const NOVELA = '11111111-1111-4111-8111-111111111111';

    private FakeBookRepository $books;

    private FakeLoanRepository $loans;

    private FakeMemberRepository $members;

    private FakeClock $clock;

    private FakeTransactionManager $transaction;

    protected function setUp(): void
    {
        $this->books = new FakeBookRepository();
        $this->loans = new FakeLoanRepository();
        $this->members = new FakeMemberRepository();
        $this->clock = new FakeClock(new DateTimeImmutable('2026-10-20T10:00:00Z'));
        $this->transaction = new FakeTransactionManager();

        $this->members->save(Member::create('m1', 'Ana', 'CC-1'));
        $this->books->save(Book::create('ba', 'Libro', 'Autor', Isbn::fromString('9780307474728'), self::NOVELA, 3));
    }

    private function lend(): array
    {
        $loan = (new LendBooksService(
            $this->books,
            $this->loans,
            $this->members,
            new FakeCategoryReader(),
            $this->clock,
            $this->transaction,
        ))->lend('m1', ['ba'], 'u1');

        return [$loan, $loan->items()[0]];
    }

    private function service(): ReturnItemService
    {
        return new ReturnItemService($this->loans, $this->books, $this->clock, $this->transaction);
    }

    public function test_devuelve_una_linea_y_restaura_ejemplar(): void
    {
        [$loan, $item] = $this->lend();
        $this->assertSame(2, $this->books->findById('ba')->available());

        $this->service()->returnItem($loan->id(), $item->id());

        $this->assertTrue($item->isReturned());
        $this->assertSame(3, $this->books->findById('ba')->available());
        $this->assertSame('closed', $loan->status($this->clock->now()));
    }

    public function test_doble_devolucion(): void
    {
        [$loan, $item] = $this->lend();
        $service = $this->service();
        $service->returnItem($loan->id(), $item->id());

        $this->expectException(ItemAlreadyReturned::class);
        $service->returnItem($loan->id(), $item->id());
    }

    public function test_prestamo_inexistente(): void
    {
        $this->expectException(ResourceNotFound::class);
        $this->service()->returnItem('nope', 'nope');
    }
}
