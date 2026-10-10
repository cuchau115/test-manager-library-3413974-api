<?php

namespace App\Domain\Model;

use App\Domain\Enum\Role;

/**
 * Agregado Usuario interno. El username se normaliza (recortado y en minúsculas).
 */
final class User
{
    private function __construct(
        private readonly string $id,
        private string $username,
        private string $passwordHash,
        private Role $role,
    ) {
    }

    public static function create(string $id, string $username, string $passwordHash, Role $role): self
    {
        return new self($id, self::normalizeUsername($username), $passwordHash, $role);
    }

    public static function reconstitute(string $id, string $username, string $passwordHash, Role $role): self
    {
        return new self($id, $username, $passwordHash, $role);
    }

    public static function normalizeUsername(string $username): string
    {
        return mb_strtolower(trim($username));
    }

    public function id(): string
    {
        return $this->id;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function role(): Role
    {
        return $this->role;
    }
}
