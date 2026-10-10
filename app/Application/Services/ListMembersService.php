<?php

namespace App\Application\Services;

use App\Application\Ports\MemberRepository;

/**
 * HU-05: listar y buscar socios.
 */
final class ListMembersService
{
    public const MAX_SIZE = 50;

    public function __construct(private readonly MemberRepository $members)
    {
    }

    /**
     * @return array{items: list<\App\Domain\Model\Member>, total: int}
     */
    public function list(?string $text, int $page, int $size): array
    {
        $page = max(1, $page);
        $size = min(max(1, $size), self::MAX_SIZE);
        $text = ($text === null || trim($text) === '') ? null : trim($text);

        return $this->members->search($text, $page, $size);
    }
}
