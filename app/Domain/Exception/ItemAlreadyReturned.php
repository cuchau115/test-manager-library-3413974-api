<?php

namespace App\Domain\Exception;

class ItemAlreadyReturned extends DomainError
{
    public function errorCode(): string
    {
        return 'ITEM_ALREADY_RETURNED';
    }

    public static function forItem(string $itemId): self
    {
        return new self(
            'La línea del préstamo ya fue devuelta.',
            [['field' => 'item_id', 'issue' => 'ya devuelta']],
        );
    }
}
