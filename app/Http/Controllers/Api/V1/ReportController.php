<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\MostBorrowedReportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\ReportRequest;
use App\Http\Resources\ReportResource;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly MostBorrowedReportService $reportService,
    ) {}

    public function mostBorrowed(ReportRequest $request): JsonResponse
    {
        $report = $this->reportService->execute(
            $request->validated('from'),
            $request->validated('to'),
        );

        return response()->json(new ReportResource($report));
    }
}