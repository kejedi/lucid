<?php

namespace Kejedi\Lucid;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Kejedi\Lucid\Console\LucidFactoryCommand;
use Kejedi\Lucid\Console\LucidMigrateCommand;
use Kejedi\Lucid\Console\LucidModelCommand;

class LucidServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                LucidFactoryCommand::class,
                LucidMigrateCommand::class,
                LucidModelCommand::class,
            ]);
        }

        Model::unguard();
    }
}
