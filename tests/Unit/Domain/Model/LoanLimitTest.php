<?php

namespace Tests\Unit\Domain\Model;

use App\Domain\Model\Loan;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class LoanLimitTest extends TestCase
{
    public function test_plazo_de_catorce_dias_y_maximo_tres_ejemplares(): void
    {
        $this->assertSame(14, Loan::LOAN_DAYS);
        $this->assertSame(3, Loan::MAX_ITEMS);
    }

    public function test_usa_el_reloj_suministrado_para_el_vencimiento(): void
    {
        $loanedAt = new DateTimeImmutable('2026-10-14T14:15:00Z');
        $loan = Loan::create('L1', 'M1', 'U1', $loanedAt);

        $this->assertSame('2026-10-28T14:15:00Z', $loan->loanedAt()->modify('+14 days')->format('Y-m-d\TH:i:s\Z'));
    }
}
