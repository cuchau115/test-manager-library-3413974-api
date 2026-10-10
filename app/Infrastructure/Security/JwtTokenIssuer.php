<?php

namespace App\Infrastructure\Security;

use App\Application\Dto\IssuedToken;
use App\Application\Ports\TokenIssuer;
use App\Domain\Model\User;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use DateTimeImmutable;

final class JwtTokenIssuer implements TokenIssuer
{
    private const EXPIRES_IN = 28800; // 8 hours
    private const ALGORITHM = 'HS256';

    public function __construct(
        private readonly string $secretKey,
    ) {
    }

    public function issue(User $user): IssuedToken
    {
        $now = new DateTimeImmutable();
        $expiresAt = $now->modify('+' . self::EXPIRES_IN . ' seconds');

        $signer = new Sha256();
        $key = InMemory::plainText($this->secretKey);

        $token = (new Builder())
            ->issuedBy('manager-library')
            ->permittedFor('manager-library')
            ->identifiedBy($user->id())
            ->issuedAt($now)
            ->expiresAt($expiresAt)
            ->withClaim('role', $user->role()->value)
            ->getToken($signer, $key);

        return new IssuedToken(
            token: $token->toString(),
            tokenType: 'Bearer',
            expiresIn: self::EXPIRES_IN,
            role: $user->role()->value,
        );
    }
}