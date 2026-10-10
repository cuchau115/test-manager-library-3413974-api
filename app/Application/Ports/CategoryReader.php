<?php

namespace App\Application\Ports;

/**
 * Lectura del catálogo fijo de categorías (semilla de solo lectura).
 */
interface CategoryReader
{
    /**
     * @return list<array{id: string, name: string}>
     */
    public function all(): array;

    public function exists(string $id): bool;

    public function name(string $id): ?string;
}
