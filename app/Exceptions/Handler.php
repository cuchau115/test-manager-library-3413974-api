<?php

namespace App\Exceptions;

use App\Domain\Exception\BookNotAvailable;
use App\Domain\Exception\DomainError;
use App\Domain\Exception\DuplicateBookInLoan;
use App\Domain\Exception\InvalidDateRange;
use App\Domain\Exception\InvalidQuantity;
use App\Domain\Exception\ItemAlreadyReturned;
use App\Domain\Exception\MemberHasOverdue;
use App\Domain\Exception\MemberLimitExceeded;
use App\Domain\Exception\ValidationError;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $this->renderApiError($e);
            }
        });
    }

    private function renderApiError(Throwable $e): Response
    {
        // Validation errors
        if ($e instanceof ValidationException) {
            return response()->json([
                'code' => 'VALIDATION_ERROR',
                'message' => 'Datos de entrada inválidos',
                'details' => $e->errors(),
            ], 422);
        }

        // Domain errors
        if ($e instanceof DomainError) {
            return $this->renderDomainError($e);
        }

        // Default error
        if ($this->isHttpException($e)) {
            return response()->json([
                'code' => 'ERROR',
                'message' => $e->getMessage(),
                'details' => [],
            ], $e->getStatusCode());
        }

        // Don't expose internal errors in production
        if (!config('app.debug')) {
            return response()->json([
                'code' => 'INTERNAL',
                'message' => 'Error interno del servidor',
                'details' => [],
            ], 500);
        }

        return response()->json([
            'code' => 'INTERNAL',
            'message' => $e->getMessage(),
            'details' => [],
        ], 500);
    }

    private function renderDomainError(DomainError $e): Response
    {
        return match ($e->code()) {
            'BOOK_NOT_AVAILABLE' => response()->json($e->toArray(), 409),
            'MEMBER_HAS_OVERDUE' => response()->json($e->toArray(), 409),
            'MEMBER_LIMIT_EXCEEDED' => response()->json($e->toArray(), 422),
            'ITEM_ALREADY_RETURNED' => response()->json($e->toArray(), 409),
            'DUPLICATE_BOOK_IN_LOAN' => response()->json($e->toArray(), 422),
            'INVALID_DATE_RANGE' => response()->json($e->toArray(), 422),
            'VALIDATION' => response()->json($e->toArray(), 422),
            'ISBN_TAKEN' => response()->json($e->toArray(), 409),
            'DOCUMENT_TAKEN' => response()->json($e->toArray(), 409),
            'BOOK_HAS_OPEN_LOANS' => response()->json($e->toArray(), 409),
            'USERNAME_TAKEN' => response()->json($e->toArray(), 409),
            'FORBIDDEN_ROLE' => response()->json($e->toArray(), 403),
            default => response()->json($e->toArray(), 400),
        };
    }
}