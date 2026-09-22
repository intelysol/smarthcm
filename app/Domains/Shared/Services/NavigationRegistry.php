<?php

declare(strict_types=1);

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Enums\WorkspaceType;
use App\Models\User;

class NavigationRegistry
{
    /**
     * Build navigation tree for a specific workspace.
     *
     * @return array<array<string, mixed>>
     */
    public function getNavigationFor(WorkspaceType $workspace, ?User $user = null): array
    {
        return match ($workspace) {
            WorkspaceType::PLATFORM_ADMIN => $this->getPlatformNavigation(),
            WorkspaceType::TENANT_ADMIN => $this->getTenantNavigation(),
            WorkspaceType::HR_ADMIN => $this->getHrNavigation(),
            WorkspaceType::MANAGER => $this->getManagerNavigation(),
            WorkspaceType::EMPLOYEE => $this->getEmployeeNavigation(),
            WorkspaceType::EXECUTIVE => $this->getExecutiveNavigation(),
            WorkspaceType::OPERATIONS => $this->getOperationsNavigation(),
        };
    }

    /**
     * 1. Platform / Super Admin Navigation
     */
    public function getPlatformNavigation(): array
    {
        return [
            [
                'id' => 'platform.control-center',
                'label' => 'Control Center',
                'icon' => 'fa-solid fa-gauge-high',
                'route' => 'platform.control-center',
            ],
            [
                'id' => 'platform.tenants',
                'label' => 'Tenants & Provisioning',
                'icon' => 'fa-solid fa-building',
                'route' => 'platform.tenants',
            ],
            [
                'id' => 'platform.products',
                'label' => 'Products & Subscriptions',
                'icon' => 'fa-solid fa-box-archive',
                'route' => 'platform.billing',
            ],
            [
                'id' => 'platform.integrations',
                'label' => 'Integrations Hub',
                'icon' => 'fa-solid fa-plug',
                'route' => 'platform.integrations',
            ],
            [
                'id' => 'platform.operations',
                'label' => 'System Health & Queues',
                'icon' => 'fa-solid fa-server',
                'route' => 'operations.system-health',
            ],
            [
                'id' => 'platform.security',
                'label' => 'Security & Audit',
                'icon' => 'fa-solid fa-shield-halved',
                'route' => 'platform.security',
            ],
            [
                'id' => 'platform.ai-governance',
                'label' => 'AI Governance & Models',
                'icon' => 'fa-solid fa-brain',
                'route' => 'platform.ai-governance',
            ],
            [
                'id' => 'platform.users',
                'label' => 'Platform Users & Admins',
                'icon' => 'fa-solid fa-user-shield',
                'route' => 'platform.users',
            ],
            [
                'id' => 'platform.settings',
                'label' => 'Platform Settings',
                'icon' => 'fa-solid fa-sliders',
                'route' => 'platform.settings',
            ],
            [
                'id' => 'platform.help',
                'label' => 'Help & Documentation',
                'icon' => 'fa-solid fa-circle-question',
                'route' => 'help.index',
            ],
        ];
    }

    /**
     * 2. Tenant Administration Navigation
     */
    public function getTenantNavigation(): array
    {
        return [
            [
                'id' => 'admin.dashboard',
                'label' => 'Organization Overview',
                'icon' => 'fa-solid fa-gauge-high',
                'route' => 'admin.dashboard',
            ],
            [
                'id' => 'admin.departments',
                'label' => 'Departments & Locations',
                'icon' => 'fa-solid fa-sitemap',
                'route' => 'admin.departments',
            ],
            [
                'id' => 'admin.positions',
                'label' => 'Positions & Architecture',
                'icon' => 'fa-solid fa-id-badge',
                'route' => 'admin.positions',
            ],
            [
                'id' => 'admin.users',
                'label' => 'Users & Access Control',
                'icon' => 'fa-solid fa-users-gear',
                'route' => 'admin.users',
            ],
            [
                'id' => 'admin.policies',
                'label' => 'Policies & Compliance',
                'icon' => 'fa-solid fa-scale-balanced',
                'route' => 'compliance.dashboard',
            ],
            [
                'id' => 'admin.documents',
                'label' => 'Document Templates',
                'icon' => 'fa-solid fa-folder-tree',
                'route' => 'employee_documents.admin.templates',
            ],
            [
                'id' => 'admin.workflows',
                'label' => 'Workflows & Approvals',
                'icon' => 'fa-solid fa-diagram-project',
                'route' => 'admin.workflows',
            ],
            [
                'id' => 'admin.operations',
                'label' => 'Tenant Operations & Health',
                'icon' => 'fa-solid fa-server',
                'route' => 'admin.operations',
            ],
            [
                'id' => 'admin.data-lifecycle',
                'label' => 'Data Retention & Archival',
                'icon' => 'fa-solid fa-box-archive',
                'route' => 'admin.data-lifecycle',
            ],
            [
                'id' => 'admin.compliance-governance',
                'label' => 'Compliance & Privacy Governance',
                'icon' => 'fa-solid fa-shield-halved',
                'route' => 'admin.compliance-governance',
            ],
            [
                'id' => 'admin.settings',
                'label' => 'Organization Settings',
                'icon' => 'fa-solid fa-gears',
                'route' => 'admin.settings',
            ],
            [
                'id' => 'admin.help',
                'label' => 'Help & Documentation',
                'icon' => 'fa-solid fa-circle-question',
                'route' => 'help.index',
            ],
        ];
    }

    /**
     * 3. HR Operations Navigation
     */
    public function getHrNavigation(): array
    {
        return [
            [
                'id' => 'hr.dashboard',
                'label' => 'HR Command Center',
                'icon' => 'fa-solid fa-tower-broadcast',
                'route' => 'hr.dashboard',
            ],
            [
                'id' => 'hr.workforce',
                'label' => 'People Directory',
                'icon' => 'fa-solid fa-users',
                'route' => 'portal.directory',
            ],
            [
                'id' => 'hr.recruitment',
                'label' => 'Recruitment & Jobs',
                'icon' => 'fa-solid fa-user-plus',
                'route' => 'recruitment.requisitions.index',
            ],
            [
                'id' => 'hr.onboarding',
                'label' => 'Onboarding',
                'icon' => 'fa-solid fa-door-open',
                'route' => 'onboarding.programs.index',
            ],
            [
                'id' => 'hr.attendance',
                'label' => 'Time & Attendance',
                'icon' => 'fa-solid fa-clock',
                'route' => 'hcm.attendance.dashboard',
            ],
            [
                'id' => 'hr.payroll',
                'label' => 'Payroll & Compensation',
                'icon' => 'fa-solid fa-file-invoice-dollar',
                'route' => 'payroll.dashboard',
            ],
            [
                'id' => 'hr.benefits',
                'label' => 'Benefits & Health',
                'icon' => 'fa-solid fa-heart-pulse',
                'route' => 'benefits.programs.index',
            ],
            [
                'id' => 'hr.expenses',
                'label' => 'Expenses Management',
                'icon' => 'fa-solid fa-receipt',
                'route' => 'expenses.claims.index',
            ],
            [
                'id' => 'hr.performance',
                'label' => 'Performance & Goals',
                'icon' => 'fa-solid fa-bullseye',
                'route' => 'performance.cycles.index',
            ],
            [
                'id' => 'hr.learning',
                'label' => 'Learning & Training',
                'icon' => 'fa-solid fa-graduation-cap',
                'route' => 'learning.catalog',
            ],
            [
                'id' => 'hr.cases',
                'label' => 'Employee Relations',
                'icon' => 'fa-solid fa-handshake-angle',
                'route' => 'employee-relations.cases.index',
            ],
            [
                'id' => 'hr.analytics',
                'label' => 'HR Reports & Analytics',
                'icon' => 'fa-solid fa-chart-pie',
                'route' => 'analytics.workforce.metrics',
            ],
            [
                'id' => 'hr.help',
                'label' => 'Help & Guides',
                'icon' => 'fa-solid fa-circle-question',
                'route' => 'help.index',
            ],
        ];
    }

    /**
     * 4. Manager Workspace Navigation
     */
    public function getManagerNavigation(): array
    {
        return [
            [
                'id' => 'manager.workbench',
                'label' => 'Team Overview',
                'icon' => 'fa-solid fa-gauge-high',
                'route' => 'manager.workbench',
            ],
            [
                'id' => 'manager.members',
                'label' => 'Team Roster',
                'icon' => 'fa-solid fa-users',
                'route' => 'manager.members',
            ],
            [
                'id' => 'manager.approvals',
                'label' => 'Pending Approvals',
                'icon' => 'fa-solid fa-check-to-slot',
                'route' => 'portal.manager.approvals',
                'badge' => 'Active',
            ],
            [
                'id' => 'manager.attendance',
                'label' => 'Team Attendance & Shifts',
                'icon' => 'fa-solid fa-calendar-check',
                'route' => 'hcm.attendance.manager',
            ],
            [
                'id' => 'manager.performance',
                'label' => 'Team Reviews & Goals',
                'icon' => 'fa-solid fa-chart-line',
                'route' => 'manager.performance',
            ],
            [
                'id' => 'manager.analytics',
                'label' => 'Team Analytics',
                'icon' => 'fa-solid fa-chart-simple',
                'route' => 'manager.analytics',
            ],
            [
                'id' => 'manager.help',
                'label' => 'Help & Guides',
                'icon' => 'fa-solid fa-circle-question',
                'route' => 'help.index',
            ],
        ];
    }

    /**
     * 5. Employee Self-Service Navigation (No administrative clutter)
     */
    public function getEmployeeNavigation(): array
    {
        return [
            [
                'id' => 'employee.home',
                'label' => 'My Home',
                'icon' => 'fa-solid fa-house',
                'route' => 'employee.home',
            ],
            [
                'id' => 'employee.work',
                'label' => 'My Work & Schedule',
                'icon' => 'fa-solid fa-briefcase',
                'route' => 'portal.work',
            ],
            [
                'id' => 'employee.requests',
                'label' => 'My Leaves & Requests',
                'icon' => 'fa-solid fa-code-pull-request',
                'route' => 'portal.requests',
            ],
            [
                'id' => 'employee.profile',
                'label' => 'My Profile & Info',
                'icon' => 'fa-solid fa-id-badge',
                'route' => 'portal.profile',
            ],
            [
                'id' => 'employee.pay',
                'label' => 'My Payslips & Tax',
                'icon' => 'fa-solid fa-file-invoice-dollar',
                'route' => 'portal.pay',
            ],
            [
                'id' => 'employee.growth',
                'label' => 'My Growth & Learning',
                'icon' => 'fa-solid fa-arrow-trend-up',
                'route' => 'portal.growth',
            ],
            [
                'id' => 'employee.documents',
                'label' => 'My Documents',
                'icon' => 'fa-solid fa-folder-open',
                'route' => 'portal.documents',
            ],
            [
                'id' => 'employee.services',
                'label' => 'HR Service Catalog',
                'icon' => 'fa-solid fa-headset',
                'route' => 'portal.services',
            ],
            [
                'id' => 'employee.directory',
                'label' => 'Colleagues & People',
                'icon' => 'fa-solid fa-address-book',
                'route' => 'portal.directory',
            ],
            [
                'id' => 'employee.privacy',
                'label' => 'Privacy & My Data Rights',
                'icon' => 'fa-solid fa-user-shield',
                'route' => 'portal.privacy',
            ],
            [
                'id' => 'employee.help',
                'label' => 'Help & Knowledge Base',
                'icon' => 'fa-solid fa-circle-question',
                'route' => 'help.index',
            ],
        ];
    }

    /**
     * 6. Executive Workspace Navigation
     */
    public function getExecutiveNavigation(): array
    {
        return [
            [
                'id' => 'executive.overview',
                'label' => 'Strategic Overview',
                'icon' => 'fa-solid fa-chart-line',
                'route' => 'executive.overview',
            ],
            [
                'id' => 'executive.headcount',
                'label' => 'Headcount & Retention',
                'icon' => 'fa-solid fa-users-viewfinder',
                'route' => 'analytics.workforce.metrics',
            ],
            [
                'id' => 'executive.costs',
                'label' => 'Total Workforce Cost',
                'icon' => 'fa-solid fa-coins',
                'route' => 'executive.costs',
            ],
            [
                'id' => 'executive.productivity',
                'label' => 'Productivity & Capacity',
                'icon' => 'fa-solid fa-bolt',
                'route' => 'workforce-productivity.dashboard',
            ],
            [
                'id' => 'executive.planning',
                'label' => 'Strategic Planning',
                'icon' => 'fa-solid fa-compass-drafting',
                'route' => 'workforce-planning.index',
            ],
            [
                'id' => 'executive.intelligence',
                'label' => 'Workforce Intelligence',
                'icon' => 'fa-solid fa-lightbulb',
                'route' => 'workforce-intelligence.dashboard',
            ],
        ];
    }

    /**
     * 7. Operations Workspace Navigation
     */
    public function getOperationsNavigation(): array
    {
        return [
            [
                'id' => 'operations.dashboard',
                'label' => 'Operations Control Plane',
                'icon' => 'fa-solid fa-gauge-high',
                'route' => 'operations.dashboard',
            ],
            [
                'id' => 'operations.health',
                'label' => 'System Health & Metrics',
                'icon' => 'fa-solid fa-heart-pulse',
                'route' => 'operations.system-health',
            ],
            [
                'id' => 'operations.incidents',
                'label' => 'Incident Management',
                'icon' => 'fa-solid fa-triangle-exclamation',
                'route' => 'operations.incidents',
            ],
            [
                'id' => 'operations.alerts',
                'label' => 'Active Alerts & Rules',
                'icon' => 'fa-solid fa-bell',
                'route' => 'operations.alerts',
            ],
            [
                'id' => 'operations.queues',
                'label' => 'Queues & Horizon',
                'icon' => 'fa-solid fa-bars-staggered',
                'route' => 'operations.queues',
            ],
            [
                'id' => 'operations.backups',
                'label' => 'Backup Snapshots',
                'icon' => 'fa-solid fa-database',
                'route' => 'operations.backups',
            ],
            [
                'id' => 'operations.recovery',
                'label' => 'Disaster Recovery & HA',
                'icon' => 'fa-solid fa-shield-heart',
                'route' => 'operations.recovery',
            ],
            [
                'id' => 'operations.performance',
                'label' => 'Performance & Telemetry',
                'icon' => 'fa-solid fa-gauge',
                'route' => 'operations.performance',
            ],
            [
                'id' => 'operations.capacity',
                'label' => 'Capacity & Scalability',
                'icon' => 'fa-solid fa-chart-area',
                'route' => 'operations.capacity',
            ],
            [
                'id' => 'operations.data-lifecycle',
                'label' => 'Data Lifecycle & Retention',
                'icon' => 'fa-solid fa-recycle',
                'route' => 'operations.data-lifecycle',
            ],
            [
                'id' => 'operations.compliance',
                'label' => 'Compliance & Governance',
                'icon' => 'fa-solid fa-scale-balanced',
                'route' => 'operations.compliance',
            ],
            [
                'id' => 'operations.billing',
                'label' => 'Commercial Billing & Invoices',
                'icon' => 'fa-solid fa-file-invoice',
                'route' => 'portal.billing.index',
            ],
            [
                'id' => 'operations.devices',
                'label' => 'Biometric Devices',
                'icon' => 'fa-solid fa-fingerprint',
                'route' => 'hcm.attendance.devices',
            ],
            [
                'id' => 'operations.logs',
                'label' => 'Operational Logs & Telemetry',
                'icon' => 'fa-solid fa-terminal',
                'route' => 'operations.logs',
            ],
            [
                'id' => 'operations.help',
                'label' => 'Help & Runbooks',
                'icon' => 'fa-solid fa-circle-question',
                'route' => 'help.index',
            ],
        ];
    }
}
