<?php

namespace Kejedi\Lucid\Database;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

class LucidBlueprint extends Blueprint
{
    public function addColumn($type, $name, array $parameters = []): ColumnDefinition
    {
        $columnDefinition = parent::addColumn($type, $name, $parameters);

        if ($name != 'id') {
            $columnDefinition->nullable();
        }

        return $columnDefinition;
    }
}
