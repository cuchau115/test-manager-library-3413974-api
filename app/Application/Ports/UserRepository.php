<?php

namespace App\Application\Ports;

use App\Domain\Model\User;

interface UserRepository
{
    public function nextIdentity(): string;

    public function save(User $user): void;

    public function findById(string $id): ?User;

    public function findByUsername(string $username): ?User;
}
