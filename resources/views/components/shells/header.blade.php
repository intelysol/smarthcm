@props([
    'currentWorkspace',
    'allowedWorkspaces' => [],
    'title' => null,
])

@php
    use App\Domains\Shared\Enums\WorkspaceType;
    $current = $currentWorkspace instanceof WorkspaceType ? $currentWorkspace : WorkspaceType::tryFrom($currentWorkspace ?? 'employee');
    $user = auth()->user();
    $tenant = $user?->tenant;
@endphp

<header class="border-b sticky top-0 z-40 shadow-sm" style="background-color: {{ $current?->themeColor() ?? '#1E3A5F' }}; border-color: rgba(255, 255, 255, 0.1);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- Left: Branding & Workspace Switcher -->
        <div class="flex items-center space-x-4">
            <a href="{{ route($current?->dashboardRoute() ?? 'portal.dashboard') }}" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-black/20 border border-[#C9A227]/40 flex items-center justify-center text-[#C9A227] font-extrabold shadow group-hover:border-[#C9A227] transition">
                    <i class="fa-solid fa-layer-group text-lg"></i>
                </div>
                <div>
                    <span class="text-base font-black tracking-wide text-white">SmartHCM</span>
                    <span class="block text-[10px] text-[#C9A227] font-mono tracking-wider uppercase font-bold">Enterprise OS</span>
                </div>
            </a>

            <div class="h-6 w-px bg-white/20 hidden md:block"></div>

            <!-- Workspace Switcher Component -->
            @include('components.shells.workspace-switcher', [
                'currentWorkspace' => $current,
                'allowedWorkspaces' => $allowedWorkspaces
            ])

            <!-- Tenant Context Indicator (For Tenant-bound workspaces) -->
            @if($current !== WorkspaceType::PLATFORM_ADMIN && $tenant)
                <div class="hidden lg:flex items-center space-x-2 px-2.5 py-1 rounded-md bg-white/5 border border-white/10 text-white/80 text-xs">
                    <i class="fa-solid fa-building text-[11px] text-[#C9A227]"></i>
                    <span class="font-medium truncate max-w-[140px]">{{ $tenant->name }}</span>
                </div>
            @endif
        </div>

        <!-- Center: Contextual Global Search -->
        <div class="hidden md:flex flex-1 max-w-md mx-6">
            <div class="relative w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-white/50">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                @php
                    $searchPlaceholder = match($current) {
                        WorkspaceType::PLATFORM_ADMIN => 'Search tenants, users, subscriptions, audit logs... (Ctrl+K)',
                        WorkspaceType::TENANT_ADMIN => 'Search departments, positions, users, policies... (Ctrl+K)',
                        WorkspaceType::HR_ADMIN => 'Search employees, requisitions, payroll, claims... (Ctrl+K)',
                        WorkspaceType::MANAGER => 'Search team members, approvals, schedules... (Ctrl+K)',
                        WorkspaceType::EMPLOYEE => 'Search requests, payslips, policies, people... (Ctrl+K)',
                        WorkspaceType::EXECUTIVE => 'Search workforce KPIs, cost centers, forecasts... (Ctrl+K)',
                        WorkspaceType::OPERATIONS => 'Search queues, logs, errors, telemetry... (Ctrl+K)',
                        default => 'Search anything... (Ctrl+K)',
                    };
                @endphp
                <input type="text" id="global-search" placeholder="{{ $searchPlaceholder }}" 
                    @click="$dispatch('open-command-palette')"
                    readonly
                    class="w-full pl-9 pr-10 py-1.5 bg-black/25 border border-white/15 rounded-lg text-xs text-white placeholder-white/50 cursor-pointer focus:outline-none focus:border-[#C9A227] transition">
                <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none">
                    <kbd class="px-1.5 py-0.5 text-[9px] bg-white/10 text-white/70 rounded border border-white/10 font-mono">⌘K</kbd>
                </span>
            </div>
        </div>

        <!-- Right: Actions & User Dropdown -->
        <div class="flex items-center space-x-3">
            
            <!-- AI Copilot / Concierge Button -->
            @php
                $aiLabel = match($current) {
                    WorkspaceType::EMPLOYEE => 'AI Concierge',
                    WorkspaceType::MANAGER => 'Manager Copilot',
                    WorkspaceType::HR_ADMIN => 'HR Copilot',
                    WorkspaceType::EXECUTIVE => 'Workforce Intelligence AI',
                    default => 'AI Assistant',
                };
            @endphp
            <button onclick="window.toggleAiDrawer ? toggleAiDrawer() : alert('AI Assistant active')" 
                class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-[#C9A227]/20 text-[#F4E7B2] border border-[#C9A227]/50 hover:bg-[#C9A227]/30 text-xs font-semibold transition">
                <i class="fa-solid fa-wand-magic-sparkles text-[#C9A227]"></i>
                <span class="hidden sm:inline">{{ $aiLabel }}</span>
            </button>

            <!-- Notifications Bell -->
            <button @click="$dispatch('open-notifications')" aria-label="Open notifications" class="relative p-2 rounded-lg bg-black/20 text-white/80 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-[#C9A227]">
                <i class="fa-regular fa-bell text-sm" aria-hidden="true"></i>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-[#C9A227] rounded-full"></span>
            </button>

            <!-- User Menu Dropdown -->
            <div class="relative" x-data="{ userMenu: false }">
                <button @click="userMenu = !userMenu" type="button" class="flex items-center pl-2 border-l border-white/20 space-x-2 text-left focus:outline-none group">
                    <div class="w-8 h-8 rounded-full bg-black/30 border border-[#C9A227]/50 flex items-center justify-center text-xs font-bold text-white overflow-hidden group-hover:border-[#C9A227] transition">
                        {{ substr($user->name ?? 'User', 0, 1) }}
                    </div>
                    <div class="hidden lg:block text-left text-xs">
                        <div class="font-semibold text-white leading-tight flex items-center">
                            {{ $user->name ?? 'Enterprise Staff' }}
                            <i class="fa-solid fa-chevron-down text-[10px] text-white/60 ml-1.5"></i>
                        </div>
                        <div class="text-[10px] text-white/60 truncate max-w-[130px]">{{ $current?->label() }}</div>
                    </div>
                </button>

                <!-- User Dropdown Menu -->
                <div x-show="userMenu" @click.away="userMenu = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-slate-200 z-50 text-slate-800 py-1 text-xs"
                    style="display: none;">
                    
                    <div class="px-4 py-2.5 border-b border-slate-100">
                        <p class="font-bold text-slate-900">{{ $user->name ?? 'Enterprise User' }}</p>
                        <p class="text-slate-500 text-[11px] truncate">{{ $user->email ?? '' }}</p>
                    </div>

                    <!-- Role-appropriate user menu items -->
                    @if($current === WorkspaceType::EMPLOYEE)
                        <a href="{{ route('portal.profile') }}" class="flex items-center px-4 py-2 hover:bg-slate-50 text-slate-700">
                            <i class="fa-solid fa-id-badge w-5 text-slate-400"></i> My Profile
                        </a>
                        <a href="{{ route('portal.requests') }}" class="flex items-center px-4 py-2 hover:bg-slate-50 text-slate-700">
                            <i class="fa-solid fa-clipboard-list w-5 text-slate-400"></i> My Requests
                        </a>
                    @elseif($current === WorkspaceType::TENANT_ADMIN)
                        <a href="{{ route('admin.settings') }}" class="flex items-center px-4 py-2 hover:bg-slate-50 text-slate-700">
                            <i class="fa-solid fa-gears w-5 text-slate-400"></i> Organization Settings
                        </a>
                    @elseif($current === WorkspaceType::PLATFORM_ADMIN)
                        <a href="{{ route('platform.settings') }}" class="flex items-center px-4 py-2 hover:bg-slate-50 text-slate-700">
                            <i class="fa-solid fa-sliders w-5 text-slate-400"></i> Platform Settings
                        </a>
                    @endif

                    <div class="border-t border-slate-100 mt-1 pt-1">
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-rose-600 hover:bg-rose-50 font-semibold transition">
                                <i class="fa-solid fa-arrow-right-from-bracket w-5 text-rose-500"></i> Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Workspace Sub-Navigation Bar -->
    @if(isset($navigation) && is_array($navigation))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-white/10 overflow-x-auto">
            <nav class="flex space-x-1 py-1.5 text-xs font-medium text-white/70 whitespace-nowrap">
                @foreach($navigation as $item)
                    @php
                        $isActive = isset($item['route']) && Route::has($item['route']) && request()->routeIs($item['route']);
                        $href = isset($item['route']) && Route::has($item['route']) ? route($item['route']) : '#';
                    @endphp
                    <a href="{{ $href }}" 
                        class="px-3 py-1.5 rounded-md hover:text-white hover:bg-white/10 transition flex items-center {{ $isActive ? 'text-white bg-white/15 font-semibold border-b-2 border-[#C9A227]' : '' }}">
                        @if(!empty($item['icon']))
                            <i class="{{ $item['icon'] }} mr-1.5 text-[11px]"></i>
                        @endif
                        <span>{{ $item['label'] }}</span>
                        @if(!empty($item['badge']))
                            <span class="ml-1.5 px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-[#C9A227] text-[#142A44]">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </div>
    @endif
</header>
