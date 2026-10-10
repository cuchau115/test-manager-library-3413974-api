<?php

namespace Tests\Support\Fakes;

use App\Application\Ports\UserRepository;
use App\Domain\Model\User;

final class FakeUserRepository implements UserRepository
{
    /** @var array<string, User> */
    public array $items = [];

    private int $sequence = 0;

    public function nextIdentity(): string
    {
        return 'user-' . (++$this->sequence);
    }

    public function save(User $user): void
    {
        $this->items[$user->id()] = $user;
    }

    public function findById(string $id): ?User
    {
        return $this->items[$id] ?? null;
    }

    public function findByUsername(string $username): ?User
    {
        $normalized = User::normalizeUsername($username);

        foreach ($this->items as $user) {
            if ($user->username() === $normalized) {
                return $user;
            }
        }

        return null;
    }
}
