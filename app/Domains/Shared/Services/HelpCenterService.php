<?php

declare(strict_types=1);

namespace App\Domains\Shared\Services;

class HelpCenterService
{
    /**
     * Categories list.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getCategories(): array
    {
        return [
            'getting-started' => [
                'key' => 'getting-started',
                'title' => 'Getting Started & Setup',
                'description' => 'First-time installation, environment configuration, and demo account access.',
                'icon' => 'fa-solid fa-rocket',
                'color' => 'indigo',
            ],
            'super-admin' => [
                'key' => 'super-admin',
                'title' => 'Platform Super Admin Guide',
                'description' => 'Global SaaS control plane, tenant provisioning, system health, and security.',
                'icon' => 'fa-solid fa-server',
                'color' => 'amber',
            ],
            'tenant-admin' => [
                'key' => 'tenant-admin',
                'title' => 'Tenant Administrator Guide',
                'description' => 'Company settings, department hierarchy, user administration, and roles.',
                'icon' => 'fa-solid fa-building-user',
                'color' => 'blue',
            ],
            'hr-admin' => [
                'key' => 'hr-admin',
                'title' => 'HR Administrator Guide',
                'description' => 'Employee master records, time & attendance policies, leaves, and documents.',
                'icon' => 'fa-solid fa-users-gear',
                'color' => 'emerald',
            ],
            'manager' => [
                'key' => 'manager',
                'title' => 'People Manager Guide',
                'description' => 'Team overview, pending approvals, team attendance monitoring, and performance.',
                'icon' => 'fa-solid fa-user-group',
                'color' => 'cyan',
            ],
            'employee' => [
                'key' => 'employee',
                'title' => 'Employee Self-Service Guide',
                'description' => 'Personal profile, punch clock, leave requests, payslips, and data privacy.',
                'icon' => 'fa-solid fa-user-tie',
                'color' => 'purple',
            ],
            'troubleshooting' => [
                'key' => 'troubleshooting',
                'title' => 'Troubleshooting & Diagnostics',
                'description' => 'Common issues, migration steps, queue monitoring, and error remediation.',
                'icon' => 'fa-solid fa-wrench',
                'color' => 'rose',
            ],
            'faq' => [
                'key' => 'faq',
                'title' => 'Frequently Asked Questions',
                'description' => 'Frequently asked questions regarding roles, tenant boundaries, and access.',
                'icon' => 'fa-solid fa-circle-question',
                'color' => 'teal',
            ],
        ];
    }

    /**
     * All knowledge base articles.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllArticles(): array
    {
        return [
            // Getting Started
            [
                'slug' => 'quick-start-five-minute-guide',
                'category' => 'getting-started',
                'title' => '5-Minute Quick Start Guide',
                'summary' => 'Quickly spin up the environment, log in with demo personas, and test end-to-end workflows.',
                'role' => 'all',
                'read_time' => '4 min',
                'content' => <<<HTML
<h3>Overview</h3>
<p>The Flow Enterprise Platform (Smart HCM) is designed for immediate testing with 5 pre-configured demo personas:</p>
<ul>
    <li><strong>Platform Super Admin</strong>: <code>superadmin@example.test</code></li>
    <li><strong>Tenant Admin</strong>: <code>admin@example.test</code></li>
    <li><strong>HR Administrator</strong>: <code>hr@example.test</code></li>
    <li><strong>People Manager</strong>: <code>manager@example.test</code></li>
    <li><strong>Employee</strong>: <code>employee@example.test</code></li>
</ul>
<p>All demo accounts share the password defined in your local <code>.env</code> file (default: <code>Demo1234!@#$</code>).</p>

<h3>Step-by-Step Walkthrough</h3>
<ol class="list-decimal pl-5 space-y-2">
    <li><strong>Launch the Application</strong>: Ensure your local server is running (e.g. via Laragon, Valet, or <code>php artisan serve</code>).</li>
    <li><strong>Open Login Screen</strong>: Navigate to <code>/login</code> in your web browser.</li>
    <li><strong>Super Admin Access</strong>: Click the <em>Platform Super Admin</em> pill or type <code>superadmin@example.test</code>. You will be automatically routed to the <strong>Platform Control Center</strong> (<code>/platform</code>).</li>
    <li><strong>Tenant Admin Access</strong>: Sign out and log in as <code>admin@example.test</code>. You will land on the <strong>Organization Overview</strong> (<code>/admin/dashboard</code>).</li>
    <li><strong>Employee & Manager Flow</strong>: Log in as <code>employee@example.test</code>, view attendance, then submit a sample request. Sign in as <code>manager@example.test</code> to review and approve the request.</li>
</ol>
HTML
            ],
            [
                'slug' => 'demo-accounts-and-credentials',
                'category' => 'getting-started',
                'title' => 'Demo Accounts & Environment Credentials',
                'summary' => 'Authoritative directory of development personas, authorization scopes, and credential safeguards.',
                'role' => 'all',
                'read_time' => '3 min',
                'content' => <<<HTML
<h3>Demo Personas Matrix</h3>
<div class="overflow-x-auto my-4">
    <table class="w-full text-xs border border-slate-200">
        <thead class="bg-slate-100 font-bold">
            <tr>
                <th class="p-2 border">Role</th>
                <th class="p-2 border">Email</th>
                <th class="p-2 border">Default Destination</th>
                <th class="p-2 border">Authorized Scope</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="p-2 border font-bold">Platform Super Admin</td>
                <td class="p-2 border font-mono">superadmin@example.test</td>
                <td class="p-2 border font-mono">/platform</td>
                <td class="p-2 border">Global platform governance & tenants</td>
            </tr>
            <tr>
                <td class="p-2 border font-bold">Tenant Admin</td>
                <td class="p-2 border font-mono">admin@example.test</td>
                <td class="p-2 border font-mono">/admin/dashboard</td>
                <td class="p-2 border">Demo Organization setup & users</td>
            </tr>
            <tr>
                <td class="p-2 border font-bold">HR Administrator</td>
                <td class="p-2 border font-mono">hr@example.test</td>
                <td class="p-2 border font-mono">/hr/dashboard</td>
                <td class="p-2 border">HCM, employee master records & policies</td>
            </tr>
            <tr>
                <td class="p-2 border font-bold">People Manager</td>
                <td class="p-2 border font-mono">manager@example.test</td>
                <td class="p-2 border font-mono">/manager/workbench</td>
                <td class="p-2 border">Team roster, leaves, and approvals</td>
            </tr>
            <tr>
                <td class="p-2 border font-bold">Employee</td>
                <td class="p-2 border font-mono">employee@example.test</td>
                <td class="p-2 border font-mono">/portal</td>
                <td class="p-2 border">Personal self-service, punch, profile</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-xs">
    <strong>Security Notice:</strong> Demo accounts are strictly for local/development and demonstration environments. Production deployments automatically reject demo credential seeding unless explicitly overridden with <code>SEED_DEMO_USERS=true</code>.
</div>
HTML
            ],

            // Super Admin
            [
                'slug' => 'platform-super-admin-guide',
                'category' => 'super-admin',
                'title' => 'Platform Super Admin Governance Guide',
                'summary' => 'Comprehensive manual for managing SaaS tenants, global security, system observability, and platform telemetry.',
                'role' => 'super_admin',
                'read_time' => '6 min',
                'content' => <<<HTML
<h3>Role Responsibilities</h3>
<p>The <strong>Platform Super Admin</strong> operates above individual tenants. Key capabilities include:</p>
<ul>
    <li><strong>Tenant Provisioning</strong>: Create, activate, suspend, and configure multi-tenant corporate accounts under <code>/platform/tenants</code>.</li>
    <li><strong>Global Users Directory</strong>: Review cross-tenant user accounts and suspend compromised credentials under <code>/platform/users</code>.</li>
    <li><strong>System Operations</strong>: Monitor health checks, Horizon queue worker metrics, and background jobs under <code>/operations</code>.</li>
    <li><strong>Disaster Recovery & Lifecycle</strong>: Inspect cryptographic backups, recovery objectives (RTO/RPO), and retention policies under <code>/operations/recovery</code> and <code>/operations/data-lifecycle</code>.</li>
    <li><strong>Compliance Control Plane</strong>: Verify ISO 27001, SOC 2, and GDPR controls across the platform under <code>/operations/compliance</code>.</li>
</ul>

<h3>Tenant Separation Boundary</h3>
<p>Super Admins manage the platform infrastructure, but tenant application contexts remain strictly isolated. Each database query in the tenant portal enforces tenant scoping.</p>
HTML
            ],

            // Tenant Admin
            [
                'slug' => 'tenant-administrator-guide',
                'category' => 'tenant-admin',
                'title' => 'Tenant Administrator Operations Guide',
                'summary' => 'Setting up organizational departments, user credentials, role assignments, and compliance policies.',
                'role' => 'tenant_admin',
                'read_time' => '5 min',
                'content' => <<<HTML
<h3>Key Administration Workflows</h3>
<ol class="list-decimal pl-5 space-y-2">
    <li><strong>Organization Hierarchy</strong>: Navigate to <code>/admin/departments</code> and <code>/admin/positions</code> to establish reporting structures.</li>
    <li><strong>User Administration</strong>: Open <code>/admin/users</code> to create team accounts, toggle account status (Active/Disabled), and reset user passwords.</li>
    <li><strong>Compliance & Privacy</strong>: Access <code>/admin/compliance-governance</code> to review Records of Processing Activities (ROPA GDPR Art. 30) and manage Data Subject Rights (DSAR).</li>
    <li><strong>Data Lifecycle Management</strong>: Open <code>/admin/data-lifecycle</code> to review tenant retention policies and statutory platform floor guarantees.</li>
</ol>
HTML
            ],

            // HR Admin
            [
                'slug' => 'hr-administrator-guide',
                'category' => 'hr-admin',
                'title' => 'HR Administrator Operations Manual',
                'summary' => 'Managing employee master records, attendance punches, leave balances, and company documents.',
                'role' => 'hr_admin',
                'read_time' => '5 min',
                'content' => <<<HTML
<h3>HR Command Center (`/hr/dashboard`)</h3>
<p>The HR Command Center provides real-time headcount indicators, pending requests, and attendance exceptions.</p>

<h3>Core Workflows</h3>
<ul>
    <li><strong>Employee Management</strong>: Maintain active employee records, designations, and department affiliations.</li>
    <li><strong>Attendance Monitoring</strong>: Review biometric device status and daily punch activity.</li>
    <li><strong>Leave Governance</strong>: Oversee company leave categories (Annual, Sick, Casual) and policy adjustments.</li>
    <li><strong>Document Templates</strong>: Manage corporate document templates and contracts.</li>
</ul>
HTML
            ],

            // Manager
            [
                'slug' => 'people-manager-workbench-guide',
                'category' => 'manager',
                'title' => 'People Manager Workbench Guide',
                'summary' => 'Monitoring direct reports, reviewing team attendance, and approving employee leave requests.',
                'role' => 'manager',
                'read_time' => '4 min',
                'content' => <<<HTML
<h3>Manager Responsibilities</h3>
<p>People Managers have scoped visibility restricted to their direct and indirect team reports.</p>

<h3>Handling Team Approvals</h3>
<ol class="list-decimal pl-5 space-y-2">
    <li>Open <strong>Manager Workbench</strong> (<code>/manager/workbench</code> or <code>/portal/manager/workbench</code>).</li>
    <li>Inspect the <strong>Pending Approvals</strong> badge.</li>
    <li>Click into any pending request to view request details, employee balance, and submission date.</li>
    <li>Click <strong>Approve</strong> or <strong>Reject</strong> with business justification.</li>
</ol>
HTML
            ],

            // Employee
            [
                'slug' => 'employee-self-service-guide',
                'category' => 'employee',
                'title' => 'Employee Self-Service (ESS) User Guide',
                'summary' => 'Clocking attendance, submitting leave requests, downloading payslips, and exercising privacy rights.',
                'role' => 'employee',
                'read_time' => '4 min',
                'content' => <<<HTML
<h3>Welcome to Your Digital Workplace</h3>
<p>The Employee Portal (<code>/portal</code>) provides single-click access to all your employment services:</p>

<ul>
    <li><strong>My Work (<code>/portal/work</code>)</strong>: Record today's biometric attendance punch and review shift schedules.</li>
    <li><strong>My Requests (<code>/portal/requests</code>)</strong>: Submit time-off, expense reimbursements, and service requests.</li>
    <li><strong>My Pay (<code>/portal/pay</code>)</strong>: View and download itemized monthly salary slips and tax summaries.</li>
    <li><strong>My Privacy (<code>/portal/privacy</code>)</strong>: Request a cryptographic machine-readable export of your personal data (DSAR) or submit data rectification requests.</li>
</ul>
HTML
            ],

            // Troubleshooting
            [
                'slug' => 'troubleshooting-common-platform-issues',
                'category' => 'troubleshooting',
                'title' => 'Troubleshooting Common Issues',
                'summary' => 'Resolution steps for authentication failures, migration discrepancies, database connection errors, and queues.',
                'role' => 'all',
                'read_time' => '5 min',
                'content' => <<<HTML
<h3>1. Cannot Sign In / Invalid Credentials</h3>
<ul>
    <li><strong>Symptom</strong>: Error "These credentials do not match our records."</li>
    <li><strong>Check</strong>: Verify that the demo seeder was executed via <code>php artisan app:setup-demo</code>.</li>
    <li><strong>Solution</strong>: Ensure password matches <code>DEMO_USER_PASSWORD</code> in <code>.env</code> (default: <code>Demo1234!@#$</code>).</li>
</ul>

<h3>2. 403 Forbidden / Unauthorized Workspace</h3>
<ul>
    <li><strong>Symptom</strong>: "Access Restricted: You are not authorized to access this workspace."</li>
    <li><strong>Cause</strong>: Attempting to access administrative routes (e.g. <code>/admin</code> or <code>/platform</code>) as an Employee or Manager without appropriate roles.</li>
    <li><strong>Solution</strong>: Log in with an authorized persona (e.g. <code>superadmin@example.test</code> or <code>admin@example.test</code>).</li>
</ul>

<h3>3. Database Migration or Seeding Failures</h3>
<ul>
    <li><strong>Check</strong>: Ensure SQLite or MySQL database connection is reachable.</li>
    <li><strong>Solution</strong>: Run <code>php artisan migrate --force</code> followed by <code>php artisan app:setup-demo</code>.</li>
</ul>
HTML
            ],

            // FAQ
            [
                'slug' => 'frequently-asked-questions',
                'category' => 'faq',
                'title' => 'Frequently Asked Questions (FAQ)',
                'summary' => 'Answers to common questions regarding multi-tenancy, data protection, and platform scalability.',
                'role' => 'all',
                'read_time' => '3 min',
                'content' => <<<HTML
<h3>Frequently Asked Questions</h3>

<h4>How are tenants isolated?</h4>
<p>Every tenant record is isolated at the database level with strict foreign key constraints and automatic query scoping. Tenant administrators cannot view or mutate records belonging to another tenant.</p>

<h4>Where are background jobs and queues monitored?</h4>
<p>Platform Super Admins can monitor background queue throughput, failed jobs, and latency under <code>/operations/queues</code>.</p>

<h4>Can demo users be reset without dropping the entire database?</h4>
<p>Yes. Run <code>php artisan app:reset-demo --force</code> to safely purge and re-seed only the demo organization and demo accounts.</p>
HTML
            ],
        ];
    }

    /**
     * Get articles by category.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getArticlesByCategory(string $category): array
    {
        return array_values(array_filter(
            $this->getAllArticles(),
            fn (array $article) => $article['category'] === $category
        ));
    }

    /**
     * Find single article by slug.
     *
     * @return array<string, mixed>|null
     */
    public function getArticleBySlug(string $slug): ?array
    {
        foreach ($this->getAllArticles() as $article) {
            if ($article['slug'] === $slug) {
                return $article;
            }
        }

        return null;
    }

    /**
     * Search articles by query string.
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query): array
    {
        $q = strtolower(trim($query));
        if ($q === '') {
            return $this->getAllArticles();
        }

        return array_values(array_filter(
            $this->getAllArticles(),
            fn (array $article) => str_contains(strtolower($article['title']), $q)
                || str_contains(strtolower($article['summary']), $q)
                || str_contains(strtolower($article['content']), $q)
        ));
    }
}
