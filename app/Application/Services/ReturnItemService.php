<?php

namespace App\Application\Services;

use App\Application\Ports\BookRepository;
use App\Application\Ports\Clock;
use App\Application\Ports\LoanRepository;
use App\Application\Ports\TransactionManager;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Model\Loan;

/**
 * HU-07: devolver una línea del préstamo una sola vez.
 */
final class ReturnItemService
{
    public function __construct(
        private readonly LoanRepository $loans,
        private readonly BookRepository $books,
        private readonly Clock $clock,
        private readonly TransactionManager $transaction,
    ) {
    }

    public function returnItem(string $loanId, string $itemId): Loan
    {
        $loan = $this->loans->findById($loanId) ?? throw ResourceNotFound::forResource('loan');

        return $this->transaction->run(function () use ($loan, $itemId): Loan {
            $item = $loan->returnItem($itemId, $this->clock->now());

            $book = $this->books->findById($item->bookId());

            if ($book !== null) {
                $book->restock();
                $this->books->save($book);
            }

            $this->loans->save($loan);

            return $loan;
        });
    }
}
