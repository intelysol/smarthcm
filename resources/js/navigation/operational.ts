import { NavigationItem } from './index';

export const operationalNavigation: NavigationItem[] = [
    {
        id: 'operations.health',
        label: 'System Health & Metrics',
        icon: 'fa-solid fa-heart-pulse',
        route: '/operations/system-health',
        permission: 'operations.health.view',
    },
    {
        id: 'operations.queues',
        label: 'Queues & Horizon',
        icon: 'fa-solid fa-bars-staggered',
        route: '/operations/queues',
        permission: 'operations.queues.manage',
    },
    {
        id: 'operations.billing',
        label: 'Commercial Billing & Invoices',
        icon: 'fa-solid fa-file-invoice',
        route: '/portal/billing',
        permission: 'operations.billing.manage',
    },
    {
        id: 'operations.devices',
        label: 'Biometric Devices',
        icon: 'fa-solid fa-fingerprint',
        route: '/hcm/attendance/devices',
        permission: 'attendance.devices.manage',
    },
    {
        id: 'operations.logs',
        label: 'Operational Logs & Telemetry',
        icon: 'fa-solid fa-terminal',
        route: '/operations/logs',
        permission: 'operations.logs.view',
    },
];
