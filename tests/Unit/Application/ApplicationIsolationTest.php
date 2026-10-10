<?php

namespace Tests\Unit\Application;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Artículo II: la capa de aplicación no conoce la infraestructura.
 */
final class ApplicationIsolationTest extends TestCase
{
    public function test_aplicacion_no_importa_infrastructure(): void
    {
        $dir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Application';

        $this->assertDirectoryExists($dir);

        $offenders = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (str_contains($contents, 'App\\Infrastructure') || str_contains($contents, 'Illuminate\\')) {
                $offenders[] = $file->getPathname();
            }
        }

        $this->assertSame([], $offenders, 'La aplicación no puede depender de infraestructura: ' . implode(', ', $offenders));
    }
}
