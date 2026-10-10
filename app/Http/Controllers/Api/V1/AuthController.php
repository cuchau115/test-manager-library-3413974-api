<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\LoginService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginService $loginService,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->loginService->login(
            $request->validated('username'),
            $request->validated('password'),
        );

        return response()->json([
            'access_token' => $token->token,
            'token_type' => $token->tokenType,
            'expires_in' => $token->expiresIn,
            'role' => $token->role,
        ]);
    }
}