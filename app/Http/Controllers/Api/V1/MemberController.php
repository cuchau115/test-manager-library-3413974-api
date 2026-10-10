<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\CreateMemberService;
use App\Application\Services\GetMemberService;
use App\Application\Services\ListMembersService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\CreateMemberRequest;
use App\Http\Requests\Member\MemberSearchRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends Controller
{
    public function __construct(
        private readonly CreateMemberService $createMemberService,
        private readonly ListMembersService $listMembersService,
        private readonly GetMemberService $getMemberService,
    ) {}

    public function index(MemberSearchRequest $request): JsonResponse
    {
        $result = $this->listMembersService->list(
            $request->validated('q'),
            (int) $request->validated('page', 1),
            (int) $request->validated('size', 20),
        );

        $items = array_map(fn ($member) => $this->memberData($member), $result['items']);

        return response()->json([
            'items' => $items,
            'page' => (int) $request->validated('page', 1),
            'size' => (int) $request->validated('size', 20),
            'total' => $result['total'],
        ]);
    }

    public function store(CreateMemberRequest $request): JsonResponse
    {
        $member = $this->createMemberService->create(
            $request->validated('full_name'),
            $request->validated('document_number'),
        );

        return response()->json($this->memberData($member), Response::HTTP_CREATED);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->getMemberService->get($id);
        return response()->json($this->memberData($result['member']) + [
            'active_loans' => $result['active_loans'],
        ]);
    }

    private function memberData(\App\Domain\Model\Member $member): array
    {
        return [
            'id' => $member->id(),
            'full_name' => $member->fullName(),
            'document_number' => $member->documentNumber(),
        ];
    }
}