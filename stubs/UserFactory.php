<?php

namespace Database\Factories;

use Kejedi\Lucid\Database\LucidFactory;

class UserFactory extends LucidFactory
{
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
