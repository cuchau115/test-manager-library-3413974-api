<?php

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidIsbn;

/**
 * ISBN de 13 dígitos (sin guiones). Inmutable.
 */
final class Isbn
{
    private function __construct(private readonly string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = trim($value);

        if (preg_match('/^\d{13}$/', $normalized) !== 1) {
            throw InvalidIsbn::forValue($value);
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
