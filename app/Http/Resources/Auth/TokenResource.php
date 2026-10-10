<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'access_token' => $this->token,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'role' => $this->role,
        ];
    }
}