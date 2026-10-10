<?php

namespace Tests\Support\Fakes;

use App\Application\Dto\IssuedToken;
use App\Application\Ports\TokenIssuer;
use App\Domain\Model\User;

final class FakeTokenIssuer implements TokenIssuer
{
    public function issue(User $user): IssuedToken
    {
        return new IssuedToken('token-' . $user->id(), 'Bearer', 28800, $user->role()->value);
    }
}
