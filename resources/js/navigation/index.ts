/**
 * Enterprise Experience Architecture — Navigation Specifications
 * Defines strongly typed navigation hierarchies across all 7 workspaces.
 */

export interface NavigationItem {
    id: string;
    label: string;
    icon?: string;
    route?: string;
    permission?: string;
    feature?: string;
    entitlement?: string;
    badge?: string;
    children?: NavigationItem[];
}

export type WorkspaceId = 
    | 'platform'
    | 'tenant_admin'
    | 'hr'
    | 'manager'
    | 'employee'
    | 'executive'
    | 'operations';

export * from './platform';
export * from './tenant';
export * from './hr';
export * from './manager';
export * from './employee';
export * from './executive';
export * from './operational';
