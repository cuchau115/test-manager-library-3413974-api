<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\LendBooksService;
use App\Application\Services\GetLoanService;
use App\Application\Services\ListLoansService;
use App\Application\Services\OverdueService;
use App\Application\Services\ReturnItemService;
use App\Domain\Model\Loan;
use App\Domain\Model\LoanItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Loan\CreateLoanRequest;
use App\Http\Requests\Loan\LoanSearchRequest;
use App\Http\Requests\Loan\ReturnItemRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LoanController extends Controller
{
    public function __construct(
        private readonly LendBooksService $lendBooksService,
        private readonly ReturnItemService $returnItemService,
        private readonly GetLoanService $getLoanService,
        private readonly ListLoansService $listLoansService,
        private readonly OverdueService $overdueService,
    ) {}

    public function store(CreateLoanRequest $request): JsonResponse
    {
        $loanedAt = config('app.env') === 'local' && $request->filled('loaned_at')
            ? new \DateTimeImmutable($request->validated('loaned_at'))
            : null;

        $loan = $this->lendBooksService->lend(
            $request->validated('member_id'),
            array_column($request->validated('items'), 'book_id'),
            $request->attributes->get('auth.user_id'),
            $loanedAt,
        );

        return response()->json($this->loanData($loan), Response::HTTP_CREATED);
    }

    public function index(LoanSearchRequest $request): JsonResponse
    {
        $from = $request->filled('from')
            ? new \DateTimeImmutable($request->validated('from') . ' 00:00:00', new \DateTimeZone('UTC'))
            : null;
        $to = $request->filled('to')
            ? new \DateTimeImmutable($request->validated('to') . ' 23:59:59.999999', new \DateTimeZone('UTC'))
            : null;
        $result = $this->listLoansService->list(
            $from,
            $to,
            $request->filled('status') ? $request->validated('status') : null,
            $request->filled('member_id') ? $request->validated('member_id') : null,
            (int) $request->validated('page', 1),
            (int) $request->validated('size', 20),
        );

        $items = array_map(fn (Loan $loan) => $this->loanData($loan), $result['items']);

        return response()->json([
            'items' => $items,
            'page' => (int) $request->validated('page', 1),
            'size' => (int) $request->validated('size', 20),
            'total' => $result['total'],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json($this->loanData($this->getLoanService->get($id)));
    }

    public function returnItem(ReturnItemRequest $request, string $loanId, string $itemId): JsonResponse
    {
        $loan = $this->returnItemService->returnItem($loanId, $itemId);

        return response()->json($this->itemData($loan->itemById($itemId)));
    }

    public function overdue(LoanSearchRequest $request): JsonResponse
    {
        $page = (int) $request->validated('page', 1);
        $size = (int) $request->validated('size', 20);
        $result = $this->overdueService->list($page, $size);

        return response()->json([
            'items' => array_map(fn (array $item) => [
                'loan_id' => $item['loan_id'],
                'item_id' => $item['item_id'],
                'book_title' => $item['book_title'],
                'member_name' => $item['member_name'],
                'due_at' => $this->dateTime($item['due_at']),
                'days_late' => $item['days_late'],
            ], $result['items']),
            'page' => $page,
            'size' => $size,
            'total' => $result['total'],
        ]);
    }

    private function loanData(Loan $loan): array
    {
        return [
            'id' => $loan->id(),
            'member_id' => $loan->memberId(),
            'loaned_at' => $this->dateTime($loan->loanedAt()),
            'status' => $loan->status(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
            'items' => array_map(fn (LoanItem $item) => $this->itemData($item), $loan->items()),
        ];
    }

    private function itemData(LoanItem $item): array
    {
        return [
            'id' => $item->id(),
            'book_id' => $item->bookId(),
            'book_title' => $item->bookTitle(),
            'isbn' => $item->isbn()->value(),
            'category_name' => $item->categoryName(),
            'due_at' => $this->dateTime($item->dueAt()),
            'returned_at' => $item->returnedAt() === null ? null : $this->dateTime($item->returnedAt()),
        ];
    }

    private function dateTime(\DateTimeInterface $value): string
    {
        return \DateTimeImmutable::createFromInterface($value)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z');
    }
}