<?php

namespace App\Application\Ports;

use DateTimeImmutable;

/**
 * Reloj del sistema. Inyectable para poder fijar el instante en las pruebas.
 */
interface Clock
{
    public function now(): DateTimeImmutable;
}
