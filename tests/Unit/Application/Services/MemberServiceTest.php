<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\CreateMemberService;
use App\Application\Services\GetMemberService;
use App\Application\Services\ListMembersService;
use App\Domain\Exception\DocumentTaken;
use App\Domain\Exception\ResourceNotFound;
use App\Domain\Exception\ValidationError;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fakes\FakeLoanRepository;
use Tests\Support\Fakes\FakeMemberRepository;

final class MemberServiceTest extends TestCase
{
    private FakeMemberRepository $members;

    protected function setUp(): void
    {
        $this->members = new FakeMemberRepository();
    }

    public function test_crea_socio(): void
    {
        $member = (new CreateMemberService($this->members))->create('Ana Gómez', 'CC-1');

        $this->assertSame('Ana Gómez', $member->fullName());
        $this->assertNotNull($this->members->findByDocument('CC-1'));
    }

    public function test_documento_repetido(): void
    {
        (new CreateMemberService($this->members))->create('Ana', 'CC-1');

        $this->expectException(DocumentTaken::class);
        (new CreateMemberService($this->members))->create('Otra', 'CC-1');
    }

    public function test_documento_obligatorio(): void
    {
        $this->expectException(ValidationError::class);
        (new CreateMemberService($this->members))->create('Ana', '  ');
    }

    public function test_lista_y_busca(): void
    {
        $create = new CreateMemberService($this->members);
        $create->create('Ana Gómez', 'CC-1');
        $create->create('Beto Ruiz', 'CC-2');

        $result = (new ListMembersService($this->members))->list('beto', 1, 20);

        $this->assertSame(1, $result['total']);
        $this->assertSame('Beto Ruiz', $result['items'][0]->fullName());
    }

    public function test_detalle_con_prestamos_activos(): void
    {
        $member = (new CreateMemberService($this->members))->create('Ana', 'CC-1');

        $service = new GetMemberService($this->members, new FakeLoanRepository());
        $detail = $service->get($member->id());

        $this->assertSame($member->id(), $detail['member']->id());
        $this->assertSame(0, $detail['active_loans']);
    }

    public function test_detalle_socio_inexistente(): void
    {
        $this->expectException(ResourceNotFound::class);
        (new GetMemberService($this->members, new FakeLoanRepository()))->get('nope');
    }
}
