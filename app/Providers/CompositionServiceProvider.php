<?php

namespace App\Providers;

use App\Application\Ports\BookRepository;
use App\Application\Ports\CategoryReader;
use App\Application\Ports\CoverStorage;
use App\Application\Ports\LoanRepository;
use App\Application\Ports\MemberRepository;
use App\Application\Ports\PasswordHasher;
use App\Application\Ports\ReportReader;
use App\Application\Ports\TokenIssuer;
use App\Application\Ports\TransactionManager;
use App\Application\Ports\UserRepository;
use App\Infrastructure\Persistence\Repositories\EloquentBookRepository;
use App\Infrastructure\Persistence\Repositories\EloquentCategoryReader;
use App\Infrastructure\Persistence\Repositories\EloquentLoanRepository;
use App\Infrastructure\Persistence\Repositories\EloquentMemberRepository;
use App\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class CompositionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BookRepository::class, EloquentBookRepository::class);
        $this->app->bind(MemberRepository::class, EloquentMemberRepository::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(CategoryReader::class, EloquentCategoryReader::class);
        $this->app->bind(LoanRepository::class, EloquentLoanRepository::class);
    }
}