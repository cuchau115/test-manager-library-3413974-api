<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Ports\ReportReader;
use App\Domain\ValueObject\DateRange;
use Illuminate\Support\Facades\DB;

final class EloquentReportReader implements ReportReader
{
    /**
     * @return list<array{book_id: string, book_title: string, category_name: string, times_borrowed: int}>
     */
    public function mostBorrowed(DateRange $range): array
    {
        return DB::table('library.loan_item as item')
            ->join('library.loan as loan', 'loan.id', '=', 'item.loan_id')
            ->where('loan.loaned_at', '>=', $range->start()->format('Y-m-d H:i:s.uP'))
            ->where('loan.loaned_at', '<=', $range->end()->format('Y-m-d H:i:s.uP'))
            ->groupBy('item.book_id', 'item.book_title', 'item.category_name')
            ->orderByDesc('times_borrowed')
            ->orderBy('item.book_title')
            ->get([
                'item.book_id',
                'item.book_title',
                'item.category_name',
                DB::raw('COUNT(*) AS times_borrowed'),
            ])
            ->map(fn ($row) => [
                'book_id' => $row->book_id,
                'book_title' => $row->book_title,
                'category_name' => $row->category_name,
                'times_borrowed' => (int) $row->times_borrowed,
            ])
            ->all();
    }
}
