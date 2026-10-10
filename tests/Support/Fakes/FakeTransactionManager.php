<?php

namespace Tests\Support\Fakes;

use App\Application\Ports\TransactionManager;

final class FakeTransactionManager implements TransactionManager
{
    public int $runs = 0;

    public function run(callable $work): mixed
    {
        $this->runs++;

        return $work();
    }
}
