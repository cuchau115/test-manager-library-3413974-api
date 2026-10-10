<?php

namespace Tests\Support\Fakes;

use App\Application\Ports\CategoryReader;

final class FakeCategoryReader implements CategoryReader
{
    public const CATEGORIES = [
        '11111111-1111-4111-8111-111111111111' => 'Novela',
        '22222222-2222-4222-8222-222222222222' => 'Ciencia',
        '33333333-3333-4333-8333-333333333333' => 'Tecnología',
        '44444444-4444-4444-8444-444444444444' => 'Historia',
        '55555555-5555-4555-8555-555555555555' => 'Infantil',
    ];

    public function all(): array
    {
        $out = [];

        foreach (self::CATEGORIES as $id => $name) {
            $out[] = ['id' => $id, 'name' => $name];
        }

        return $out;
    }

    public function exists(string $id): bool
    {
        return isset(self::CATEGORIES[$id]);
    }

    public function name(string $id): ?string
    {
        return self::CATEGORIES[$id] ?? null;
    }
}
