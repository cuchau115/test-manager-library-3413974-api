<?php

namespace App\Infrastructure\Storage;

use App\Application\Ports\CoverStorage;
use InvalidArgumentException;

final class DiskCoverStorage implements CoverStorage
{
    private const MAX_SIZE = 2 * 1024 * 1024; // 2 MB
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png'];

    public function __construct(
        private readonly string $coversDir,
    ) {
        if (!is_dir($this->coversDir)) {
            mkdir($this->coversDir, 0755, true);
        }
    }

    public function store(string $contents, string $mimeType): string
    {
        $this->validate($contents, $mimeType);

        $key = 'cover-' . bin2hex(random_bytes(16));
        $path = $this->coversDir . '/' . $key;

        if (file_put_contents($path, $contents) === false) {
            throw new \RuntimeException('No se pudo guardar la portada.');
        }

        return $key;
    }

    public function get(string $key): ?string
    {
        $path = $this->coversDir . '/' . $key;

        if (!is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        return $contents === false ? null : $contents;
    }

    public function delete(string $key): void
    {
        $path = $this->coversDir . '/' . $key;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function validate(string $contents, string $mimeType): void
    {
        if (strlen($contents) > self::MAX_SIZE) {
            throw new InvalidArgumentException('La imagen supera el tamaño máximo de 2 MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->buffer($contents);

        if (!in_array($detectedMime, self::ALLOWED_MIME_TYPES, true)) {
            throw new InvalidArgumentException('Formato de imagen no permitido. Solo JPG y PNG.');
        }

        if ($detectedMime !== $mimeType) {
            throw new InvalidArgumentException('El tipo MIME no coincide con el contenido.');
        }

        // Additional validation: check image dimensions are reasonable
        $imageInfo = @getimagesizefromstring($contents);
        if ($imageInfo === false) {
            throw new InvalidArgumentException('El archivo no es una imagen válida.');
        }

        if ($imageInfo[0] > 5000 || $imageInfo[1] > 5000) {
            throw new InvalidArgumentException('Las dimensiones de la imagen son demasiado grandes.');
        }
    }
}