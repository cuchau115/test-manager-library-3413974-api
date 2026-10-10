<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ArchitectureTest extends TestCase
{
    public function test_domain_and_application_dependencies_point_inward(): void
    {
        $root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'app';

        foreach ($this->phpFiles($root . DIRECTORY_SEPARATOR . 'Domain') as $file) {
            $contents = file_get_contents($file);
            self::assertDoesNotMatchRegularExpression('/\buse\s+Illuminate\\\\/', $contents, $file);
            self::assertDoesNotMatchRegularExpression('/\buse\s+App\\\\(Application|Infrastructure|Http)\\\\/', $contents, $file);
        }

        foreach ($this->phpFiles($root . DIRECTORY_SEPARATOR . 'Application') as $file) {
            $contents = file_get_contents($file);
            self::assertDoesNotMatchRegularExpression('/\buse\s+Illuminate\\\\/', $contents, $file);
            self::assertDoesNotMatchRegularExpression('/\buse\s+App\\\\Infrastructure\\\\/', $contents, $file);
        }
    }

    public function test_composition_provider_is_the_only_application_binding_to_infrastructure(): void
    {
        $root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'app';
        $provider = $root . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR . 'CompositionServiceProvider.php';

        foreach ($this->phpFiles($root) as $file) {
            if ($file === $provider) {
                continue;
            }

            $contents = file_get_contents($file);
            self::assertDoesNotMatchRegularExpression('/\bnew\s+\\\\?App\\\\Infrastructure\\\\/', $contents, $file);
            if (str_contains($file, DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR)) {
                self::assertDoesNotMatchRegularExpression('/\buse\s+App\\\\Infrastructure\\\\/', $contents, $file);
            }
        }

        self::assertFileExists($provider);
    }

    /**
     * @return list<string>
     */
    private function phpFiles(string $directory): array
    {
        $files = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
