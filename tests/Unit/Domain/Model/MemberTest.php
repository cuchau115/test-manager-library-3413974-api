<?php

namespace Tests\Unit\Domain\Model;

use App\Domain\Exception\ValidationError;
use App\Domain\Model\Member;
use PHPUnit\Framework\TestCase;

final class MemberTest extends TestCase
{
    public function test_crea_socio_con_documento(): void
    {
        $member = Member::create('M1', 'Ana Gómez', 'CC-123');

        $this->assertSame('Ana Gómez', $member->fullName());
        $this->assertSame('CC-123', $member->documentNumber());
    }

    public function test_nombre_es_obligatorio(): void
    {
        $this->expectException(ValidationError::class);
        Member::create('M1', '   ', 'CC-123');
    }

    public function test_documento_es_obligatorio(): void
    {
        $this->expectException(ValidationError::class);
        Member::create('M1', 'Ana Gómez', '');
    }
}
