<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\MostBorrowedReportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\ReportRequest;
use Illuminate\Http\JsonResponse;
use DateTimeImmutable;
use DateTimeZone;

class ReportController extends Controller
{
    public function __construct(
        private readonly MostBorrowedReportService $reportService,
    ) {}

    public function mostBorrowed(ReportRequest $request): JsonResponse
    {
        $from = new DateTimeImmutable($request->validated('from') . ' 00:00:00', new DateTimeZone('UTC'));
        $to = new DateTimeImmutable($request->validated('to') . ' 23:59:59.999999', new DateTimeZone('UTC'));
        $rows = $this->reportService->generate($from, $to);

        return response()->json([
            'from' => $request->validated('from'),
            'to' => $request->validated('to'),
            'rows' => $rows,
        ]);
    }
}