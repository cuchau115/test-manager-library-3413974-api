<?php

namespace App\Domain\Exception;

class DocumentTaken extends DomainError
{
    public function errorCode(): string
    {
        return 'DOCUMENT_TAKEN';
    }

    public static function forValue(string $documentNumber): self
    {
        return new self(
            sprintf('El documento «%s» ya pertenece a un socio.', $documentNumber),
            [['field' => 'document_number', 'issue' => 'ya existe']],
        );
    }
}
