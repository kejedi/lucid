<?php

namespace DummyNamespace;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Kejedi\Lucid\Database\LucidBlueprint;

class DummyClass extends Pivot
{
    use HasUuids;

    public function lucidSchema(LucidBlueprint $table): void
    {
        $table->uuid('id')->primary();
        $table->timestamps();
    }
}
