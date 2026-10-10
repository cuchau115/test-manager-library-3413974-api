<?php

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidDateRange;
use DateTimeImmutable;

/**
 * Rango de fechas cerrado [start, end]. Inmutable.
 */
final class DateRange
{
    private function __construct(
        private readonly DateTimeImmutable $start,
        private readonly DateTimeImmutable $end,
    ) {
    }

    public static function of(DateTimeImmutable $start, DateTimeImmutable $end): self
    {
        if ($end < $start) {
            throw InvalidDateRange::endBeforeStart();
        }

        return new self($start, $end);
    }

    public function start(): DateTimeImmutable
    {
        return $this->start;
    }

    public function end(): DateTimeImmutable
    {
        return $this->end;
    }

    public function contains(DateTimeImmutable $moment): bool
    {
        return $moment >= $this->start && $moment <= $this->end;
    }
}
