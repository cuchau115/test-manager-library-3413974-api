<?php

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Artículo I: app/Domain es PHP puro, sin dependencias de framework.
 */
final class DomainIsolationTest extends TestCase
{
    public function test_dominio_no_importa_illuminate(): void
    {
        $domainDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Domain';

        $this->assertDirectoryExists($domainDir);

        $offenders = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($domainDir, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (str_contains($contents, 'Illuminate\\')) {
                $offenders[] = $file->getPathname();
            }
        }

        $this->assertSame([], $offenders, 'El dominio no puede importar Illuminate: ' . implode(', ', $offenders));
    }
}
