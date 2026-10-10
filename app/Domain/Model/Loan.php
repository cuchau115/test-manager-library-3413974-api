<?php

namespace App\Domain\Model;

use App\Domain\Exception\DuplicateBookInLoan;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Exception\ValidationError;
use DateTimeImmutable;

/**
 * Agregado Préstamo. De 1 a 3 líneas sin repetir libro; el estado se calcula.
 */
final class Loan
{
    public const LOAN_DAYS = 14;
    public const MAX_ITEMS = 3;

    /** @var list<LoanItem> */
    private array $items = [];

    private function __construct(
        private readonly string $id,
        private readonly string $memberId,
        private readonly string $loanedByUserId,
        private readonly DateTimeImmutable $loanedAt,
    ) {
    }

    public static function create(
        string $id,
        string $memberId,
        string $loanedByUserId,
        DateTimeImmutable $loanedAt,
    ): self {
        return new self($id, $memberId, $loanedByUserId, $loanedAt);
    }

    /**
     * @param list<LoanItem> $items
     */
    public static function reconstitute(
        string $id,
        string $memberId,
        string $loanedByUserId,
        DateTimeImmutable $loanedAt,
        array $items,
    ): self {
        $loan = new self($id, $memberId, $loanedByUserId, $loanedAt);
        $loan->items = $items;

        return $loan;
    }

    public function addItem(string $itemId, Book $book, string $categoryName): LoanItem
    {
        if (count($this->items) >= self::MAX_ITEMS) {
            throw new ValidationError(
                sprintf('Un préstamo admite como máximo %d líneas.', self::MAX_ITEMS),
                [['field' => 'items', 'issue' => sprintf('máximo %d', self::MAX_ITEMS)]],
            );
        }

        foreach ($this->items as $existing) {
            if ($existing->bookId() === $book->id()) {
                throw DuplicateBookInLoan::forBook($book->id());
            }
        }

        $book->withdraw(1);

        $dueAt = $this->loanedAt->modify('+' . self::LOAN_DAYS . ' days');

        $item = new LoanItem(
            $itemId,
            $book->id(),
            $book->title(),
            $book->isbn(),
            $categoryName,
            $dueAt,
            null,
        );

        $this->items[] = $item;

        return $item;
    }

    public function returnItem(string $itemId, DateTimeImmutable $at): LoanItem
    {
        foreach ($this->items as $item) {
            if ($item->id() === $itemId) {
                $item->return($at);

                return $item;
            }
        }

        throw ResourceNotFound::forItem($itemId);
    }

    /**
     * Estado calculado: closed si todas las líneas devueltas, overdue si alguna
     * línea sin devolver está vencida, open en el resto de los casos.
     */
    public function status(DateTimeImmutable $now): string
    {
        $anyUnreturned = false;
        $anyOverdue = false;

        foreach ($this->items as $item) {
            if (! $item->isReturned()) {
                $anyUnreturned = true;

                if ($item->isOverdue($now)) {
                    $anyOverdue = true;
                }
            }
        }

        if (! $anyUnreturned) {
            return 'closed';
        }

        return $anyOverdue ? 'overdue' : 'open';
    }

    public function id(): string
    {
        return $this->id;
    }

    public function memberId(): string
    {
        return $this->memberId;
    }

    public function loanedByUserId(): string
    {
        return $this->loanedByUserId;
    }

    public function loanedAt(): DateTimeImmutable
    {
        return $this->loanedAt;
    }

    /** @return list<LoanItem> */
    public function items(): array
    {
        return $this->items;
    }

    public function itemById(string $itemId): LoanItem
    {
        foreach ($this->items as $item) {
            if ($item->id() === $itemId) {
                return $item;
            }
        }

        throw ResourceNotFound::forItem($itemId);
    }
}
