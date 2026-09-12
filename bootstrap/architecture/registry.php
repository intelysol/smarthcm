<?php

declare(strict_types=1);

/** Source-of-truth inventory for Flow bounded contexts. */
return [
    'apps' => ['platform', 'hcm', 'crm', 'erp', 'finance', 'procurement', 'inventory', 'manufacturing', 'healthcare', 'education', 'government'],
    'packages' => ['platform-core', 'identity', 'tenancy', 'organization', 'metadata', 'workflow', 'rules', 'notifications', 'documents', 'search', 'integrations', 'automation', 'analytics', 'ai', 'reporting', 'scheduler', 'marketplace', 'operations', 'sdk'],
    'modules' => ['employee', 'attendance', 'leave', 'payroll', 'recruitment', 'onboarding', 'performance', 'learning', 'expenses', 'assets', 'helpdesk', 'separation'],
    'layers' => ['Application', 'Domain', 'Infrastructure', 'Presentation', 'Routes', 'Config', 'Database', 'Resources', 'Tests'],
];
