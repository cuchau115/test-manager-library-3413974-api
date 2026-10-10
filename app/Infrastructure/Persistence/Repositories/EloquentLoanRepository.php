<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Ports\LoanRepository;
use App\Domain\Model\Loan;
use App\Domain\Model\LoanItem;
use App\Infrastructure\Persistence\Models\LoanItemModel;
use App\Infrastructure\Persistence\Models\LoanModel;
use DateTimeImmutable;

final class EloquentLoanRepository implements LoanRepository
{
    public function nextIdentity(): string
    {
        return (string) \Ramsey\Uuid\Uuid::uuid4();
    }

    public function nextItemIdentity(): string
    {
        return (string) \Ramsey\Uuid\Uuid::uuid4();
    }

    public function save(Loan $loan): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($loan) {
            $loanModel = LoanModel::find($loan->id());
            if ($loanModel === null) {
                $loanModel = new LoanModel();
                $loanModel->id = $loan->id();
                $loanModel->member_id = $loan->memberId();
                $loanModel->loaned_by_user_id = $loan->loanedByUserId();
                $loanModel->loaned_at = $loan->loanedAt();
                $loanModel->save();
            }

            // Get existing item IDs
            $existingItemIds = LoanItemModel::where('loan_id', $loan->id())->pluck('id')->toArray();
            $newItemIds = [];

            foreach ($loan->items() as $item) {
                $itemModel = LoanItemModel::find($item->id());
                if ($itemModel === null) {
                    $itemModel = new LoanItemModel();
                    $itemModel->id = $item->id();
                    $itemModel->loan_id = $loan->id();
                    $itemModel->book_id = $item->bookId();
                    $itemModel->book_title = $item->bookTitle();
                    $itemModel->isbn = $item->isbn()->value();
                    $itemModel->category_name = $item->categoryName();
                    $itemModel->due_at = $item->dueAt();
                    $itemModel->returned_at = $item->returnedAt();
                    $itemModel->save();
                } else {
                    $itemModel->returned_at = $item->returnedAt();
                    $itemModel->save();
                }
                $newItemIds[] = $item->id();
            }

            // Delete items that are no longer in the loan
            $toDelete = array_diff($existingItemIds, $newItemIds);
            if (!empty($toDelete)) {
                LoanItemModel::whereIn('id', $toDelete)->delete();
            }
        });
    }

    public function findById(string $id): ?Loan
    {
        $model = LoanModel::with('items')->find($id);
        if ($model === null) {
            return null;
        }

        $items = $model->items->map(fn ($i) => new LoanItem(
            $i->id,
            $i->book_id,
            $i->book_title,
            \App\Domain\ValueObject\Isbn::fromString($i->isbn),
            $i->category_name,
            new DateTimeImmutable($i->due_at),
            $i->returned_at ? new DateTimeImmutable($i->returned_at) : null,
        ))->all();

        return Loan::reconstitute(
            $model->id,
            $model->member_id,
            $model->loaned_by_user_id,
            new DateTimeImmutable($model->loaned_at),
            $items,
        );
    }

    public function countOpenItemsByMember(string $memberId): int
    {
        return LoanItemModel::whereHas('loan', fn ($q) => $q->where('member_id', $memberId))
            ->whereNull('returned_at')
            ->count();
    }

    public function memberHasOverdue(string $memberId, DateTimeImmutable $now): bool
    {
        return LoanItemModel::whereHas('loan', fn ($q) => $q->where('member_id', $memberId))
            ->whereNull('returned_at')
            ->where('due_at', '<', $now->format('Y-m-d H:i:s'))
            ->exists();
    }

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
    ): array {
        $query = LoanModel::with('items')->with('member');

        if ($from !== null) {
            $query->where('loaned_at', '>=', $from->format('Y-m-d H:i:s'));
        }
        if ($to !== null) {
            $query->where('loaned_at', '<=', $to->format('Y-m-d H:i:s'));
        }
        if ($memberId !== null && $memberId !== '') {
            $query->where('member_id', $memberId);
        }

        if ($status === 'closed') {
            $query->whereDoesntHave('items', fn ($items) => $items->whereNull('returned_at'));
        } elseif ($status === 'overdue') {
            $query->whereHas('items', fn ($items) => $items
                ->whereNull('returned_at')
                ->where('due_at', '<', $now->format('Y-m-d H:i:s')));
        } elseif ($status === 'open') {
            $query->whereHas('items', fn ($items) => $items
                ->whereNull('returned_at')
                ->where('due_at', '>=', $now->format('Y-m-d H:i:s')))
                ->whereDoesntHave('items', fn ($items) => $items
                    ->whereNull('returned_at')
                    ->where('due_at', '<', $now->format('Y-m-d H:i:s')));
        }

        $query->orderBy('loaned_at', 'desc');

        $total = $query->count();
        $models = $query->skip(($page - 1) * $size)->take($size)->get();

        $items = $models->map(fn ($model) => $this->toDomain($model))->all();

        return ['items' => $items, 'total' => $total];
    }

    /**
     * @return array{items: list<array{loan_id: string, item_id: string, book_title: string, member_name: string, due_at: DateTimeImmutable, days_late: int}>, total: int}
     */
    public function overdue(DateTimeImmutable $now, int $page, int $size): array
    {
        $query = LoanItemModel::with('loan.member')
            ->whereNull('returned_at')
            ->where('due_at', '<', $now->format('Y-m-d H:i:s'))
            ->orderBy('due_at');

        $total = $query->count();
        $models = $query->skip(($page - 1) * $size)->take($size)->get();

        $items = $models->map(function ($model) use ($now) {
            $dueAt = new DateTimeImmutable($model->due_at);
            $daysLate = (int) $now->diff($dueAt)->days;
            return [
                'loan_id' => $model->loan_id,
                'item_id' => $model->id,
                'book_title' => $model->book_title,
                'member_name' => $model->loan?->member?->full_name ?? '',
                'due_at' => $dueAt,
                'days_late' => $daysLate,
            ];
        })->all();

        return ['items' => $items, 'total' => $total];
    }

    /**
     * @return array{items: list<array{member_id: string, member_name: string, book_title: string, due_at: DateTimeImmutable, days_late: int}>, total: int}
     */
    public function overdueByMember(string $memberId, DateTimeImmutable $now): array
    {
        $models = LoanItemModel::with('loan.member')
            ->whereHas('loan', fn ($q) => $q->where('member_id', $memberId))
            ->whereNull('returned_at')
            ->where('due_at', '<', $now->format('Y-m-d H:i:s'))
            ->orderBy('due_at')
            ->get();

        $items = $models->map(function ($model) use ($now) {
            $dueAt = new DateTimeImmutable($model->due_at);
            $daysLate = (int) $now->diff($dueAt)->days;
            return [
                'member_id' => $model->loan?->member_id ?? $memberId,
                'member_name' => $model->loan?->member?->full_name ?? '',
                'book_title' => $model->book_title,
                'due_at' => $dueAt,
                'days_late' => $daysLate,
            ];
        })->all();

        return ['items' => $items, 'total' => count($items)];
    }

    private function toDomain(LoanModel $model): Loan
    {
        $items = $model->items->map(fn ($i) => new LoanItem(
            $i->id,
            $i->book_id,
            $i->book_title,
            \App\Domain\ValueObject\Isbn::fromString($i->isbn),
            $i->category_name,
            new DateTimeImmutable($i->due_at),
            $i->returned_at ? new DateTimeImmutable($i->returned_at) : null,
        ))->all();

        return Loan::reconstitute(
            $model->id,
            $model->member_id,
            $model->loaned_by_user_id,
            new DateTimeImmutable($model->loaned_at),
            $items,
        );
    }
}