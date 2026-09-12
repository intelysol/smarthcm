<?php

namespace App\Providers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Employee\Policies\EmployeePolicy;
use App\Domains\Metadata\Events\MetadataChanged;
use App\Domains\Metadata\Listeners\InvalidateMetadataCache;
use App\Domains\Metadata\Models\MetadataEntity;
use App\Domains\Metadata\Policies\MetadataEntityPolicy;
use App\Domains\Rules\Events\RuleExecuted;
use App\Domains\Rules\Listeners\RecordRuleExecution;
use App\Domains\Rules\Models\BusinessRule;
use App\Domains\Rules\Policies\BusinessRulePolicy;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Policies\CompanyPolicy;
use App\Domains\Organization\Repositories\CompanyRepositoryInterface;
use App\Domains\Organization\Repositories\EloquentCompanyRepository;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Platform\Events\RoleAssigned;
use App\Domains\Platform\Events\UserAuthenticated;
use App\Domains\Platform\Listeners\RecordLoginHistory;
use App\Domains\Platform\Listeners\RecordRoleAssignment;
use App\Domains\Platform\Models\Role;
use App\Domains\Platform\Policies\RolePolicy;
use App\Domains\Platform\Services\RequestTenantContext;
use App\Domains\Performance\Models\PerformanceCycle;
use App\Domains\Performance\Models\PerformanceGoal;
use App\Domains\Performance\Policies\PerformanceCyclePolicy;
use App\Domains\Performance\Policies\PerformanceGoalPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CompanyRepositoryInterface::class, EloquentCompanyRepository::class);
        $this->app->scoped(TenantContext::class, RequestTenantContext::class);
    }

    public function boot(): void
    {
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(MetadataEntity::class, MetadataEntityPolicy::class);
        Gate::policy(BusinessRule::class, BusinessRulePolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(PerformanceCycle::class, PerformanceCyclePolicy::class);
        Gate::policy(PerformanceGoal::class, PerformanceGoalPolicy::class);
        Event::listen(UserAuthenticated::class, RecordLoginHistory::class);
        Event::listen(RoleAssigned::class, RecordRoleAssignment::class);
        Event::listen(MetadataChanged::class, InvalidateMetadataCache::class);
        Event::listen(RuleExecuted::class, RecordRuleExecution::class);
    }
}
