<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Performance\Models\Competency;
use App\Domains\Performance\Models\CompetencyCategory;
use App\Domains\Performance\Models\CompetencyFramework;
use App\Domains\Performance\Models\CompetencyLevel;
use App\Domains\Performance\Models\PerformanceRatingScale;
use App\Domains\Performance\Models\PerformanceRatingScaleItem;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class PerformanceDefaultRatingScaleSeeder extends Seeder
{
    public function run(): void
    {
         = Tenant::all();

        foreach ( as ) {
            ->seedTenantData(->id);
        }
    }

    public function seedTenantData(string ): void
    {
        // 1. Standard 5-Point Performance Rating Scale
         = PerformanceRatingScale::firstOrCreate(
            [
                'tenant_id' => ,
                'name' => 'Standard 5-Point Scale',
            ],
            [
                'rating_type' => 'numeric',
                'is_default' => true,
            ]
        );

         = [
            ['value' => 1.0, 'label' => 'Does Not Meet Expectations', 'min_score' => 0.0, 'max_score' => 1.49, 'description' => 'Consistently falls below job standards and objectives'],
            ['value' => 2.0, 'label' => 'Partially Meets Expectations', 'min_score' => 1.5, 'max_score' => 2.49, 'description' => 'Meets some objectives but requires improvement in key areas'],
            ['value' => 3.0, 'label' => 'Meets Expectations', 'min_score' => 2.5, 'max_score' => 3.49, 'description' => 'Consistently meets performance standards and accomplishes target goals'],
            ['value' => 4.0, 'label' => 'Exceeds Expectations', 'min_score' => 3.5, 'max_score' => 4.49, 'description' => 'Frequently surpasses targets and demonstrates high competency'],
            ['value' => 5.0, 'label' => 'Outstanding', 'min_score' => 4.5, 'max_score' => 5.0, 'description' => 'Demonstrates exceptional sustained leadership and breakthrough outcomes'],
        ];

        foreach ( as ) {
            PerformanceRatingScaleItem::firstOrCreate(
                [
                    'rating_scale_id' => ->id,
                    'value' => ['value'],
                ],
                
            );
        }

        // 2. Default Core Competency Framework
         = CompetencyFramework::firstOrCreate(
            [
                'tenant_id' => ,
                'name' => 'Core Enterprise Competencies',
            ],
            [
                'description' => 'Universal organizational behavioral and functional competencies',
                'status' => 'active',
            ]
        );

         = CompetencyCategory::firstOrCreate(
            [
                'framework_id' => ->id,
                'name' => 'Core Professional Skills',
            ],
            [
                'description' => 'Essential cross-functional performance behaviors',
                'sort_order' => 1,
            ]
        );

         = [
            ['name' => 'Collaboration & Teamwork', 'description' => 'Builds constructive relationships across team boundaries'],
            ['name' => 'Accountability & Execution', 'description' => 'Takes ownership of deliverables and reliably executes on commitments'],
            ['name' => 'Innovation & Problem Solving', 'description' => 'Identifies creative solutions and drives continuous workflow improvement'],
        ];

        foreach ( as ) {
             = Competency::firstOrCreate(
                [
                    'tenant_id' => ,
                    'category_id' => ->id,
                    'name' => ['name'],
                ],
                [
                    'description' => ['description'],
                    'status' => 'active',
                ]
            );

            for ( = 1;  <= 5; ++) {
                CompetencyLevel::firstOrCreate(
                    [
                        'competency_id' => ->id,
                        'level' => ,
                    ],
                    [
                        'label' => 'Level ' . ,
                        'description' => 'Proficiency level ' .  . ' description',
                    ]
                );
            }
        }
    }
}
