<?php

namespace Kejedi\Lucid\Console;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Str;
use Kejedi\Lucid\Database\LucidBlueprint;
use Symfony\Component\Finder\Finder;

class LucidMigrateCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'lucid:migrate {--f|fresh} {--s|seed} {--force}';

    protected $description = 'Migrate & sync Lucid model schemas with the database';

    public function handle(): void
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->migrate();

        $this->sync();

        if ($this->option('seed')) {
            $this->seed();
        }
    }

    protected function migrate(): void
    {
        $command = $this->option('fresh') ? 'migrate:fresh' : 'migrate';

        $this->call($command, [
            '--force' => $this->option('force'),
        ]);
    }

    protected function sync(): void
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

    protected function syncSchema(Model|Pivot $model): void
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

    protected function createTemporaryTable(Model|Pivot $model, Builder $builder): string
    {
        $temporaryTable = "{$model->getTable()}_table";

        $builder->dropIfExists($temporaryTable);

        $builder->create($temporaryTable, function (LucidBlueprint $table) use ($model) {
            collect(get_class_methods($model))
                ->filter(function ($method) {
                    return Str::containsAll($method, ['lucid', 'schema'], true);
                })
                ->each(function ($method) use ($model, $table) {
                    $model->$method($table);
                });
        });

        return $temporaryTable;
    }

    protected function createTable(Model|Pivot $model, Builder $builder, $temporaryTable): void
    {
        $this->components->task(
            "Creating {$model->getTable()} table",
            function () use ($model, $builder, $temporaryTable) {
                $builder->rename($temporaryTable, $model->getTable());
            }
        );
    }

    protected function updateTable(Model|Pivot $model, Builder $builder, $temporaryTable): void
    {
        $connection = $model->getConnection();

        try {
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
                $this->components->task(
                    "Updating {$model->getTable()} table",
                    function () use ($schemaManager, $tableDiff) {
                        $schemaManager->alterTable($tableDiff);
                    }
                );
            }
        } catch (Exception $exception) {
            $this->components->error($exception->getMessage());
        }

        $builder->drop($temporaryTable);
    }

    protected function seed(): void
    {
        $this->call('db:seed', [
            '--force' => $this->option('force'),
        ]);
    }
}
