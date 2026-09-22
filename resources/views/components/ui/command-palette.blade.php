@props([
    'currentWorkspace' => 'employee',
])

@php
    use App\Domains\Shared\Enums\WorkspaceType;
    $ws = $currentWorkspace instanceof WorkspaceType ? $currentWorkspace : WorkspaceType::tryFrom($currentWorkspace ?? 'employee');

    // Build role/workspace authorized commands
    $commands = [];

    $safeRoute = fn(string $name, string $fallback = '#') => \Illuminate\Support\Facades\Route::has($name) ? route($name) : $fallback;

    // General navigation
    $commands[] = ['title' => 'My Dashboard', 'category' => 'Navigation', 'icon' => 'fa-solid fa-house', 'url' => $safeRoute($ws?->dashboardRoute() ?? 'portal.dashboard', '/portal')];

    // Role-tailored commands
    if ($ws === WorkspaceType::EMPLOYEE) {
        $commands[] = ['title' => 'My Profile & Documents', 'category' => 'Self-Service', 'icon' => 'fa-solid fa-user', 'url' => $safeRoute('portal.profile')];
        $commands[] = ['title' => 'Request Time Off / Leave', 'category' => 'Requests', 'icon' => 'fa-solid fa-calendar-plus', 'url' => $safeRoute('portal.leave.create', $safeRoute('portal.requests'))];
        $commands[] = ['title' => 'View My Requests & Status', 'category' => 'Requests', 'icon' => 'fa-solid fa-clipboard-list', 'url' => $safeRoute('portal.requests')];
        $commands[] = ['title' => 'Check My Work Schedule', 'category' => 'Schedule', 'icon' => 'fa-solid fa-calendar-days', 'url' => $safeRoute('portal.schedule')];
        $commands[] = ['title' => 'Ask AI Concierge for Help', 'category' => 'Assistance', 'icon' => 'fa-solid fa-wand-magic-sparkles', 'url' => '#ai-concierge'];
    } elseif ($ws === WorkspaceType::MANAGER) {
        $commands[] = ['title' => 'Team Workbench Overview', 'category' => 'Management', 'icon' => 'fa-solid fa-users', 'url' => $safeRoute('manager.workbench')];
        $commands[] = ['title' => 'Direct Reports & Members', 'category' => 'Management', 'icon' => 'fa-solid fa-user-group', 'url' => $safeRoute('manager.members')];
        $commands[] = ['title' => 'Team Performance Reviews', 'category' => 'Reviews', 'icon' => 'fa-solid fa-chart-line', 'url' => $safeRoute('manager.performance')];
        $commands[] = ['title' => 'Team Productivity Analytics', 'category' => 'Analytics', 'icon' => 'fa-solid fa-chart-pie', 'url' => $safeRoute('manager.analytics')];
    } elseif ($ws === WorkspaceType::HR_ADMIN) {
        $commands[] = ['title' => 'HR Operations Dashboard', 'category' => 'Operations', 'icon' => 'fa-solid fa-gauge-high', 'url' => $safeRoute('hr.dashboard')];
        $commands[] = ['title' => 'Employee Directory & Records', 'category' => 'People', 'icon' => 'fa-solid fa-address-book', 'url' => $safeRoute('portal.employees')];
        $commands[] = ['title' => 'Attendance Management', 'category' => 'Attendance', 'icon' => 'fa-solid fa-clock', 'url' => $safeRoute('portal.attendance')];
        $commands[] = ['title' => 'Payroll & Compensation Runs', 'category' => 'Payroll', 'icon' => 'fa-solid fa-money-check-dollar', 'url' => $safeRoute('portal.payroll')];
    } elseif ($ws === WorkspaceType::TENANT_ADMIN) {
        $commands[] = ['title' => 'Organization Settings', 'category' => 'Settings', 'icon' => 'fa-solid fa-gears', 'url' => $safeRoute('admin.settings')];
        $commands[] = ['title' => 'User & Account Management', 'category' => 'Security', 'icon' => 'fa-solid fa-users-gear', 'url' => $safeRoute('admin.users')];
        $commands[] = ['title' => 'Role & Permission Configuration', 'category' => 'Security', 'icon' => 'fa-solid fa-shield-halved', 'url' => $safeRoute('admin.roles')];
        $commands[] = ['title' => 'Workflow Automation Rules', 'category' => 'Automation', 'icon' => 'fa-solid fa-network-wired', 'url' => $safeRoute('admin.workflows')];
    } elseif ($ws === WorkspaceType::PLATFORM_ADMIN) {
        $commands[] = ['title' => 'Control Center Dashboard', 'category' => 'SaaS Admin', 'icon' => 'fa-solid fa-sliders', 'url' => $safeRoute('platform.control-center')];
        $commands[] = ['title' => 'Tenant Directory & Multi-Tenancy', 'category' => 'Tenants', 'icon' => 'fa-solid fa-building', 'url' => $safeRoute('platform.tenants')];
        $commands[] = ['title' => 'Billing & SaaS Subscriptions', 'category' => 'Billing', 'icon' => 'fa-solid fa-credit-card', 'url' => $safeRoute('platform.billing')];
        $commands[] = ['title' => 'Platform Security Posture', 'category' => 'Security', 'icon' => 'fa-solid fa-lock', 'url' => $safeRoute('platform.security')];
        $commands[] = ['title' => 'AI Governance & Safety Audits', 'category' => 'AI', 'icon' => 'fa-solid fa-robot', 'url' => $safeRoute('platform.ai-governance')];
    } elseif ($ws === WorkspaceType::EXECUTIVE) {
        $commands[] = ['title' => 'Workforce Intelligence Overview', 'category' => 'Executive', 'icon' => 'fa-solid fa-chart-line', 'url' => $safeRoute('executive.overview')];
        $commands[] = ['title' => 'Workforce Cost & Headcount Forecast', 'category' => 'Finance', 'icon' => 'fa-solid fa-coins', 'url' => $safeRoute('executive.costs')];
    } elseif ($ws === WorkspaceType::OPERATIONS) {
        $commands[] = ['title' => 'Queue Monitors & Workers', 'category' => 'Operations', 'icon' => 'fa-solid fa-layer-group', 'url' => $safeRoute('operations.queues')];
        $commands[] = ['title' => 'System & Audit Telemetry Logs', 'category' => 'Operations', 'icon' => 'fa-solid fa-file-waveform', 'url' => $safeRoute('operations.logs')];
    }
@endphp

<div x-data="{
    open: false,
    query: '',
    items: {{ json_encode($commands) }},
    get filteredItems() {
        if (!this.query.trim()) return this.items;
        const q = this.query.toLowerCase();
        return this.items.filter(i => i.title.toLowerCase().includes(q) || i.category.toLowerCase().includes(q));
    },
    navigate(url) {
        this.open = false;
        window.location.href = url;
    }
}"
     x-on:keydown.window.prevent.cmd.k="open = true; $nextTick(() => $refs.commandInput.focus())"
     x-on:keydown.window.prevent.ctrl.k="open = true; $nextTick(() => $refs.commandInput.focus())"
     x-on:open-command-palette.window="open = true; $nextTick(() => $refs.commandInput.focus())"
     x-on:keydown.escape.window="open = false"
     class="relative z-50">

    <!-- Modal Backdrop -->
    <div x-show="open" 
         x-transition:enter="ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
         aria-hidden="true"
         style="display: none;"></div>

    <!-- Palette Dialog -->
    <div x-show="open" 
         class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-start justify-center pt-24"
         role="dialog"
         aria-modal="true"
         aria-label="Command Palette"
         style="display: none;">

        <div @click.away="open = false"
             x-transition:enter="ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">

            <!-- Search Input Header -->
            <div class="relative border-b border-slate-200">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400 text-sm" aria-hidden="true"></i>
                <input type="text"
                       x-ref="commandInput"
                       x-model="query"
                       placeholder="Type a command or search actions... (Esc to exit)"
                       class="w-full pl-11 pr-12 py-3.5 text-sm bg-transparent text-slate-900 placeholder-slate-400 focus:outline-none">
                <kbd class="absolute right-3.5 top-3 px-2 py-0.5 text-[10px] font-mono text-slate-400 bg-slate-100 rounded border border-slate-200">ESC</kbd>
            </div>

            <!-- Command List -->
            <div class="max-h-80 overflow-y-auto py-2 divide-y divide-slate-50">
                <template x-if="filteredItems.length === 0">
                    <div class="p-8 text-center text-xs text-slate-400">
                        No commands match "<span x-text="query"></span>"
                    </div>
                </template>

                <template x-for="(item, idx) in filteredItems" :key="idx">
                    <button type="button"
                            @click="navigate(item.url)"
                            class="w-full px-4 py-2.5 flex items-center justify-between text-left hover:bg-slate-50 transition group focus:bg-slate-100 focus:outline-none">
                        <div class="flex items-center space-x-3">
                            <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center group-hover:bg-[#1E3A5F] group-hover:text-white transition">
                                <i :class="item.icon" class="text-xs" aria-hidden="true"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-800 group-hover:text-[#1E3A5F]" x-text="item.title"></span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium px-2 py-0.5 rounded bg-slate-100" x-text="item.category"></span>
                    </button>
                </template>
            </div>

            <!-- Footer Keyboard Hints -->
            <div class="px-4 py-2 bg-slate-50 border-t border-slate-100 text-[10px] text-slate-400 flex items-center justify-between">
                <span>Navigation shortcut: <kbd class="font-mono bg-white px-1 py-0.5 rounded border border-slate-200">⌘K</kbd> / <kbd class="font-mono bg-white px-1 py-0.5 rounded border border-slate-200">Ctrl+K</kbd></span>
                <span>Role: <strong class="text-slate-600">{{ $ws?->label() ?? 'Enterprise User' }}</strong></span>
            </div>
        </div>
    </div>
</div>
