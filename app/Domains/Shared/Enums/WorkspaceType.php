<?php

declare(strict_types=1);

namespace App\Domains\Shared\Enums;

enum WorkspaceType: string
{
    case PLATFORM_ADMIN = 'platform';
    case TENANT_ADMIN = 'tenant_admin';
    case HR_ADMIN = 'hr';
    case MANAGER = 'manager';
    case EMPLOYEE = 'employee';
    case EXECUTIVE = 'executive';
    case OPERATIONS = 'operations';

    public function label(): string
    {
        return match ($this) {
            self::PLATFORM_ADMIN => 'Platform Control Center',
            self::TENANT_ADMIN => 'Tenant Administration',
            self::HR_ADMIN => 'HR Operations',
            self::MANAGER => 'My Team',
            self::EMPLOYEE => 'Employee Self-Service',
            self::EXECUTIVE => 'Workforce Intelligence',
            self::OPERATIONS => 'System Operations',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PLATFORM_ADMIN => 'Global SaaS control plane, tenant provisioning, and platform governance.',
            self::TENANT_ADMIN => 'Organization settings, departments, positions, roles, and compliance policies.',
            self::HR_ADMIN => 'Workforce administration, recruitment, payroll, benefits, and employee lifecycle.',
            self::MANAGER => 'Team overview, attendance, leave approvals, schedules, and team performance.',
            self::EMPLOYEE => 'Personal workspace, schedule, leaves, payslips, requests, and personal career.',
            self::EXECUTIVE => 'Strategic workforce analytics, headcount trajectory, costs, and executive KPIs.',
            self::OPERATIONS => 'Health monitoring, background queues, integrations, and operational logs.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PLATFORM_ADMIN => 'fa-solid fa-server',
            self::TENANT_ADMIN => 'fa-solid fa-building-user',
            self::HR_ADMIN => 'fa-solid fa-users-gear',
            self::MANAGER => 'fa-solid fa-user-group',
            self::EMPLOYEE => 'fa-solid fa-user-tie',
            self::EXECUTIVE => 'fa-solid fa-chart-line',
            self::OPERATIONS => 'fa-solid fa-shield-halved',
        };
    }

    public function routePrefix(): string
    {
        return match ($this) {
            self::PLATFORM_ADMIN => '/platform',
            self::TENANT_ADMIN => '/admin',
            self::HR_ADMIN => '/hr',
            self::MANAGER => '/manager',
            self::EMPLOYEE => '/employee',
            self::EXECUTIVE => '/executive',
            self::OPERATIONS => '/operations',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::PLATFORM_ADMIN => 'platform.control-center',
            self::TENANT_ADMIN => 'admin.dashboard',
            self::HR_ADMIN => 'hr.dashboard',
            self::MANAGER => 'manager.workbench',
            self::EMPLOYEE => 'employee.home',
            self::EXECUTIVE => 'executive.overview',
            self::OPERATIONS => 'operations.system-health',
        };
    }

    public function themeColor(): string
    {
        return match ($this) {
            self::PLATFORM_ADMIN => '#0F172A', // Slate 900
            self::TENANT_ADMIN => '#1E293B',   // Slate 800
            self::HR_ADMIN => '#1E3A5F',       // Corporate Navy
            self::MANAGER => '#142A44',        // Navy Dark
            self::EMPLOYEE => '#1E3A5F',       // Corporate Navy
            self::EXECUTIVE => '#312E81',      // Indigo 900
            self::OPERATIONS => '#18181B',     // Zinc 900
        };
    }
}
