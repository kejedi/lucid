<?php

namespace Kejedi\Lucid\Console;

use Doctrine\DBAL\DriverManager;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Kejedi\Lucid\Database\LucidBlueprint;
use Symfony\Component\Finder\Finder;

class LucidMigrateCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'lucid:migrate {--f|fresh} {--s|seed} {--force}';

    protected $description = 'Migrate & sync Lucid model schemas with the database';

    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return 1;
        }

        $this->migrate();

        $this->sync();

        if ($this->option('seed')) {
            $this->seed();
        }

        if (!App::isProduction()) {
            $this->generateIdeHelperFiles();
        }

        return 0;
    }

    protected function migrate()
    {
        $command = $this->option('fresh') ? 'migrate:fresh' : 'migrate';

        $this->call($command, [
            '--force' => $this->option('force'),
        ]);
    }

    protected function sync()
    {
        $this->components->info('Syncing model schemas.');

        $modelPath = app_path('Models');

        collect(Finder::create()->in($modelPath)->files()->name('*.php'))
            ->map(function ($model) {
                $namespace = $this->laravel->getNamespace();

                return $namespace . str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($model->getRealPath(), realpath(app_path()) . DIRECTORY_SEPARATOR)
                );
            })
            ->filter(function ($model) {
                return is_subclass_of($model, Model::class) || is_subclass_of($model, Pivot::class);
            })
            ->each(function ($model) {
                $this->syncSchema(new $model);
            });
    }

    protected function syncSchema(Model|Pivot $model)
    {
        $builder = $model->getConnection()->getSchemaBuilder();

        $builder->blueprintResolver(function ($table, $callback, $prefix) {
            return new LucidBlueprint($table, $callback, $prefix);
        });

        $temporaryTable = $this->createTemporaryTable($model, $builder);

        if (!$builder->hasTable($model->getTable())) {
            $this->createTable($model, $builder, $temporaryTable);
        } else {
            $this->updateTable($model, $builder, $temporaryTable);
        }
    }

    protected function createTemporaryTable(Model|Pivot $model, Builder $builder)
    {
        $temporaryTable = "{$model->getTable()}_table";

        $builder->dropIfExists($temporaryTable);

        $builder->create($temporaryTable, function (LucidBlueprint $table) use ($model) {
            collect(get_class_methods($model))
                ->filter(function ($method) {
                    return Str::containsAll($method, ['lucid', 'schema'], true);
                })
                ->map(function ($method) use ($model, $table) {
                    $model->$method($table);
                });
        });

        return $temporaryTable;
    }

    protected function createTable(Model|Pivot $model, Builder $builder, $temporaryTable)
    {
        $this->components->task("Creating {$model->getTable()} table", function () use ($model, $builder, $temporaryTable) {
            $builder->rename($temporaryTable, $model->getTable());
        });
    }

    protected function updateTable(Model|Pivot $model, Builder $builder, $temporaryTable)
    {
        $connection = $model->getConnection();

        $schemaManager = DriverManager::getConnection([
            'driver' => "pdo_{$connection->getConfig('driver')}",
            'host' => $connection->getConfig('host'),
            'port' => $connection->getConfig('port'),
            'dbname' => $connection->getConfig('database'),
            'user' => $connection->getConfig('username'),
            'password' => $connection->getConfig('password'),
        ])->createSchemaManager();

        $tableDiff = $schemaManager->createComparator()->compareTables(
            $schemaManager->introspectTable($model->getTable()),
            $schemaManager->introspectTable($temporaryTable),
        );

        if (!$tableDiff->isEmpty()) {
            $this->components->task("Updating {$model->getTable()} table", function () use ($schemaManager, $tableDiff) {
                $schemaManager->alterTable($tableDiff);
            });
        }

        $builder->drop($temporaryTable);
    }

    protected function seed()
    {
        $this->call('db:seed', [
            '--force' => $this->option('force'),
        ]);
    }

    protected function generateIdeHelperFiles()
    {
        $this->components->info('Generating IDE helper files.');

        $this->callSilently('ide-helper:generate');

        $this->callSilently('ide-helper:models', [
            '--nowrite' => true,
        ]);
    }
}
