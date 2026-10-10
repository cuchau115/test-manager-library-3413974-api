<?php

namespace App\Domain\Model;

use App\Domain\Exception\ValidationError;

/**
 * Agregado Socio. No inicia sesión.
 */
final class Member
{
    private function __construct(
        private readonly string $id,
        private string $fullName,
        private string $documentNumber,
    ) {
    }

    public static function create(string $id, string $fullName, string $documentNumber): self
    {
        $fullName = trim($fullName);
        $documentNumber = trim($documentNumber);

        if ($fullName === '') {
            throw new ValidationError('El nombre del socio es obligatorio.', [['field' => 'full_name', 'issue' => 'obligatorio']]);
        }

        if ($documentNumber === '') {
            throw new ValidationError('El documento del socio es obligatorio.', [['field' => 'document_number', 'issue' => 'obligatorio']]);
        }

        return new self($id, $fullName, $documentNumber);
    }

    public static function reconstitute(string $id, string $fullName, string $documentNumber): self
    {
        return new self($id, $fullName, $documentNumber);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function fullName(): string
    {
        return $this->fullName;
    }

    public function documentNumber(): string
    {
        return $this->documentNumber;
    }
}
