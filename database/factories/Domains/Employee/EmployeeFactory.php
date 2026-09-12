<?php

namespace Database\Factories\Domains\Employee;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'employee_number' => 'EMP-'.fake()->unique()->numerify('######'),
            'employee_code' => fake()->unique()->bothify('E-####'),
            'company_id' => Company::factory(),
            'employment_status' => 'active',
            'joining_date' => now()->subMonths(3)->toDateString(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => 'not_disclosed',
            'official_email' => fake()->unique()->companyEmail(),
            'mobile' => fake()->phoneNumber(),
        ];
    }
}
