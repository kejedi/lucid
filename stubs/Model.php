<?php

namespace DummyNamespace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kejedi\Lucid\Table;

class DummyClass extends Model
{
    use HasFactory;

    public function schema(Table $table)
    {
        $table->id();
        $table->string('name');
        $table->timestamp('created_at');
        $table->timestamp('updated_at');
    }
}
