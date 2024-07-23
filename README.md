# Lucid

Declare database schemas and factory definitions inside Laravel models.

## Installation

Require Lucid via composer:

```console
composer require kejedi/lucid
```

## Usage

### Using the `lucid:model` Command

Create a new Eloquent model class with Lucid schema & definition methods:

```console
php artisan lucid:model Post
```

### Manually Adding Schemas & Definitions

You can also add `lucidSchema` and `lucidDefinition` methods to an existing model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kejedi\Lucid\Database\LucidBlueprint;

class Post extends Model
{
    use HasFactory;

    public function lucidSchema(LucidBlueprint $table): void
    {
        $table->id();
        $table->string('title');
        $table->text('body');
        $table->timestamps();
    }

    public function lucidDefinition(): array
    {
        return [
            'title' => fake()->sentence(),
            'body' => fake()->paragraph(),
        ];
    }
}
```

If adding methods manually, be sure to create a Lucid factory for the model:

```console
php artisan lucid:factory Post
```

### Defining Multiple Schemas & Definitions

Define multiple schemas & definitions by using `lucid` and `schema`/`definition` in method names:

```php
namespace App\Traits;

use App\Models\Tenant;
use Kejedi\Lucid\Database\LucidBlueprint;

trait HasTenant
{
    public function lucidTenantSchema(LucidBlueprint $table): void
    {
        $table->integer('tenant_id')->index();
    }

    public function lucidTenantDefinition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
        ];
    }
}
```

This is useful for things like traits, multi-tenancy, etc.

## Migrating & Syncing

Migrate & sync Lucid model schemas with the database:

```console
php artisan lucid:migrate
```

This will migrate traditional Laravel database files, then sync model schema methods automatically.

## Commands

### `lucid:model`

Create a new Eloquent model class with Lucid schema & definition methods.

```console
php artisan lucid:model {name} {--force} {--p|pivot} {--r|resource}
```

- `name`: the model name
- `--force`: Create the class even if the model already exists
- `--pivot` or `-p`: Indicates if the generated model should be a custom intermediate table model
- `--resource` or `-r`: Create a new Filament resource for the model (Filament must be installed first)

### `lucid:factory`

Create a new model factory for Lucid definition methods.

```console
php artisan lucid:factory {name} {--force}
```

- `name`: the model name for the factory
- `--force`: Create the class even if the factory already exists

### `lucid:migrate`

Migrate & sync Lucid model schemas with the database.

```console
php artisan lucid:migrate {--force} {--f|fresh} {--s|seed}
```

- `--force`: Force the operation to run when in production
- `--fresh` or `-f`: Drop all tables from the database and then execute the migrate command
- `--seed` or `-s`: Indicates if the seed task should be re-run

## Notes

- Lucid definition methods only work with a `LucidFactory`
- IDE helper files are automatically created after migrating in non-production
- Renaming columns will result in data loss unless they are renamed before running `lucid:migrate`
- This package only works with sqlite, mysql, & pgsql PDO drivers
- Use the `--force` to create a Lucid `User` model in new Laravel apps
- All columns are nullable by default
- All models are unguarded by default
