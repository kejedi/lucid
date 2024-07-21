<?php

namespace Kejedi\Lucid\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class LucidModelCommand extends GeneratorCommand
{
    protected $name = 'lucid:model';

    protected $description = 'Create a new Eloquent model class with Lucid schema & definition methods';

    protected $type = 'Model';

    public function handle()
    {
        if (parent::handle() === false && !$this->option('force')) {
            return false;
        }

        if ($this->argument('name') == 'User') {
            $this->replaceUserMigration();
        }

        if (!$this->option('pivot')) {
            $this->createFactory();
        }

        if ($this->option('resource')) {
            $this->createResource();
        }
    }

    protected function replaceUserMigration()
    {
        $file = database_path('migrations/0001_01_01_000000_create_users_table.php');

        copy(__DIR__ . '/../../stubs/UserMigration.php', $file);

        $this->components->info(sprintf('Migration [%s] replaced successfully.', $file));
    }

    protected function createFactory()
    {
        $this->call('lucid:factory', [
            'name' => $this->argument('name'),
            '--force' => $this->option('force'),
        ]);
    }

    protected function createResource()
    {
        $this->call('make:filament-resource', [
            'name' => $this->argument('name'),
            '--view' => true,
        ]);
    }

    protected function getStub()
    {
        if ($this->argument('name') == 'User') {
            return __DIR__ . '/../../stubs/UserModel.php';
        }

        if ($this->option('pivot')) {
            return __DIR__ . '/../../stubs/Pivot.php';
        }

        return __DIR__ . '/../../stubs/Model.php';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Models';
    }

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
            ['pivot', 'p', InputOption::VALUE_NONE, 'Indicates if the generated model should be a custom intermediate table model'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Create a new Filament resource for the model (Filament must be installed first)'],
        ];
    }
}
