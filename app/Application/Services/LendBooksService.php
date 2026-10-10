<?php

namespace App\Application\Services;

use App\Application\Ports\BookRepository;
use App\Application\Ports\CategoryReader;
use App\Application\Ports\Clock;
use App\Application\Ports\LoanRepository;
use App\Application\Ports\MemberRepository;
use App\Application\Ports\TransactionManager;
use App\Domain\Exception\DuplicateBookInLoan;
use App\Domain\Exception\MemberHasOverdue;
use App\Domain\Exception\MemberLimitExceeded;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Exception\ValidationError;
use App\Domain\Model\Book;
use App\Domain\Model\Loan;
use DateTimeImmutable;

/**
 * HU-06: registrar un préstamo. De 1 a 3 líneas, sin repetir libro, con tope de
 * 3 libros sin devolver por socio y sin admitir socios con vencidos.
 */
final class LendBooksService
{
    public function __construct(
        private readonly BookRepository $books,
        private readonly LoanRepository $loans,
        private readonly MemberRepository $members,
        private readonly CategoryReader $categories,
        private readonly Clock $clock,
        private readonly TransactionManager $transaction,
    ) {
    }

    /**
     * @param list<string> $bookIds
     */
    public function lend(
        string $memberId,
        array $bookIds,
        string $loanedByUserId,
        ?DateTimeImmutable $loanedAt = null,
    ): Loan {
        $this->members->findById($memberId) ?? throw ResourceNotFound::forResource('member');

        $bookIds = array_values($bookIds);

        if (count($bookIds) < 1 || count($bookIds) > Loan::MAX_ITEMS) {
            throw new ValidationError(
                'El préstamo debe tener de 1 a 3 líneas.',
                [['field' => 'items', 'issue' => 'debe tener de 1 a 3 elementos']],
            );
        }

        if (count($bookIds) !== count(array_unique($bookIds))) {
            throw DuplicateBookInLoan::forBook($this->firstRepeated($bookIds));
        }

        $now = $this->clock->now();

        if ($this->loans->memberHasOverdue($memberId, $now)) {
            throw MemberHasOverdue::forMember($memberId);
        }

        $activeItems = $this->loans->countOpenItemsByMember($memberId);

        if ($activeItems + count($bookIds) > Loan::MAX_ITEMS) {
            throw MemberLimitExceeded::withActiveLoans($activeItems, Loan::MAX_ITEMS);
        }

        $books = $this->indexById($this->books->findManyActiveByIds($bookIds));

        foreach ($bookIds as $id) {
            if (! isset($books[$id])) {
                throw ResourceNotFound::forResource('book');
            }
        }

        $loan = Loan::create(
            $this->loans->nextIdentity(),
            $memberId,
            $loanedByUserId,
            $loanedAt ?? $now,
        );

        return $this->transaction->run(function () use ($loan, $bookIds, $books): Loan {
            foreach ($bookIds as $id) {
                /** @var Book $book */
                $book = $books[$id];
                $categoryName = $this->categories->name($book->categoryId()) ?? '';
                $loan->addItem($this->loans->nextItemIdentity(), $book, $categoryName);
                $this->books->save($book);
            }

            $this->loans->save($loan);

            return $loan;
        });
    }

    /**
     * @param list<Book> $books
     * @return array<string, Book>
     */
    private function indexById(array $books): array
    {
        $indexed = [];

        foreach ($books as $book) {
            $indexed[$book->id()] = $book;
        }

        return $indexed;
    }

    /**
     * @param list<string> $ids
     */
    private function firstRepeated(array $ids): string
    {
        $seen = [];

        foreach ($ids as $id) {
            if (isset($seen[$id])) {
                return $id;
            }

            $seen[$id] = true;
        }

        return '';
    }
}
