<?php

namespace App\Application\Ports;

use App\Domain\Model\Loan;
use DateTimeImmutable;

interface LoanRepository
{
    public function nextIdentity(): string;

    public function nextItemIdentity(): string;

    public function save(Loan $loan): void;

    public function findById(string $id): ?Loan;

    /**
     * Líneas sin devolver de un socio (para el tope de 3).
     */
    public function countOpenItemsByMember(string $memberId): int;

    public function memberHasOverdue(string $memberId, DateTimeImmutable $now): bool;

    /**
     * @return array{items: list<Loan>, total: int}
     */
    public function search(
        ?DateTimeImmutable $from,
        ?DateTimeImmutable $to,
        ?string $status,
        ?string $memberId,
        DateTimeImmutable $now,
        int $page,
        int $size,
    ): array;

    /**
     * @return array{items: list<array{loan_id: string, item_id: string, book_title: string, member_name: string, due_at: DateTimeImmutable, days_late: int}>, total: int}
     */
    public function overdue(DateTimeImmutable $now, int $page, int $size): array;

    /**
     * @return array{items: list<array{member_id: string, member_name: string, book_title: string, due_at: DateTimeImmutable, days_late: int}>, total: int}
     */
    public function overdueByMember(string $memberId, DateTimeImmutable $now): array;
}
