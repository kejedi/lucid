<?php

namespace DummyNamespace;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kejedi\Lucid\Database\LucidBlueprint;

class DummyClass extends Model
{
    use HasFactory, HasUuids;

    public function lucidSchema(LucidBlueprint $table): void
    {
        $table->uuid('id')->primary();
        $table->timestamps();
    }

    public function lucidDefinition(): array
    {
        return [
            //
        ];
    }
}
