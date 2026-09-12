<?php

namespace Database\Factories\Domains\Metadata;

use App\Domains\Metadata\Models\MetadataEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MetadataEntity> */
class MetadataEntityFactory extends Factory
{
    protected $model = MetadataEntity::class;
    public function definition(): array { return ['key' => fake()->unique()->slug(2), 'label' => fake()->words(2, true), 'entity_type' => 'master', 'status' => 'draft', 'version' => 1, 'row_version' => 1, 'supports_soft_deletes' => true, 'supports_audit' => true]; }
}
