<?php

namespace Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\InvalidQuantity;
use App\Domain\ValueObject\Quantity;
use PHPUnit\Framework\TestCase;

final class QuantityTest extends TestCase
{
    public function test_acepta_positivos(): void
    {
        $this->assertSame(1, Quantity::of(1)->value());
        $this->assertSame(7, Quantity::of(7)->value());
    }

    public function test_rechaza_cero(): void
    {
        $this->expectException(InvalidQuantity::class);
        Quantity::of(0);
    }

    public function test_rechaza_negativos(): void
    {
        $this->expectException(InvalidQuantity::class);
        Quantity::of(-3);
    }
}
