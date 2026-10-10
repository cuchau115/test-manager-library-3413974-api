<?php

namespace Tests\Support\Fakes;

use App\Application\Ports\PasswordHasher;

final class FakePasswordHasher implements PasswordHasher
{
    public function hash(string $plainPassword): string
    {
        return 'hashed:' . $plainPassword;
    }

    public function verify(string $plainPassword, string $hash): bool
    {
        return $hash === 'hashed:' . $plainPassword;
    }
}
