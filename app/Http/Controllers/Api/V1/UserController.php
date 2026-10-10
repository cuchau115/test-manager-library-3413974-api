<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\CreateLibrarianService;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly CreateLibrarianService $createLibrarianService,
    ) {}

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->createLibrarianService->execute(
            $request->validated('username'),
            $request->validated('password'),
        );

        return response()->json(new UserResource($user), Response::HTTP_CREATED);
    }
}