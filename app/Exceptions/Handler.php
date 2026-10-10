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
            $details = [];
            foreach ($e->errors() as $field => $issues) {
                foreach ($issues as $issue) {
                    $details[] = [
                        'field' => preg_replace_callback('/\.(\d+)\./', fn ($match) => '[' . $match[1] . '].', $field),
                        'issue' => $issue,
                    ];
                }
            }

            return response()->json([
                'code' => 'VALIDATION_ERROR',
                'message' => 'Datos de entrada inválidos',
                'details' => $details,
            ], 422);
        }

        // Domain errors
        if ($e instanceof DomainError) {
            return $this->renderDomainError($e);
        }

        // Default error
        if ($this->isHttpException($e)) {
            $status = $e->getStatusCode();
            [$code, $message] = match ($status) {
                401 => ['INVALID_CREDENTIALS', 'Sesión no válida o vencida'],
                403 => ['FORBIDDEN_ROLE', 'Rol insuficiente'],
                404 => ['NOT_FOUND', 'Recurso no encontrado'],
                422 => ['VALIDATION_ERROR', 'Datos de entrada inválidos'],
                default => ['ERROR', 'No fue posible completar la solicitud'],
            };

            return response()->json([
                'code' => $code,
                'message' => $message,
                'details' => [],
            ], $status);
        }

        return response()->json([
            'code' => 'INTERNAL',
            'message' => 'Error interno del servidor',
            'details' => [],
        ], 500);
    }

    private function renderDomainError(DomainError $e): Response
    {
        $businessCode = $e->errorCode();
        $status = match ($businessCode) {
            'INVALID_CREDENTIALS' => 401,
            'FORBIDDEN_ROLE' => 403,
            'NOT_FOUND' => 404,
            'USERNAME_TAKEN',
            'ISBN_TAKEN',
            'DOCUMENT_TAKEN',
            'BOOK_HAS_OPEN_LOANS',
            'BOOK_NOT_AVAILABLE',
            'MEMBER_HAS_OVERDUE',
            'ITEM_ALREADY_RETURNED' => 409,
            default => 422,
        };

        $code = in_array($businessCode, [
            'INVALID_CREDENTIALS',
            'FORBIDDEN_ROLE',
            'NOT_FOUND',
            'USERNAME_TAKEN',
            'ISBN_TAKEN',
            'DOCUMENT_TAKEN',
            'BOOK_HAS_OPEN_LOANS',
            'BOOK_NOT_AVAILABLE',
            'MEMBER_HAS_OVERDUE',
            'ITEM_ALREADY_RETURNED',
            'MEMBER_LIMIT_EXCEEDED',
            'DUPLICATE_BOOK_IN_LOAN',
            'INVALID_DATE_RANGE',
        ], true) ? $businessCode : 'VALIDATION_ERROR';

        return response()->json([
            'code' => $code,
            'message' => $e->getMessage(),
            'details' => $e->details(),
        ], $status);
    }
}