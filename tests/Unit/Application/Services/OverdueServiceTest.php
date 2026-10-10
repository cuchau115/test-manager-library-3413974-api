<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\OverdueService;
use App\Domain\Model\Book;
use App\Domain\Model\Loan;
use App\Domain\ValueObject\Isbn;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\FakeClock;
use Tests\Support\Fakes\FakeLoanRepository;

final class OverdueServiceTest extends TestCase
{
    public function test_lista_vencidas_ordenadas_por_dias_de_retraso(): void
    {
        $now = new DateTimeImmutable('2026-11-10T00:00:00Z');
        $loans = new FakeLoanRepository();

        $loans->save($this->overdueLoan('L1', 'Libro viejo', '2026-10-14T00:00:00Z', $now));
        $loans->save($this->overdueLoan('L2', 'Libro más viejo', '2026-10-01T00:00:00Z', $now));

        $result = (new OverdueService($loans, new FakeClock($now)))->list(1, 20);

        $this->assertSame(2, $result['total']);
        $this->assertSame('Libro más viejo', $result['items'][0]['book_title']);
        $this->assertTrue($result['items'][0]['days_late'] > $result['items'][1]['days_late']);
    }

    private function overdueLoan(string $id, string $title, string $loanedAt, DateTimeImmutable $now): Loan
    {
        $loan = Loan::create($id, 'm1', 'u1', new DateTimeImmutable($loanedAt));
        $book = Book::create('b-' . $id, $title, 'Autor', Isbn::fromString('9780307474728'), '11111111-1111-4111-8111-111111111111', 2);
        $loan->addItem('i-' . $id, $book, 'Novela');

        return $loan;
    }
}
