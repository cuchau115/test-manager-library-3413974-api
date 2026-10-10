<?php

namespace App\Infrastructure\Security;

use App\Application\Ports\TokenVerifier;
use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Throwable;

final class JwtTokenVerifier implements TokenVerifier
{
    public function __construct(private readonly string $secretKey)
    {
    }

    public function verify(string $token): ?array
    {
        try {
            $signer = new Sha256();
            $key = InMemory::plainText($this->secretKey);
            $configuration = Configuration::forSymmetricSigner($signer, $key);
            $jwt = $configuration->parser()->parse($token);

            if (!$configuration->validator()->validate($jwt, new SignedWith($signer, $key))) {
                return null;
            }

            $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
            if ($jwt->isExpired($now)) {
                return null;
            }

            $claims = $jwt->claims();
            $userId = $claims->get('sub');
            $role = $claims->get('role');
            if (
                !is_string($userId)
                || $userId === ''
                || !in_array($role, ['admin', 'librarian'], true)
                || $claims->get('iss') !== 'manager-library'
                || !in_array('manager-library', (array) $claims->get('aud'), true)
            ) {
                return null;
            }

            if ($claims->has('nbf') && $claims->get('nbf') > $now) {
                return null;
            }

            return ['user_id' => $userId, 'role' => $role];
        } catch (Throwable) {
            return null;
        }
    }
}
