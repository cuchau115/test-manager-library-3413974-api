<?php

namespace Tests\Support\Fakes;

use App\Application\Ports\LoanRepository;
use App\Domain\Model\Loan;
use DateTimeImmutable;

final class FakeLoanRepository implements LoanRepository
{
    /** @var array<string, Loan> */
    public array $items = [];

    /** @var list<string> */
    public array $overdueMemberIds = [];

    private int $sequence = 0;

    private int $itemSequence = 0;

    public function nextIdentity(): string
    {
        return 'loan-' . (++$this->sequence);
    }

    public function nextItemIdentity(): string
    {
        return 'item-' . (++$this->itemSequence);
    }

    public function save(Loan $loan): void
    {
        $this->items[$loan->id()] = $loan;
    }

    public function findById(string $id): ?Loan
    {
        return $this->items[$id] ?? null;
    }

    public function countOpenItemsByMember(string $memberId): int
    {
        $count = 0;

        foreach ($this->items as $loan) {
            if ($loan->memberId() !== $memberId) {
                continue;
            }

            foreach ($loan->items() as $item) {
                if (! $item->isReturned()) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function memberHasOverdue(string $memberId, DateTimeImmutable $now): bool
    {
        if (in_array($memberId, $this->overdueMemberIds, true)) {
            return true;
        }

        foreach ($this->items as $loan) {
            if ($loan->memberId() === $memberId && $loan->status($now) === 'overdue') {
                return true;
            }
        }

        return false;
    }

    public function search(
        ?DateTimeImmutable $from,
        ?DateTimeImmutable $to,
        ?string $status,
        ?string $memberId,
        DateTimeImmutable $now,
        int $page,
        int $size,
    ): array {
        $list = array_values($this->items);

        if ($memberId !== null) {
            $list = array_values(array_filter($list, fn (Loan $l): bool => $l->memberId() === $memberId));
        }

        if ($status !== null) {
            $list = array_values(array_filter($list, fn (Loan $l): bool => $l->status($now) === $status));
        }

        $total = count($list);

        return ['items' => array_slice($list, ($page - 1) * $size, $size), 'total' => $total];
    }

    public function overdue(DateTimeImmutable $now, int $page, int $size): array
    {
        $rows = [];

        foreach ($this->items as $loan) {
            foreach ($loan->items() as $item) {
                if ($item->isOverdue($now)) {
                    $rows[] = [
                        'loan_id' => $loan->id(),
                        'item_id' => $item->id(),
                        'book_title' => $item->bookTitle(),
                        'member_name' => '',
                        'due_at' => $item->dueAt(),
                        'days_late' => $item->daysLate($now),
                    ];
                }
            }
        }

        usort($rows, fn (array $a, array $b): int => $b['days_late'] <=> $a['days_late']);
        $total = count($rows);

        return ['items' => array_slice($rows, ($page - 1) * $size, $size), 'total' => $total];
    }

    public function overdueByMember(string $memberId, DateTimeImmutable $now): array
    {
        $rows = [];

        foreach ($this->items as $loan) {
            if ($loan->memberId() !== $memberId) {
                continue;
            }

            foreach ($loan->items() as $item) {
                if ($item->isOverdue($now)) {
                    $rows[] = [
                        'member_id' => $memberId,
                        'member_name' => '',
                        'book_title' => $item->bookTitle(),
                        'due_at' => $item->dueAt(),
                        'days_late' => $item->daysLate($now),
                    ];
                }
            }
        }

        return ['items' => $rows, 'total' => count($rows)];
    }
}
