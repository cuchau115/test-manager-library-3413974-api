<?php

namespace App\Domain\Enum;

/**
 * Rol de un usuario interno. Valores persistidos: admin, librarian.
 */
enum Role: string
{
    case Admin = 'admin';
    case Librarian = 'librarian';

    public static function fromString(string $value): self
    {
        return self::from(strtolower(trim($value)));
    }
}
