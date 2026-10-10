<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\CreateLibrarianService;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly CreateLibrarianService $createLibrarianService,
    ) {}

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->createLibrarianService->create(
            $request->validated('username'),
            $request->validated('password'),
            $request->validated('role'),
        );

        return response()->json([
            'id' => $user->id(),
            'username' => $user->username(),
            'role' => $user->role()->value,
        ], Response::HTTP_CREATED);
    }
}