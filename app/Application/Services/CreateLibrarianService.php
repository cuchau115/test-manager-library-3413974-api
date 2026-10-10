<?php

namespace App\Application\Services;

use App\Application\Ports\PasswordHasher;
use App\Application\Ports\UserRepository;
use App\Domain\Enum\Role;
use App\Domain\Exception\UsernameTaken;
use App\Domain\Exception\ValidationError;
use App\Domain\Model\User;

/**
 * HU-02: crear un bibliotecario. La vía por API no admite el rol admin.
 */
final class CreateLibrarianService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasher $hasher,
    ) {
    }

    public function create(string $username, string $password, string $role): User
    {
        if (Role::fromString($role) !== Role::Librarian) {
            throw new ValidationError(
                'El rol solicitado no está permitido.',
                [['field' => 'role', 'issue' => 'solo se admite librarian']],
            );
        }

        $normalized = User::normalizeUsername($username);

        if ($normalized === '' || trim($password) === '') {
            throw new ValidationError(
                'Usuario y contraseña son obligatorios.',
                [['issue' => 'usuario y contraseña obligatorios']],
            );
        }

        if ($this->users->findByUsername($normalized) !== null) {
            throw UsernameTaken::forValue($normalized);
        }

        $user = User::create(
            $this->users->nextIdentity(),
            $normalized,
            $this->hasher->hash($password),
            Role::Librarian,
        );

        $this->users->save($user);

        return $user;
    }
}
