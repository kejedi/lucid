<?php

namespace DummyNamespace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Kejedi\Lucid\Table;
use Laravel\Sanctum\HasApiTokens;

class DummyClass extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function schema(Table $table): void
    {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at');
        $table->string('password');
        $table->rememberToken();
        $table->timestamp('created_at');
        $table->timestamp('updated_at');
    }
}
