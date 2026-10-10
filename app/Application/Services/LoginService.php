<?php

namespace App\Application\Services;

use App\Application\Dto\IssuedToken;
use App\Application\Ports\PasswordHasher;
use App\Application\Ports\TokenIssuer;
use App\Application\Ports\UserRepository;
use App\Domain\Exception\InvalidCredentials;
use App\Domain\Model\User;

/**
 * HU-01: inicio de sesión.
 */
final class LoginService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasher $hasher,
        private readonly TokenIssuer $tokens,
    ) {
    }

    public function login(string $username, string $password): IssuedToken
    {
        $user = $this->users->findByUsername(User::normalizeUsername($username));

        if ($user === null || ! $this->hasher->verify($password, $user->passwordHash())) {
            throw InvalidCredentials::generic();
        }

        return $this->tokens->issue($user);
    }
}
