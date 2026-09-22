import { NavigationItem } from './index';

export const executiveNavigation: NavigationItem[] = [
    {
        id: 'executive.overview',
        label: 'Strategic Overview',
        icon: 'fa-solid fa-chart-line',
        route: '/executive/overview',
        permission: 'analytics.strategic.view',
    },
    {
        id: 'executive.headcount',
        label: 'Headcount & Retention',
        icon: 'fa-solid fa-users-viewfinder',
        route: '/analytics/workforce/metrics',
        permission: 'analytics.workforce.view',
    },
    {
        id: 'executive.costs',
        label: 'Total Workforce Cost',
        icon: 'fa-solid fa-coins',
        route: '/executive/costs',
        permission: 'analytics.compensation.view',
    },
    {
        id: 'executive.productivity',
        label: 'Productivity & Capacity',
        icon: 'fa-solid fa-bolt',
        route: '/workforce-productivity',
        permission: 'workforce.productivity.view',
    },
    {
        id: 'executive.planning',
        label: 'Strategic Planning',
        icon: 'fa-solid fa-compass-drafting',
        route: '/workforce-planning',
        permission: 'workforce.planning.manage',
    },
    {
        id: 'executive.intelligence',
        label: 'Workforce Intelligence',
        icon: 'fa-solid fa-lightbulb',
        route: '/workforce-intelligence',
        permission: 'workforce.intelligence.view',
    },
];
