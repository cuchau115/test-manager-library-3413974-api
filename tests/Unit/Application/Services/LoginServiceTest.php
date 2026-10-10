<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\LoginService;
use App\Domain\Enum\Role;
use App\Domain\Exception\InvalidCredentials;
use App\Domain\Model\User;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\FakePasswordHasher;
use Tests\Support\Fakes\FakeTokenIssuer;
use Tests\Support\Fakes\FakeUserRepository;

final class LoginServiceTest extends TestCase
{
    private function service(): LoginService
    {
        $users = new FakeUserRepository();
        $users->save(User::create('u1', 'ana', 'hashed:secret', Role::Librarian));

        return new LoginService($users, new FakePasswordHasher(), new FakeTokenIssuer());
    }

    public function test_credenciales_validas_devuelven_token_con_rol(): void
    {
        $token = $this->service()->login('ana', 'secret');

        $this->assertSame('token-u1', $token->token);
        $this->assertSame('Bearer', $token->tokenType);
        $this->assertSame(28800, $token->expiresIn);
        $this->assertSame('librarian', $token->role);
    }

    public function test_normaliza_el_username(): void
    {
        $this->assertSame('token-u1', $this->service()->login('  ANA ', 'secret')->token);
    }

    public function test_password_incorrecta(): void
    {
        $this->expectException(InvalidCredentials::class);
        $this->service()->login('ana', 'otra');
    }

    public function test_usuario_inexistente(): void
    {
        $this->expectException(InvalidCredentials::class);
        $this->service()->login('nadie', 'secret');
    }
}
