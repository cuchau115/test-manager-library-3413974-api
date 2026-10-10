<?php

namespace App\Infrastructure\Persistence;

use App\Application\Ports\TransactionManager;
use Illuminate\Support\Facades\DB;

final class DatabaseTransactionManager implements TransactionManager
{
    public function run(callable $work): mixed
    {
        return DB::transaction($work);
    }
}
