<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Ports\UserRepository;
use App\Domain\Model\User;
use App\Domain\Enum\Role;
use App\Infrastructure\Persistence\Models\UserModel;

final class EloquentUserRepository implements UserRepository
{
    public function nextIdentity(): string
    {
        return (string) \Ramsey\Uuid\Uuid::uuid4();
    }

    public function save(User $user): void
    {
        $model = UserModel::find($user->id());
        if ($model === null) {
            $model = new UserModel();
            $model->id = $user->id();
        }

        $model->username = $user->username();
        $model->password_hash = $user->passwordHash();
        $model->role = $user->role()->value;
        $model->save();
    }

    public function findById(string $id): ?User
    {
        $model = UserModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $model = UserModel::where('username', $username)->first();
        return $model ? $this->toDomain($model) : null;
    }

    private function toDomain(UserModel $model): User
    {
        return User::reconstitute(
            $model->id,
            $model->username,
            $model->password_hash,
            Role::from($model->role),
        );
    }
}