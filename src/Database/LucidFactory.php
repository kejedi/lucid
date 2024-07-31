<?php

namespace Kejedi\Lucid\Database;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LucidFactory extends Factory
{
    public function definition(): array
    {
        $model = $this->newModel();

        return collect(get_class_methods($model))
            ->filter(function ($method) {
                return Str::containsAll($method, ['lucid', 'definition'], true);
            })
            ->map(function ($method) use ($model) {
                return $model->$method();
            })
            ->collapse()
            ->toArray();
    }
}
