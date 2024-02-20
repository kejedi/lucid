<?php

namespace Kejedi\Lucid;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Kejedi\Lucid\Console\MakeSchemaCommand;
use Kejedi\Lucid\Console\MigrateSchemasCommand;

class LucidServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeSchemaCommand::class,
                MigrateSchemasCommand::class,
            ]);
        }

        Model::unguard();
    }
}
