<?php

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidQuantity;

/**
 * Cantidad entera estrictamente mayor que cero. Inmutable.
 */
final class Quantity
{
    private function __construct(private readonly int $value)
    {
    }

    public static function of(int $value): self
    {
        if ($value < 1) {
            throw InvalidQuantity::notPositive($value);
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
