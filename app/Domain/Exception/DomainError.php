<?php

namespace App\Domain\Exception;

/**
 * Error de dominio. El dominio nunca conoce HTTP; solo expone un código de
 * negocio y un detalle neutro. El adaptador HTTP lo traduce a estado+cuerpo.
 */
abstract class DomainError extends \RuntimeException
{
    /** @param array<int, array<string, mixed>> $details */
    public function __construct(string $message, protected array $details = [])
    {
        parent::__construct($message);
    }

    abstract public function errorCode(): string;

    /** @return array<int, array<string, mixed>> */
    public function details(): array
    {
        return $this->details;
    }
}
