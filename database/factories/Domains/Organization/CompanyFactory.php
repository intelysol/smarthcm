<?php

namespace Database\Factories\Domains\Organization;

use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->unique()->company(),
            'legal_name' => fake()->company(),
            'registration_number' => fake()->bothify('REG-#######'),
            'tax_number' => fake()->bothify('TAX-#######'),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => fake()->url(),
            'timezone' => 'UTC',
            'currency' => 'USD',
            'is_active' => true,
        ];
    }
}
