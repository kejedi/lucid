<?php

namespace DummyNamespace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kejedi\Lucid\Database\LucidBlueprint;

class DummyClass extends Model
{
    use HasFactory;

    public function lucidSchema(LucidBlueprint $table): void
    {
        $table->id();
        $table->timestamps();
    }

    public function lucidDefinition(): array
    {
        return [
            //
        ];
    }
}
