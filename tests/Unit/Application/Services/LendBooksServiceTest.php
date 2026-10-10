<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\LendBooksService;
use App\Domain\Exception\DuplicateBookInLoan;
use App\Domain\Exception\MemberHasOverdue;
use App\Domain\Exception\MemberLimitExceeded;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Exception\ValidationError;
use App\Domain\Model\Book;
use App\Domain\Model\Loan;
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

final class LendBooksServiceTest extends TestCase
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
        $this->clock = new FakeClock(new DateTimeImmutable('2026-10-14T14:15:00Z'));
        $this->transaction = new FakeTransactionManager();

        $this->members->save(Member::create('m1', 'Ana Gómez', 'CC-1'));
        $this->book('ba', 3);
        $this->book('bb', 2);
        $this->book('bc', 1);
    }

    private function book(string $id, int $total): void
    {
        $this->books->save(Book::create($id, 'Libro ' . $id, 'Autor', Isbn::fromString('9780307474728'), self::NOVELA, $total));
    }

    private function service(): LendBooksService
    {
        return new LendBooksService(
            $this->books,
            $this->loans,
            $this->members,
            new FakeCategoryReader(),
            $this->clock,
            $this->transaction,
        );
    }

    public function test_presta_dos_libros_y_descuenta_ejemplares(): void
    {
        $loan = $this->service()->lend('m1', ['ba', 'bb'], 'u1');

        $this->assertCount(2, $loan->items());
        $this->assertSame(2, $this->books->findById('ba')->available());
        $this->assertSame(1, $this->books->findById('bb')->available());
        $this->assertSame(1, $this->transaction->runs);
        $this->assertSame('Novela', $loan->items()[0]->categoryName());
        $this->assertEquals(new DateTimeImmutable('2026-10-28T14:15:00Z'), $loan->items()[0]->dueAt());
    }

    public function test_cero_lineas_es_invalido(): void
    {
        $this->expectException(ValidationError::class);
        $this->service()->lend('m1', [], 'u1');
    }

    public function test_mas_de_tres_lineas_es_invalido(): void
    {
        $this->book('bd', 1);
        $this->expectException(ValidationError::class);
        $this->service()->lend('m1', ['ba', 'bb', 'bc', 'bd'], 'u1');
    }

    public function test_libro_repetido(): void
    {
        $this->expectException(DuplicateBookInLoan::class);
        $this->service()->lend('m1', ['ba', 'ba'], 'u1');
    }

    public function test_tope_de_tres_sin_devolver_por_socio(): void
    {
        $previous = Loan::create('L0', 'm1', 'u1', new DateTimeImmutable('2026-10-10T00:00:00Z'));
        $previous->addItem('i0', $this->books->findById('ba'), 'Novela');
        $previous->addItem('i1', $this->books->findById('bb'), 'Novela');
        $this->loans->save($previous);

        $this->expectException(MemberLimitExceeded::class);
        $this->service()->lend('m1', ['ba', 'bb'], 'u1');
    }

    public function test_socio_con_vencidos_no_puede_pedir(): void
    {
        $this->loans->overdueMemberIds = ['m1'];

        $this->expectException(MemberHasOverdue::class);
        $this->service()->lend('m1', ['ba'], 'u1');
    }

    public function test_socio_inexistente(): void
    {
        $this->expectException(ResourceNotFound::class);
        $this->service()->lend('nope', ['ba'], 'u1');
    }

    public function test_libro_inexistente(): void
    {
        $this->expectException(ResourceNotFound::class);
        $this->service()->lend('m1', ['nope'], 'u1');
    }

    public function test_acepta_loaned_at_fijado(): void
    {
        $loan = $this->service()->lend('m1', ['ba'], 'u1', new DateTimeImmutable('2026-01-01T00:00:00Z'));

        $this->assertEquals(new DateTimeImmutable('2026-01-15T00:00:00Z'), $loan->items()[0]->dueAt());
    }
}
