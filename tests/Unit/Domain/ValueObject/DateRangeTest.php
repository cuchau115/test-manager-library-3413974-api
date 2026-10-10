<?php

namespace Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\InvalidDateRange;
use App\Domain\ValueObject\DateRange;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DateRangeTest extends TestCase
{
    public function test_acepta_fin_posterior_al_inicio(): void
    {
        $start = new DateTimeImmutable('2026-10-01T00:00:00Z');
        $end = new DateTimeImmutable('2026-10-31T00:00:00Z');

        $range = DateRange::of($start, $end);

        $this->assertTrue($range->start() == $start);
        $this->assertTrue($range->end() == $end);
        $this->assertTrue($range->contains(new DateTimeImmutable('2026-10-14T14:15:00Z')));
    }

    public function test_acepta_inicio_igual_a_fin(): void
    {
        $same = new DateTimeImmutable('2026-10-14T00:00:00Z');

        $this->assertTrue(DateRange::of($same, $same)->contains($same));
    }

    public function test_rechaza_fin_anterior_al_inicio(): void
    {
        $this->expectException(InvalidDateRange::class);

        DateRange::of(
            new DateTimeImmutable('2026-10-31T00:00:00Z'),
            new DateTimeImmutable('2026-10-01T00:00:00Z'),
        );
    }
}
