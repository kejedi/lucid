<?php

namespace DummyNamespace;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Kejedi\Lucid\Database\LucidBlueprint;

class DummyClass extends Pivot
{
    public function lucidSchema(LucidBlueprint $table): void
    {
        $table->id();
        $table->timestamps();
    }
}
