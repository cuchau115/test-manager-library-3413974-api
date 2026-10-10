<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Ports\CategoryReader;
use App\Infrastructure\Persistence\Models\CategoryModel;

final class EloquentCategoryReader implements CategoryReader
{
    /**
     * @return list<array{id: string, name: string}>
     */
    public function all(): array
    {
        return CategoryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->all();
    }

    public function exists(string $id): bool
    {
        return CategoryModel::where('id', $id)->exists();
    }

    public function name(string $id): ?string
    {
        $model = CategoryModel::find($id);
        return $model ? $model->name : null;
    }
}