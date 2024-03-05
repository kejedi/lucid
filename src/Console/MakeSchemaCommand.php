<?php

namespace Kejedi\Lucid\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeSchemaCommand extends GeneratorCommand
{
    protected $name = 'make:schema';

    protected $description = 'Create a new model class with a schema method';

    protected $type = 'Model';

    public function handle(): bool|int
    {
        if (parent::handle() === false && !$this->option('force')) {
            return false;
        }

        if ($this->argument('name') == 'User') {
            $this->backupUserMigration();
        }

        if (!$this->option('pivot')) {
            $this->createFactory();
        }

        return 0;
    }

    protected function backupUserMigration(): void
    {
        $file = database_path('migrations/2014_10_12_000000_create_users_table.php');

        if (file_exists($file)) {
            rename($file, "$file.bak");
        }
    }

    protected function createFactory(): void
    {
        $this->call('make:factory', [
            'name' => "{$this->argument('name')}Factory",
        ]);
    }

    protected function getStub(): string
    {
        if ($this->argument('name') == 'User') {
            return __DIR__ . '/../../stubs/User.php';
        }

        if ($this->option('pivot')) {
            return __DIR__ . '/../../stubs/Pivot.php';
        }

        return __DIR__ . '/../../stubs/Model.php';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Models';
    }

    protected function getOptions(): array
    {
        return [
            ['pivot', 'p', InputOption::VALUE_NONE, 'Indicates if the generated model should be a pivot'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
        ];
    }
}
