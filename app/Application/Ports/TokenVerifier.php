<?php

namespace App\Application\Ports;

interface TokenVerifier
{
    /**
     * @return array{user_id: string, role: string}|null
     */
    public function verify(string $token): ?array;
}
