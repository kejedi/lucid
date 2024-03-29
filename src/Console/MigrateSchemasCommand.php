<?php

namespace Kejedi\Lucid\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Str;
use Kejedi\Lucid\Table;
use Symfony\Component\Finder\Finder;

class MigrateSchemasCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'migrate:schemas {--f|fresh} {--s|seed} {--force}';

    protected $description = 'Migrate & sync model schema methods with the database';

    public function handle(): int
    {
        if (!$this->confirmToProceed()) {
            return 1;
        }

        $this->migrate();

        $this->syncSchemas();

        if (!app()->isProduction()) {
            $this->generateIdeHelpers();
        }

        if ($this->option('seed')) {
            $this->seed();
        }

        $this->newLine();

        return 0;
    }

    protected function migrate(): void
    {
        $command = $this->option('fresh')
            ? 'migrate:fresh'
            : 'migrate';

        $this->call($command, [
            '--force' => true,
        ]);
    }

    protected function syncSchemas(): void
    {
        $this->components->info('Syncing schemas.');

        $path = app_path('Models');

        $namespace = app()->getNamespace();

        foreach ((new Finder)->in($path)->files() as $file) {
            $model = $namespace . str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($file->getRealPath(), realpath(app_path()) . DIRECTORY_SEPARATOR)
                );

            if (
                method_exists($model, 'schema') ||
                method_exists($model, 'extraSchema')
            ) {
                $this->syncSchema(app($model));
            }
        }
    }

    protected function syncSchema(Model|Pivot $model): void
    {
        $builder = $model->getConnection()->getSchemaBuilder();

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

        $builder->create($temporaryTable, function (Blueprint $table) use ($model) {
            $lucidTable = new Table($table);

            if (method_exists($model, 'schema')) {
                $model->schema($lucidTable);
            }

            if (method_exists($model, 'extraSchema')) {
                $model->extraSchema($lucidTable);
            }
        });

        return $temporaryTable;
    }

    protected function createTable(Model|Pivot $model, Builder $builder, $temporaryTable): void
    {
        $this->components->task(
            "Creating {$model->getTable()} table",
            function () use ($builder, $temporaryTable, $model) {
                $builder->rename($temporaryTable, $model->getTable());
            }
        );
    }

    protected function updateTable(Model|Pivot $model, Builder $builder, $temporaryTable): void
    {
        try {
            $manager = $model->getConnection()->getDoctrineSchemaManager();

            $tableDifference = $manager->createComparator()->compareTables(
                $manager->introspectTable($model->getTable()),
                $manager->introspectTable($temporaryTable),
            );

            if (!$tableDifference->isEmpty()) {
                $this->components->task(
                    "Updating {$model->getTable()} table",
                    function () use ($manager, $tableDifference) {
                        $manager->alterTable($tableDifference);
                    }
                );
            }
        } catch (Exception $exception) {
            $this->components->error($exception->getMessage());
        }

        $builder->drop($temporaryTable);
    }

    protected function generateIdeHelpers(): void
    {
        $this->components->task("Generating IDE helper files", function () {
            $this->callSilently('ide-helper:generate');

            $this->callSilently('ide-helper:model', [
                '--nowrite' => true,
            ]);
        });
    }

    protected function seed(): void
    {
        $this->call('db:seed', [
            '--force' => true,
        ]);
    }
}
