import { NavigationItem } from './index';

export const platformNavigation: NavigationItem[] = [
    {
        id: 'platform.control-center',
        label: 'Control Center',
        icon: 'fa-solid fa-gauge-high',
        route: '/platform/control-center',
        permission: 'platform.admin',
    },
    {
        id: 'platform.tenants',
        label: 'Tenants & Provisioning',
        icon: 'fa-solid fa-building',
        route: '/platform/tenants',
        permission: 'platform.tenants.manage',
    },
    {
        id: 'platform.billing',
        label: 'Products & Subscriptions',
        icon: 'fa-solid fa-box-archive',
        route: '/platform/billing',
        permission: 'platform.billing.manage',
    },
    {
        id: 'platform.integrations',
        label: 'Integrations Hub',
        icon: 'fa-solid fa-plug',
        route: '/platform/integrations',
        permission: 'platform.integrations.manage',
    },
    {
        id: 'platform.operations',
        label: 'System Health & Queues',
        icon: 'fa-solid fa-server',
        route: '/operations/system-health',
        permission: 'platform.operations.view',
    },
    {
        id: 'platform.security',
        label: 'Security & Audit',
        icon: 'fa-solid fa-shield-halved',
        route: '/platform/security',
        permission: 'platform.audit.view',
    },
    {
        id: 'platform.ai-governance',
        label: 'AI Governance',
        icon: 'fa-solid fa-brain',
        route: '/platform/ai-governance',
        permission: 'platform.ai.manage',
    },
    {
        id: 'platform.settings',
        label: 'Platform Settings',
        icon: 'fa-solid fa-sliders',
        route: '/platform/settings',
        permission: 'platform.settings.manage',
    },
];
