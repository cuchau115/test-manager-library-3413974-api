<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\LoginService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\TokenResource;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginService $loginService,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->loginService->execute(
            $request->validated('username'),
            $request->validated('password'),
        );

        return response()->json(new TokenResource($token));
    }
}