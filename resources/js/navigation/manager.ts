import { NavigationItem } from './index';

export const managerNavigation: NavigationItem[] = [
    {
        id: 'manager.workbench',
        label: 'Team Overview',
        icon: 'fa-solid fa-gauge-high',
        route: '/manager/workbench',
        permission: 'manager.team.view',
    },
    {
        id: 'manager.members',
        label: 'Team Roster',
        icon: 'fa-solid fa-users',
        route: '/manager/members',
        permission: 'manager.team.view',
    },
    {
        id: 'manager.approvals',
        label: 'Pending Approvals',
        icon: 'fa-solid fa-check-to-slot',
        route: '/portal/manager/approvals',
        permission: 'manager.approvals.manage',
        badge: 'Active',
    },
    {
        id: 'manager.attendance',
        label: 'Team Attendance',
        icon: 'fa-solid fa-calendar-check',
        route: '/hcm/manager/attendance',
        permission: 'manager.attendance.view',
    },
    {
        id: 'manager.performance',
        label: 'Team Reviews',
        icon: 'fa-solid fa-chart-line',
        route: '/manager/performance',
        permission: 'manager.reviews.manage',
    },
    {
        id: 'manager.analytics',
        label: 'Team Analytics',
        icon: 'fa-solid fa-chart-simple',
        route: '/manager/analytics',
        permission: 'manager.analytics.view',
    },
];
