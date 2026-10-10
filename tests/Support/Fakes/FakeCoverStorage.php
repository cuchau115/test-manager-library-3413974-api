<?php

namespace Tests\Support\Fakes;

use App\Application\Ports\CoverStorage;

final class FakeCoverStorage implements CoverStorage
{
    /** @var array<string, string> */
    public array $files = [];

    /** @var list<string> */
    public array $deleted = [];

    private int $sequence = 0;

    public function store(string $contents, string $mimeType): string
    {
        $key = 'key-' . (++$this->sequence);
        $this->files[$key] = $contents;

        return $key;
    }

    public function get(string $key): ?string
    {
        return $this->files[$key] ?? null;
    }

    public function delete(string $key): void
    {
        $this->deleted[] = $key;
        unset($this->files[$key]);
    }
}
