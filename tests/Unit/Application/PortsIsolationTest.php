<?php

namespace Tests\Unit\Application;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class PortsIsolationTest extends TestCase
{
    private const PORTS = [
        \App\Application\Ports\BookRepository::class,
        \App\Application\Ports\LoanRepository::class,
        \App\Application\Ports\MemberRepository::class,
        \App\Application\Ports\UserRepository::class,
        \App\Application\Ports\ReportReader::class,
        \App\Application\Ports\Clock::class,
        \App\Application\Ports\PasswordHasher::class,
        \App\Application\Ports\TokenIssuer::class,
        \App\Application\Ports\CoverStorage::class,
    ];

    public function test_existen_los_nueve_puertos_como_interfaces(): void
    {
        $this->assertCount(9, self::PORTS);

        foreach (self::PORTS as $port) {
            $this->assertTrue(interface_exists($port), "Falta el puerto {$port}");
        }
    }

    public function test_los_puertos_no_importan_illuminate(): void
    {
        $dir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR
            . 'Application' . DIRECTORY_SEPARATOR . 'Ports';

        $offenders = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php'
                && str_contains(file_get_contents($file->getPathname()), 'Illuminate\\')) {
                $offenders[] = $file->getPathname();
            }
        }

        $this->assertSame([], $offenders, 'Los puertos no pueden importar Illuminate: ' . implode(', ', $offenders));
    }
}
