<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Health Requirement Types
        Schema::create('hcm_health_requirement_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('code', 50);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('default_renewal_required')->default(false);
            $table->integer('default_validity_months')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        // 2. Health Requirements
        Schema::create('hcm_health_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('health_requirement_type_id')->index();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('country', 50)->nullable();
            $table->uuid('company_id')->nullable()->index();
            $table->uuid('branch_id')->nullable()->index();
            $table->uuid('work_location_id')->nullable()->index();
            $table->uuid('department_id')->nullable()->index();
            $table->uuid('designation_id')->nullable()->index();
            $table->string('exposure_category', 50)->nullable(); // noise, chemical, biological, radiation, ergonomic, physical
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('is_safety_critical')->default(false);
            $table->boolean('renewal_required')->default(false);
            $table->integer('validity_months')->nullable();
            $table->integer('grace_period_days')->default(0);
            $table->integer('reminder_days')->default(30);
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('health_requirement_type_id')->references('id')->on('hcm_health_requirement_types')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code', 'version']);
        });

        // 3. Employee Assigned Health Requirements
        Schema::create('hcm_employee_health_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('health_requirement_id')->index();
            $table->string('status', 30)->default('required')->index(); // required, scheduled, compliant, expiring, expired, waived, exempt
            $table->date('due_date')->nullable()->index();
            $table->date('completed_date')->nullable();
            $table->date('expiry_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('health_requirement_id')->references('id')->on('hcm_health_requirements')->cascadeOnDelete();
            $table->unique(['tenant_id', 'employee_id', 'health_requirement_id'], 'emp_health_req_unique');
        });

        // 4. Medical Providers
        Schema::create('hcm_medical_providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name', 150);
            $table->string('organization_name', 150)->nullable();
            $table->string('provider_type', 50)->default('clinic'); // clinic, hospital, independent_physician, lab, occupational_center
            $table->string('license_number', 100)->nullable();
            $table->string('country', 50);
            $table->string('city', 100)->nullable();
            $table->string('contact_email', 100)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('status', 30)->default('active'); // active, inactive, under_review
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        // 5. Medical Assessments (Examinations)
        Schema::create('hcm_medical_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('health_requirement_id')->nullable()->index();
            $table->uuid('medical_provider_id')->nullable()->index();
            $table->string('assessment_type', 80); // pre_employment, periodic, fitness_for_duty, return_to_work, vision, hearing, exposure
            $table->date('requested_date')->nullable();
            $table->date('scheduled_date')->nullable()->index();
            $table->date('completed_date')->nullable();
            $table->string('status', 30)->default('requested')->index(); // requested, scheduled, completed, cancelled, no_show
            $table->foreignId('scheduled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('document_id')->nullable();
            $table->text('operational_notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('health_requirement_id')->references('id')->on('hcm_health_requirements')->nullOnDelete();
            $table->foreign('medical_provider_id')->references('id')->on('hcm_medical_providers')->nullOnDelete();
        });

        // 6. Medical Fitness Records (Administrative Determinations)
        Schema::create('hcm_medical_fitness_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('assessment_id')->nullable()->index();
            $table->uuid('medical_provider_id')->nullable()->index();
            $table->string('fitness_status', 30)->default('fit')->index(); // fit, fit_with_restrictions, temporarily_unfit, unfit, pending_assessment
            $table->date('determined_date');
            $table->date('effective_from');
            $table->date('effective_to')->nullable()->index();
            $table->date('next_review_date')->nullable();
            $table->string('certificate_reference', 100)->nullable();
            $table->uuid('document_id')->nullable();
            $table->boolean('has_restrictions')->default(false);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('summary_notes')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('assessment_id')->references('id')->on('hcm_medical_assessments')->nullOnDelete();
            $table->foreign('medical_provider_id')->references('id')->on('hcm_medical_providers')->nullOnDelete();
        });

        // 7. Medical Restrictions (Operational Work Limitations)
        Schema::create('hcm_medical_restrictions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->uuid('fitness_record_id')->nullable()->index();
            $table->string('restriction_type', 80); // lifting, night_shift, travel, chemical_exposure, physical_workload, machinery_operation, standing_limit, custom
            $table->string('title', 150);
            $table->text('operational_description'); // Safe for manager viewing (e.g. "No lifting over 25 lbs")
            $table->text('medical_rationale_restricted')->nullable(); // Strictly restricted to Occupational Health / Medical roles
            $table->date('effective_from');
            $table->date('effective_to')->nullable()->index();
            $table->boolean('is_permanent')->default(false);
            $table->string('status', 30)->default('active')->index(); // active, expired, resolved, revoked
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('document_id')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('fitness_record_id')->references('id')->on('hcm_medical_fitness_records')->nullOnDelete();
        });

        // 8. Return to Work Cases
        Schema::create('hcm_return_to_work_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('case_number', 50)->unique();
            $table->string('absence_reason', 80); // work_injury, non_work_injury, medical_illness, surgery, mental_health, maternity_medical
            $table->date('incident_or_absence_date')->nullable();
            $table->date('target_return_date')->nullable();
            $table->date('actual_return_date')->nullable();
            $table->string('return_phase', 40)->default('assessment_pending'); // assessment_pending, restricted_duties, graduated_hours, full_duties, closed
            $table->string('status', 30)->default('open')->index(); // open, in_progress, completed, cancelled
            $table->foreignId('case_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('plan_summary')->nullable();
            $table->uuid('clearance_document_id')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 9. Workplace Accommodations
        Schema::create('hcm_workplace_accommodations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('request_number', 50)->unique();
            $table->string('accommodation_type', 80); // ergonomic_equipment, schedule_adjustment, modified_workstation, facility_access, assistive_technology, light_duty
            $table->string('title', 150);
            $table->text('requested_adjustment');
            $table->string('decision', 30)->default('pending'); // pending, approved, rejected, implemented, under_review
            $table->date('review_date')->nullable();
            $table->date('implemented_date')->nullable();
            $table->decimal('cost_estimate', 10, 2)->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_notes')->nullable();
            $table->uuid('supporting_document_id')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // 10. Workplace Safety Incidents
        Schema::create('hcm_safety_incidents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->nullable()->index(); // Injured or primary involved employee
            $table->string('incident_number', 50)->unique();
            $table->dateTime('incident_datetime');
            $table->string('incident_type', 50)->index(); // injury, near_miss, illness, exposure, equipment_damage, environmental, safety_hazard
            $table->string('severity', 30)->default('minor')->index(); // minor, moderate, serious, critical, fatal
            $table->string('location_description', 255);
            $table->uuid('company_id')->nullable()->index();
            $table->uuid('branch_id')->nullable()->index();
            $table->uuid('work_location_id')->nullable()->index();
            $table->uuid('department_id')->nullable()->index();
            $table->text('description');
            $table->text('immediate_action_taken')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('reported')->index(); // reported, acknowledged, under_investigation, corrective_action, closed, reopened
            $table->boolean('is_osha_reportable')->default(false);
            $table->boolean('lost_time_injury')->default(false);
            $table->integer('lost_work_days')->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
        });

        // 11. Safety Incident Witnesses
        Schema::create('hcm_safety_incident_witnesses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('safety_incident_id')->index();
            $table->uuid('employee_id')->nullable()->index();
            $table->string('witness_name', 150);
            $table->string('contact_phone', 50)->nullable();
            $table->text('statement')->nullable();
            $table->date('statement_date')->nullable();
            $table->timestamps();

            $table->foreign('safety_incident_id')->references('id')->on('hcm_safety_incidents')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
        });

        // 12. Safety Incident Investigations
        Schema::create('hcm_safety_incident_investigations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('safety_incident_id')->unique();
            $table->foreignId('lead_investigator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('started_at');
            $table->date('completed_at')->nullable();
            $table->string('root_cause_category', 50)->nullable(); // human_factor, equipment_failure, procedure_gap, environmental, training_gap, management_control
            $table->text('root_cause_analysis')->nullable(); // 5-why, fishbone summary
            $table->text('findings');
            $table->text('contributing_factors')->nullable();
            $table->string('status', 30)->default('in_progress'); // in_progress, completed, approved
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('safety_incident_id')->references('id')->on('hcm_safety_incidents')->cascadeOnDelete();
        });

        // 13. Safety Incident Corrective / Preventive Actions (CAPA)
        Schema::create('hcm_safety_incident_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('safety_incident_id')->index();
            $table->string('action_type', 50)->default('corrective'); // corrective, preventive
            $table->string('title', 150);
            $table->text('description');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->index();
            $table->string('priority', 30)->default('medium'); // low, medium, high, critical
            $table->string('status', 30)->default('open')->index(); // open, in_progress, completed, verified, cancelled
            $table->date('completed_date')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->uuid('evidence_document_id')->nullable();
            $table->timestamps();

            $table->foreign('safety_incident_id')->references('id')->on('hcm_safety_incidents')->cascadeOnDelete();
        });

        // 14. Workplace Environmental Exposures
        Schema::create('hcm_workplace_exposures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id')->index();
            $table->string('hazard_type', 50); // chemical, biological, noise, radiation, dust, heat, cold, ergonomic
            $table->string('hazard_name', 150);
            $table->string('exposure_level', 50)->nullable(); // low, moderate, high, action_level, permissible_limit_exceeded
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('surveillance_required')->default(false);
            $table->integer('surveillance_interval_months')->nullable();
            $table->text('controls_in_place')->nullable(); // PPE, ventilation, shielding
            $table->string('status', 30)->default('active'); // active, ceased, monitored
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hcm_workplace_exposures');
        Schema::dropIfExists('hcm_safety_incident_actions');
        Schema::dropIfExists('hcm_safety_incident_investigations');
        Schema::dropIfExists('hcm_safety_incident_witnesses');
        Schema::dropIfExists('hcm_safety_incidents');
        Schema::dropIfExists('hcm_workplace_accommodations');
        Schema::dropIfExists('hcm_return_to_work_cases');
        Schema::dropIfExists('hcm_medical_restrictions');
        Schema::dropIfExists('hcm_medical_fitness_records');
        Schema::dropIfExists('hcm_medical_assessments');
        Schema::dropIfExists('hcm_medical_providers');
        Schema::dropIfExists('hcm_employee_health_requirements');
        Schema::dropIfExists('hcm_health_requirements');
        Schema::dropIfExists('hcm_health_requirement_types');
    }
};
