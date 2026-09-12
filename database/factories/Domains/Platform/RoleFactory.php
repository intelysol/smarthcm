<?php

namespace Database\Factories\Domains\Platform;

use App\Domains\Platform\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Role> */
class RoleFactory extends Factory
{
    protected $model = Role::class;
    public function definition(): array { return ['name' => fake()->unique()->slug(2), 'label' => fake()->jobTitle(), 'description' => fake()->sentence(), 'is_system' => false]; }
}
