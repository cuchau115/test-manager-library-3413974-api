<?php

namespace App\Application\Services;

use App\Application\Ports\Clock;
use App\Application\Ports\LoanRepository;
use DateTimeImmutable;

final class ListLoansService
{
    public const MAX_SIZE = 50;

    public function __construct(
        private readonly LoanRepository $loans,
        private readonly Clock $clock,
    ) {
    }

    /**
     * @return array{items: list<\App\Domain\Model\Loan>, total: int}
     */
    public function list(
        ?DateTimeImmutable $from,
        ?DateTimeImmutable $to,
        ?string $status,
        ?string $memberId,
        int $page,
        int $size,
    ): array {
        $page = max(1, $page);
        $size = min(max(1, $size), self::MAX_SIZE);

        return $this->loans->search($from, $to, $status, $memberId, $this->clock->now(), $page, $size);
    }
}
