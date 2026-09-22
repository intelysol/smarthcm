import { NavigationItem } from './index';

export const tenantNavigation: NavigationItem[] = [
    {
        id: 'admin.dashboard',
        label: 'Organization Overview',
        icon: 'fa-solid fa-gauge-high',
        route: '/admin/dashboard',
        permission: 'tenant.overview.view',
    },
    {
        id: 'admin.departments',
        label: 'Departments & Locations',
        icon: 'fa-solid fa-sitemap',
        route: '/admin/departments',
        permission: 'organization.departments.manage',
    },
    {
        id: 'admin.positions',
        label: 'Positions & Architecture',
        icon: 'fa-solid fa-id-badge',
        route: '/admin/positions',
        permission: 'organization.positions.manage',
    },
    {
        id: 'admin.users',
        label: 'Users & Access Control',
        icon: 'fa-solid fa-users-gear',
        route: '/admin/users',
        permission: 'tenant.users.manage',
    },
    {
        id: 'admin.policies',
        label: 'Policies & Compliance',
        icon: 'fa-solid fa-scale-balanced',
        route: '/compliance/dashboard',
        permission: 'compliance.policies.manage',
    },
    {
        id: 'admin.workflows',
        label: 'Workflows & Approvals',
        icon: 'fa-solid fa-diagram-project',
        route: '/admin/workflows',
        permission: 'workflows.manage',
    },
    {
        id: 'admin.settings',
        label: 'Organization Settings',
        icon: 'fa-solid fa-gears',
        route: '/admin/settings',
        permission: 'tenant.settings.manage',
    },
];
