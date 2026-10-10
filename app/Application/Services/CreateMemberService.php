<?php

namespace App\Application\Services;

use App\Application\Ports\MemberRepository;
use App\Domain\Exception\DocumentTaken;
use App\Domain\Exception\ValidationError;
use App\Domain\Model\Member;

/**
 * HU-05: crear un socio.
 */
final class CreateMemberService
{
    public function __construct(private readonly MemberRepository $members)
    {
    }

    public function create(string $fullName, string $documentNumber): Member
    {
        $fullName = trim($fullName);
        $documentNumber = trim($documentNumber);

        if ($fullName === '') {
            throw new ValidationError('El nombre del socio es obligatorio.', [['field' => 'full_name', 'issue' => 'obligatorio']]);
        }

        if ($documentNumber === '') {
            throw new ValidationError('El documento del socio es obligatorio.', [['field' => 'document_number', 'issue' => 'obligatorio']]);
        }

        if ($this->members->findByDocument($documentNumber) !== null) {
            throw DocumentTaken::forValue($documentNumber);
        }

        $member = Member::create($this->members->nextIdentity(), $fullName, $documentNumber);
        $this->members->save($member);

        return $member;
    }
}
