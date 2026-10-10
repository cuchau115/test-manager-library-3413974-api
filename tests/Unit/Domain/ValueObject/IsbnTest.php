<?php

namespace Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\InvalidIsbn;
use App\Domain\ValueObject\Isbn;
use PHPUnit\Framework\TestCase;

final class IsbnTest extends TestCase
{
    public function test_acepta_trece_digitos(): void
    {
        $isbn = Isbn::fromString('9780307474728');

        $this->assertSame('9780307474728', $isbn->value());
        $this->assertSame('9780307474728', (string) $isbn);
    }

    public function test_recorta_espacios(): void
    {
        $this->assertSame('9780307474728', Isbn::fromString('  9780307474728 ')->value());
    }

    public function test_rechaza_longitud_incorrecta(): void
    {
        $this->expectException(InvalidIsbn::class);
        Isbn::fromString('97803074747');
    }

    public function test_rechaza_no_digitos(): void
    {
        $this->expectException(InvalidIsbn::class);
        Isbn::fromString('978030747472X');
    }

    public function test_igualdad_por_valor(): void
    {
        $this->assertTrue(Isbn::fromString('9780307474728')->equals(Isbn::fromString('9780307474728')));
    }
}
