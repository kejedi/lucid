<?php

namespace Kejedi\Lucid\Database;

use Illuminate\Database\Schema\Blueprint;

class LucidBlueprint extends Blueprint
{
    public function addColumn($type, $name, array $parameters = [])
    {
        return parent::addColumn($type, $name, $parameters)
            ->nullable($name != 'id');
    }
}
