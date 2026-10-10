<?php

namespace App\Application\Ports;

/**
 * Almacenamiento de portadas bajo clave opaca. Nunca guarda rutas ni bytes en el modelo.
 */
interface CoverStorage
{
    /**
     * Guarda el binario y devuelve una clave opaca.
     */
    public function store(string $contents, string $mimeType): string;

    /**
     * Devuelve el binario de la clave o null si no existe.
     */
    public function get(string $key): ?string;

    public function delete(string $key): void;
}
