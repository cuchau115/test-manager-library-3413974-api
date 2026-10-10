<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\CreateLibrarianService;
use App\Domain\Enum\Role;
use App\Domain\Exception\UsernameTaken;
use App\Domain\Exception\ValidationError;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\FakePasswordHasher;
use Tests\Support\Fakes\FakeUserRepository;

final class CreateLibrarianTest extends TestCase
{
    private FakeUserRepository $users;

    protected function setUp(): void
    {
        $this->users = new FakeUserRepository();
    }

    private function service(): CreateLibrarianService
    {
        return new CreateLibrarianService($this->users, new FakePasswordHasher());
    }

    public function test_crea_bibliotecario_normalizado_y_con_hash(): void
    {
        $user = $this->service()->create('Ana ', 'secret', 'librarian');

        $this->assertSame('ana', $user->username());
        $this->assertSame(Role::Librarian, $user->role());
        $this->assertSame('hashed:secret', $user->passwordHash());
        $this->assertNotNull($this->users->findByUsername('ana'));
    }

    public function test_rol_admin_es_rechazado(): void
    {
        $this->expectException(ValidationError::class);
        $this->service()->create('ana', 'secret', 'admin');
    }

    public function test_username_repetido(): void
    {
        $service = $this->service();
        $service->create('ana', 'secret', 'librarian');

        $this->expectException(UsernameTaken::class);
        $service->create('Ana', 'secret', 'librarian');
    }
}
