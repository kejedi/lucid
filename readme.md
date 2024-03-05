# Lucid

Declare database schemas inside Laravel models.

## Installation

Require this package via composer:

```console
composer require kejedi/lucid
```

## Usage

Create a new model class with a `schema` method:

```console
php artisan make:schema Post
```

Or, add a `schema` / `extraSchema` method to an existing model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kejedi\Lucid\Table;

class Post extends Model
{
    use HasFactory;

    // and / or extraSchema
    public function schema(Table $table): void 
    {
        $table->id();
        $table->string('title')->index();
        $table->text('body');
        $table->timestamp('created_at');
        $table->timestamp('updated_at');
    }
}
```

Migrate & sync model `schema` / `extraSchema` methods with the database:

```console
php artisan migrate:schemas
```

## Commands

Create a new model class with a `schema` method:

```console
php artisan make:schema {name} {--p|pivot} {--force}
```

Migrate & sync model `schema` / `extraSchema` methods with the database:

```console
migrate:schemas {--f|fresh} {--s|seed} {--force}
```
