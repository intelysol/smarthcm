<?php

namespace Database\Factories\Domains\Metadata;

use App\Domains\Metadata\Models\MetadataField;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MetadataField> */
class MetadataFieldFactory extends Factory
{
    protected $model = MetadataField::class;
    public function definition(): array { return ['key' => fake()->unique()->slug(2), 'label' => fake()->words(2, true), 'field_type' => 'text', 'sort_order' => 0, 'configuration' => ['required' => false], 'row_version' => 1]; }
}
