<?php

namespace Tests\Support\Fakes;

use App\Application\Ports\MemberRepository;
use App\Domain\Model\Member;

final class FakeMemberRepository implements MemberRepository
{
    /** @var array<string, Member> */
    public array $items = [];

    private int $sequence = 0;

    public function nextIdentity(): string
    {
        return 'member-' . (++$this->sequence);
    }

    public function save(Member $member): void
    {
        $this->items[$member->id()] = $member;
    }

    public function findById(string $id): ?Member
    {
        return $this->items[$id] ?? null;
    }

    public function findByDocument(string $documentNumber): ?Member
    {
        foreach ($this->items as $member) {
            if ($member->documentNumber() === $documentNumber) {
                return $member;
            }
        }

        return null;
    }

    public function search(?string $text, int $page, int $size): array
    {
        $list = array_values($this->items);

        if ($text !== null) {
            $needle = mb_strtolower($text);
            $list = array_values(array_filter($list, fn (Member $m): bool => str_contains(mb_strtolower($m->fullName()), $needle)
                || str_contains(mb_strtolower($m->documentNumber()), $needle)));
        }

        usort($list, fn (Member $a, Member $b): int => strcmp($a->fullName(), $b->fullName()));

        $total = count($list);

        return ['items' => array_slice($list, ($page - 1) * $size, $size), 'total' => $total];
    }
}
