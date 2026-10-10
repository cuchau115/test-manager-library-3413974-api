<?php

namespace Tests\Unit\Domain\Model;

use App\Domain\Enum\Role;
use App\Domain\Model\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function test_normaliza_username(): void
    {
        $this->assertSame('ana', User::normalizeUsername('Ana '));
        $this->assertSame('ana', User::create('U1', 'Ana ', 'hash', Role::Librarian)->username());
        $this->assertSame('admin', User::create('U2', '  ADMIN', 'hash', Role::Admin)->username());
    }

    public function test_rol(): void
    {
        $user = User::create('U1', 'ana', 'hash', Role::Librarian);

        $this->assertSame(Role::Librarian, $user->role());
        $this->assertSame('librarian', $user->role()->value);
    }

    public function test_rol_desde_texto(): void
    {
        $this->assertSame(Role::Admin, Role::fromString(' admin '));
    }
}
