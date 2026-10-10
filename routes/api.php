<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\LoanController;
use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\HealthController;
use App\Http\Middleware\AuthenticateJwt;
use App\Http\Middleware\RequireRole;
use Illuminate\Support\Facades\Route;

// Public health endpoints
Route::get('/health', HealthController::class);
Route::get('/v1/health', HealthController::class);

// Auth routes (public)
Route::post('/v1/auth/login', [AuthController::class, 'login']);

// Protected routes (require JWT)
Route::middleware(AuthenticateJwt::class)->group(function () {
    // Categories
    Route::get('/v1/categories', [CategoryController::class, 'index']);

    // Books
    Route::get('/v1/books', [BookController::class, 'index']);
    Route::get('/v1/books/{id}', [BookController::class, 'show']);
    Route::post('/v1/books', [BookController::class, 'store'])->middleware(RequireRole::class . ':admin');
    Route::put('/v1/books/{id}', [BookController::class, 'update'])->middleware(RequireRole::class . ':admin');
    Route::delete('/v1/books/{id}', [BookController::class, 'destroy'])->middleware(RequireRole::class . ':admin');
    Route::put('/v1/books/{id}/cover', [BookController::class, 'updateCover'])->middleware(RequireRole::class . ':admin');
    Route::get('/v1/books/{id}/cover', [BookController::class, 'cover']);
    Route::delete('/v1/books/{id}/cover', [BookController::class, 'deleteCover'])->middleware(RequireRole::class . ':admin');

    // Members
    Route::get('/v1/members', [MemberController::class, 'index']);
    Route::post('/v1/members', [MemberController::class, 'store']);
    Route::get('/v1/members/{id}', [MemberController::class, 'show']);

    // Users (admin only)
    Route::post('/v1/users', [UserController::class, 'store'])->middleware(RequireRole::class . ':admin');

    // Loans
    Route::post('/v1/loans', [LoanController::class, 'store']);
    Route::get('/v1/loans', [LoanController::class, 'index']);
    Route::get('/v1/loans/overdue', [LoanController::class, 'overdue']);
    Route::get('/v1/loans/{id}', [LoanController::class, 'show']);
    Route::post('/v1/loans/{loan_id}/items/{item_id}/return', [LoanController::class, 'returnItem']);

    // Reports (admin only)
    Route::get('/v1/reports/most-borrowed', [ReportController::class, 'mostBorrowed'])->middleware(RequireRole::class . ':admin');
});