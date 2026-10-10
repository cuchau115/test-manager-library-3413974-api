<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\LendBooksService;
use App\Application\Services\ReturnItemService;
use App\Application\Services\OverdueService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Loan\CreateLoanRequest;
use App\Http\Requests\Loan\LoanSearchRequest;
use App\Http\Requests\Loan\ReturnItemRequest;
use App\Http\Resources\LoanResource;
use App\Http\Resources\LoanItemResource;
use App\Http\Resources\OverdueItemResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LoanController extends Controller
{
    public function __construct(
        private readonly LendBooksService $lendBooksService,
        private readonly ReturnItemService $returnItemService,
        private readonly OverdueService $overdueService,
    ) {}

    public function store(CreateLoanRequest $request): JsonResponse
    {
        $loan = $this->lendBooksService->execute(
            $request->validated('member_id'),
            $request->validated('items'),
            $request->filled('loaned_at') ? new \DateTimeImmutable($request->validated('loaned_at')) : null,
        );

        return response()->json(new LoanResource($loan), Response::HTTP_CREATED);
    }

    public function index(LoanSearchRequest $request): JsonResponse
    {
        $result = $this->overdueService->search(
            $request->filled('from') ? new \DateTimeImmutable($request->validated('from')) : null,
            $request->filled('to') ? new \DateTimeImmutable($request->validated('to')) : null,
            $request->filled('status') ? $request->validated('status') : null,
            $request->filled('member_id') ? $request->validated('member_id') : null,
            (int) $request->validated('page', 1),
            (int) $request->validated('size', 20),
        );

        $items = array_map(fn ($l) => new LoanResource($l), $result['items']);

        return response()->json([
            'items' => $items,
            'page' => $result['page'],
            'size' => $result['size'],
            'total' => $result['total'],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        // This would need a GetLoanService - for now we'll use a placeholder
        abort(501);
    }

    public function returnItem(ReturnItemRequest $request, string $loanId, string $itemId): JsonResponse
    {
        $item = $this->returnItemService->execute($loanId, $itemId);
        return response()->json(new LoanItemResource($item));
    }

    public function overdue(): JsonResponse
    {
        // The OverdueService returns items with pagination
        // We'll use a simple approach here
        abort(501);
    }
}