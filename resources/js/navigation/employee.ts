import { NavigationItem } from './index';

export const employeeNavigation: NavigationItem[] = [
    {
        id: 'employee.home',
        label: 'My Home',
        icon: 'fa-solid fa-house',
        route: '/employee/home',
    },
    {
        id: 'employee.work',
        label: 'My Work',
        icon: 'fa-solid fa-briefcase',
        route: '/portal/work',
    },
    {
        id: 'employee.requests',
        label: 'My Requests',
        icon: 'fa-solid fa-code-pull-request',
        route: '/portal/requests',
    },
    {
        id: 'employee.profile',
        label: 'My Profile',
        icon: 'fa-solid fa-id-badge',
        route: '/portal/profile',
    },
    {
        id: 'employee.pay',
        label: 'My Pay',
        icon: 'fa-solid fa-file-invoice-dollar',
        route: '/portal/pay',
    },
    {
        id: 'employee.growth',
        label: 'My Growth',
        icon: 'fa-solid fa-arrow-trend-up',
        route: '/portal/growth',
    },
    {
        id: 'employee.documents',
        label: 'My Documents',
        icon: 'fa-solid fa-folder-open',
        route: '/portal/documents',
    },
    {
        id: 'employee.services',
        label: 'HR Services',
        icon: 'fa-solid fa-headset',
        route: '/portal/services',
    },
    {
        id: 'employee.directory',
        label: 'People',
        icon: 'fa-solid fa-address-book',
        route: '/portal/directory',
    },
];
