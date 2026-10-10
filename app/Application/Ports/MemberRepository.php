<?php

namespace App\Application\Ports;

use App\Domain\Model\Member;

interface MemberRepository
{
    public function nextIdentity(): string;

    public function save(Member $member): void;

    public function findById(string $id): ?Member;

    public function findByDocument(string $documentNumber): ?Member;

    /**
     * @return array{items: list<Member>, total: int}
     */
    public function search(?string $text, int $page, int $size): array;
}
