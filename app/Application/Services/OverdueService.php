<?php

namespace App\Application\Services;

use App\Application\Ports\Clock;
use App\Application\Ports\LoanRepository;

/**
 * HU-09: líneas vencidas, ordenadas por días de retraso.
 */
final class OverdueService
{
    public const MAX_SIZE = 50;

    public function __construct(
        private readonly LoanRepository $loans,
        private readonly Clock $clock,
    ) {
    }

    /**
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function list(int $page, int $size): array
    {
        $page = max(1, $page);
        $size = min(max(1, $size), self::MAX_SIZE);

        return $this->loans->overdue($this->clock->now(), $page, $size);
    }
}
