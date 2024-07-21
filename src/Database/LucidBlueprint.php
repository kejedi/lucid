<?php

namespace Kejedi\Lucid\Database;

use Illuminate\Database\Schema\Blueprint;

class LucidBlueprint extends Blueprint
{
    public function addColumn($type, $name, array $parameters = [])
    {
        $columnDefinition = parent::addColumn($type, $name, $parameters);

        if (!in_array('autoIncrement', $parameters)) {
            $columnDefinition->nullable();
        }

        return $columnDefinition;
    }
}
