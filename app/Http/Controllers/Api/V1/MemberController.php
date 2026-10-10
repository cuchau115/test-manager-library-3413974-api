<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\MemberService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\CreateMemberRequest;
use App\Http\Requests\Member\MemberSearchRequest;
use App\Http\Resources\MemberResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends Controller
{
    public function __construct(
        private readonly MemberService $memberService,
    ) {}

    public function index(MemberSearchRequest $request): JsonResponse
    {
        $result = $this->memberService->list(
            $request->validated('q'),
            (int) $request->validated('page', 1),
            (int) $request->validated('size', 20),
        );

        $items = array_map(fn ($m) => new MemberResource($m), $result['items']);

        return response()->json([
            'items' => $items,
            'page' => $result['page'],
            'size' => $result['size'],
            'total' => $result['total'],
        ]);
    }

    public function store(CreateMemberRequest $request): JsonResponse
    {
        $member = $this->memberService->create(
            $request->validated('full_name'),
            $request->validated('document_number'),
        );

        return response()->json(new MemberResource($member), Response::HTTP_CREATED);
    }

    public function show(string $id): JsonResponse
    {
        $member = $this->memberService->get($id);
        return response()->json(new MemberResource($member));
    }
}