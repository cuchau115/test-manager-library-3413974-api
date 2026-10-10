<?php

namespace App\Application\Ports;

/**
 * Frontera transaccional. Garantiza que descontar ejemplares y crear la línea
 * del préstamo son una sola operación.
 */
interface TransactionManager
{
    /**
     * @template T
     * @param callable(): T $work
     * @return T
     */
    public function run(callable $work): mixed;
}
