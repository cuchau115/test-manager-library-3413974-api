<?php

namespace App\Application\Ports;

use App\Application\Dto\IssuedToken;
use App\Domain\Model\User;

interface TokenIssuer
{
    public function issue(User $user): IssuedToken;
}
