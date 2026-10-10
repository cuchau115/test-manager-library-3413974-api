<?php

namespace App\Application\Services;

use App\Application\Ports\LoanRepository;
use App\Application\Ports\MemberRepository;
use App\Domain\Exception\ResourceNotFound;

/**
 * HU-05: detalle de un socio con el conteo de préstamos sin devolver.
 */
final class GetMemberService
{
    public function __construct(
        private readonly MemberRepository $members,
        private readonly LoanRepository $loans,
    ) {
    }

    /**
     * @return array{member: \App\Domain\Model\Member, active_loans: int}
     */
    public function get(string $id): array
    {
        $member = $this->members->findById($id) ?? throw ResourceNotFound::forResource('member');

        return [
            'member' => $member,
            'active_loans' => $this->loans->countOpenItemsByMember($id),
        ];
    }
}
