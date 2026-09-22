<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Digital Workplace') — SmartHCM Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'corp-navy': '#1E3A5F',
                        'corp-navy-dark': '#142A44',
                        'corp-gold': '#C9A227',
                        'corp-gold-light': '#F4E7B2',
                        'corp-bg': '#F7F9FC',
                        'corp-surface': '#FFFFFF',
                        'corp-text': '#1F2937',
                        'corp-muted': '#6B7280',
                        'corp-border': '#E5E7EB',
                        'corp-success': '#16805C',
                        'corp-warning': '#B7791F',
                        'corp-danger': '#C0392B',
                        'corp-info': '#2563EB',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="/css/corporate-tokens.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#F7F9FC] text-[#1F2937] min-h-screen flex flex-col font-sans antialiased">
    <!-- Top Bar Navigation (Corporate Navy) -->
    <header class="bg-[#1E3A5F] border-b border-[#142A44] sticky top-0 z-40 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('portal.dashboard') }}" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-[#142A44] border border-[#C9A227]/40 flex items-center justify-center text-[#C9A227] font-extrabold shadow group-hover:border-[#C9A227] transition">
                        <i class="fa-solid fa-layer-group text-lg"></i>
                    </div>
                    <div>
                        <span class="text-base font-black tracking-wide text-white">SmartHCM</span>
                        <span class="block text-[10px] text-[#C9A227] font-mono tracking-wider uppercase font-bold">Enterprise Operating System</span>
                    </div>
                </a>

                @auth
                    @php
                        $workspaceManager = app(\App\Domains\Shared\Services\WorkspaceManager::class);
                        $userWorkspaces = $workspaceManager->resolveAllowedWorkspaces(auth()->user());
                        $activeWorkspace = $workspaceManager->getActiveWorkspace(auth()->user());
                    @endphp
                    @if(count($userWorkspaces) > 1)
                        <div class="h-6 w-px bg-slate-700 hidden md:block"></div>
                        @include('components.shells.workspace-switcher', [
                            'currentWorkspace' => \App\Domains\Shared\Enums\WorkspaceType::EMPLOYEE,
                            'allowedWorkspaces' => $userWorkspaces
                        ])
                    @endif
                @endauth
            </div>

            <!-- Global Search Bar -->
            <div class="hidden md:flex flex-1 max-w-md mx-8">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-300">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" id="global-search" placeholder="Search requests, people, policies, payslips... (Ctrl+K)" 
                        class="w-full pl-9 pr-12 py-1.5 bg-[#142A44]/90 border border-slate-600 rounded-lg text-xs text-white placeholder-slate-400 focus:outline-none focus:border-[#C9A227] transition">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none">
                        <kbd class="px-1.5 py-0.5 text-[10px] bg-[#1E3A5F] text-slate-300 rounded border border-slate-600">⌘K</kbd>
                    </span>
                </div>
            </div>

            <!-- Header Right Actions -->
            <div class="flex items-center space-x-3">
                <!-- Ask HR AI Concierge Button -->
                <button onclick="toggleAiDrawer()" class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-[#C9A227]/20 text-[#F4E7B2] border border-[#C9A227]/50 hover:bg-[#C9A227]/30 text-xs font-semibold transition">
                    <i class="fa-solid fa-wand-magic-sparkles text-[#C9A227]"></i>
                    <span class="hidden sm:inline">Ask AI</span>
                </button>

                <!-- Notifications Button with Dropdown -->
                <div class="relative">
                    <button id="btn-notifications" onclick="toggleNotificationsDropdown()" class="relative p-2 rounded-lg bg-[#142A44] text-slate-200 hover:text-white transition focus:outline-none">
                        <i class="fa-regular fa-bell text-sm"></i>
                        <span id="notif-badge" class="absolute top-1 right-1 w-2 h-2 bg-[#C9A227] rounded-full"></span>
                    </button>

                    <!-- Notifications Dropdown Menu -->
                    <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-slate-200 z-50 text-slate-800 overflow-hidden">
                        <div class="px-4 py-3 bg-[#1E3A5F] text-white flex items-center justify-between">
                            <span class="font-bold text-xs uppercase tracking-wider">Notifications & Tasks</span>
                            <button onclick="markAllNotificationsRead()" class="text-[10px] text-[#F4E7B2] hover:underline">Mark all read</button>
                        </div>
                        <div id="notifications-list" class="max-h-64 overflow-y-auto divide-y divide-slate-100 text-xs">
                            <div class="p-3 hover:bg-slate-50 transition flex items-start space-x-2.5">
                                <span class="w-2 h-2 rounded-full bg-[#16805C] mt-1.5 shrink-0"></span>
                                <div>
                                    <p class="font-semibold text-slate-900">System Operating Normally</p>
                                    <p class="text-slate-500 text-[11px]">All HCM services, integrations and billing subsystems active.</p>
                                </div>
                            </div>
                            <div class="p-3 hover:bg-slate-50 transition flex items-start space-x-2.5">
                                <span class="w-2 h-2 rounded-full bg-[#C9A227] mt-1.5 shrink-0"></span>
                                <div>
                                    <p class="font-semibold text-slate-900">Monthly Payroll Run</p>
                                    <p class="text-slate-500 text-[11px]">Current pay cycle timesheets ready for review.</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-2 bg-slate-50 border-t border-slate-100 text-center">
                            <a href="{{ route('portal.requests') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">View All Requests &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Role / Profile Switcher & User Dropdown -->
                @php
                    $currentPerson = $employee ?? $manager ?? null;
                    $isManagerUser = $isManager ?? (isset($manager) ? true : false);
                @endphp
                <div class="relative">
                    <button onclick="toggleUserDropdown()" class="flex items-center pl-2 border-l border-slate-700 space-x-2 text-left focus:outline-none group">
                        <div class="w-8 h-8 rounded-full bg-[#142A44] border border-[#C9A227]/50 flex items-center justify-center text-xs font-bold text-white overflow-hidden group-hover:border-[#C9A227] transition">
                            @if(!empty($currentPerson?->photo_path))
                                <img src="{{ $currentPerson->photo_path }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                                {{ substr($currentPerson?->first_name ?? 'U', 0, 1) }}
                            @endif
                        </div>
                        <div class="hidden lg:block text-left text-xs">
                            <div class="font-semibold text-white leading-tight flex items-center">
                                {{ $currentPerson?->fullName() ?? 'Employee User' }}
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 ml-1.5"></i>
                            </div>
                            <div class="text-[10px] text-slate-300">{{ $currentPerson?->designation?->name ?? 'Enterprise Staff' }}</div>
                        </div>
                    </button>

                    <!-- User Dropdown Menu -->
                    <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-slate-200 z-50 text-slate-800 py-1 text-xs">
                        <div class="px-4 py-2.5 border-b border-slate-100">
                            <p class="font-bold text-slate-900">{{ $currentPerson?->fullName() ?? 'Enterprise User' }}</p>
                            <p class="text-slate-500 text-[11px] truncate">{{ auth()->user()->email ?? 'user@enterprise.internal' }}</p>
                        </div>
                        <a href="{{ route('portal.profile') }}" class="flex items-center px-4 py-2 hover:bg-slate-50 text-slate-700 hover:text-slate-900 transition">
                            <i class="fa-solid fa-id-badge w-5 text-slate-400"></i> My Information
                        </a>
                        <a href="{{ route('portal.requests') }}" class="flex items-center px-4 py-2 hover:bg-slate-50 text-slate-700 hover:text-slate-900 transition">
                            <i class="fa-solid fa-clipboard-list w-5 text-slate-400"></i> My Requests & Leaves
                        </a>
                        <a href="{{ route('portal.documents') }}" class="flex items-center px-4 py-2 hover:bg-slate-50 text-slate-700 hover:text-slate-900 transition">
                            <i class="fa-solid fa-folder-open w-5 text-slate-400"></i> Documents & Letters
                        </a>
                        @if(!empty($isManagerUser))
                        <a href="{{ route('portal.manager.workbench') }}" class="flex items-center px-4 py-2 hover:bg-slate-50 text-[#1E3A5F] font-semibold transition border-t border-slate-100">
                            <i class="fa-solid fa-users-gear w-5 text-[#C9A227]"></i> Manager Workbench
                        </a>
                        @endif
                        <a href="{{ route('portal.billing.index') }}" class="flex items-center px-4 py-2 hover:bg-slate-50 text-slate-700 hover:text-slate-900 transition border-t border-slate-100">
                            <i class="fa-solid fa-receipt w-5 text-slate-400"></i> Billing & Subscriptions
                        </a>
                        <a href="{{ route('operations.system-health') }}" class="flex items-center px-4 py-2 hover:bg-slate-50 text-slate-700 hover:text-slate-900 transition">
                            <i class="fa-solid fa-shield-halved w-5 text-slate-400"></i> Operations & Health
                        </a>
                        <div class="border-t border-slate-100 mt-1 pt-1">
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-rose-600 hover:bg-rose-50 transition font-semibold">
                                    <i class="fa-solid fa-arrow-right-from-bracket w-5 text-rose-500"></i> Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Primary Digital Workplace Navigation -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-slate-700/60 overflow-x-auto">
            <nav class="flex space-x-1 py-1.5 text-xs font-medium text-slate-300 whitespace-nowrap">
                <a href="{{ route('portal.dashboard') }}" class="px-3 py-1.5 rounded-md hover:text-white hover:bg-[#142A44] transition flex items-center {{ request()->routeIs('portal.dashboard') ? 'text-white bg-[#142A44] font-semibold border-b-2 border-[#C9A227]' : '' }}">
                    <i class="fa-solid fa-house mr-1.5"></i> Home
                </a>
                <a href="{{ route('portal.work') }}" class="px-3 py-1.5 rounded-md hover:text-white hover:bg-[#142A44] transition flex items-center {{ request()->routeIs('portal.work') ? 'text-white bg-[#142A44] font-semibold border-b-2 border-[#C9A227]' : '' }}">
                    <i class="fa-solid fa-briefcase mr-1.5"></i> My Work
                </a>
                <a href="{{ route('portal.requests') }}" class="px-3 py-1.5 rounded-md hover:text-white hover:bg-[#142A44] transition flex items-center {{ request()->routeIs('portal.requests') ? 'text-white bg-[#142A44] font-semibold border-b-2 border-[#C9A227]' : '' }}">
                    <i class="fa-solid fa-code-pull-request mr-1.5"></i> My Requests
                </a>
                <a href="{{ route('portal.profile') }}" class="px-3 py-1.5 rounded-md hover:text-white hover:bg-[#142A44] transition flex items-center {{ request()->routeIs('portal.profile') ? 'text-white bg-[#142A44] font-semibold border-b-2 border-[#C9A227]' : '' }}">
                    <i class="fa-solid fa-user mr-1.5"></i> My Information
                </a>
                <a href="{{ route('portal.pay') }}" class="px-3 py-1.5 rounded-md hover:text-white hover:bg-[#142A44] transition flex items-center {{ request()->routeIs('portal.pay') ? 'text-white bg-[#142A44] font-semibold border-b-2 border-[#C9A227]' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar mr-1.5"></i> My Pay
                </a>
                <a href="{{ route('portal.growth') }}" class="px-3 py-1.5 rounded-md hover:text-white hover:bg-[#142A44] transition flex items-center {{ request()->routeIs('portal.growth') ? 'text-white bg-[#142A44] font-semibold border-b-2 border-[#C9A227]' : '' }}">
                    <i class="fa-solid fa-arrow-trend-up mr-1.5"></i> My Growth
                </a>
                <a href="{{ route('portal.documents') }}" class="px-3 py-1.5 rounded-md hover:text-white hover:bg-[#142A44] transition flex items-center {{ request()->routeIs('portal.documents') ? 'text-white bg-[#142A44] font-semibold border-b-2 border-[#C9A227]' : '' }}">
                    <i class="fa-solid fa-folder-open mr-1.5"></i> My Documents
                </a>
                <a href="{{ route('portal.services') }}" class="px-3 py-1.5 rounded-md hover:text-white hover:bg-[#142A44] transition flex items-center {{ request()->routeIs('portal.services') ? 'text-white bg-[#142A44] font-semibold border-b-2 border-[#C9A227]' : '' }}">
                    <i class="fa-solid fa-headset mr-1.5"></i> My HR Services
                </a>
                <a href="{{ route('portal.directory') }}" class="px-3 py-1.5 rounded-md hover:text-white hover:bg-[#142A44] transition flex items-center {{ request()->routeIs('portal.directory') ? 'text-white bg-[#142A44] font-semibold border-b-2 border-[#C9A227]' : '' }}">
                    <i class="fa-solid fa-address-book mr-1.5"></i> My People
                </a>
                <a href="{{ route('portal.privacy') }}" class="px-3 py-1.5 rounded-md hover:text-white hover:bg-[#142A44] transition flex items-center {{ request()->routeIs('portal.privacy') ? 'text-white bg-[#142A44] font-semibold border-b-2 border-[#C9A227]' : '' }}">
                    <i class="fa-solid fa-user-shield mr-1.5"></i> Privacy
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content Canvas -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Embedded AI Concierge Slide-over Drawer -->
    <div id="ai-drawer" class="fixed inset-y-0 right-0 max-w-md w-full bg-slate-900 border-l border-slate-800 shadow-2xl z-50 transform translate-x-full transition-transform duration-300 flex flex-col">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-sm shadow">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Ask HR Concierge</h3>
                    <p class="text-[10px] text-slate-400">Authenticated Context: {{ $currentPerson?->fullName() ?? 'Employee' }}</p>
                </div>
            </div>
            <button onclick="toggleAiDrawer()" class="text-slate-400 hover:text-white p-1 rounded">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div id="ai-chat-messages" class="flex-1 p-4 overflow-y-auto space-y-3 text-xs">
            <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700/60 text-slate-200">
                <div class="font-bold text-indigo-400 mb-1 flex items-center">
                    <i class="fa-solid fa-robot mr-1.5"></i> HR Assistant
                </div>
                Hello {{ $currentPerson?->first_name ?? 'there' }}! I am your enterprise HR Concierge. How can I assist you with your schedule, leave, payslips, or policies today?
            </div>
        </div>

        <!-- Suggestion Chips -->
        <div class="px-4 py-2 bg-slate-950 border-t border-slate-800/80 flex flex-wrap gap-1.5 text-[11px]">
            <button onclick="sendAiPrompt('How much leave do I have remaining?')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                Leave balance?
            </button>
            <button onclick="sendAiPrompt('When is my next shift schedule?')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                My next shift?
            </button>
            <button onclick="sendAiPrompt('Where is my latest payslip?')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                Latest payslip?
            </button>
        </div>

        <!-- Chat Input Form -->
        <form id="ai-chat-form" onsubmit="handleAiSubmit(event)" class="p-3 border-t border-slate-800 bg-slate-950 flex space-x-2">
            <input type="text" id="ai-chat-input" placeholder="Ask a question about HR, policy, leave..." required
                class="flex-1 px-3 py-2 bg-slate-900 border border-slate-800 rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            <button type="submit" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold shadow transition">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>
    </div>

    <!-- Backdrop for Drawer -->
    <div id="ai-drawer-backdrop" onclick="toggleAiDrawer()" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-40 hidden transition-opacity"></div>

    <!-- Enterprise Toast Notification Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col space-y-3 pointer-events-none max-w-sm w-full"></div>

    <!-- Global Quick Search & Command Palette Modal (Ctrl+K) -->
    <div id="global-search-modal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm hidden items-start justify-center pt-20 px-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-xl w-full overflow-hidden flex flex-col">
            <div class="p-3 border-b border-slate-100 flex items-center space-x-3 bg-slate-50">
                <i class="fa-solid fa-magnifying-glass text-slate-400 pl-2"></i>
                <input type="text" id="palette-input" placeholder="Type a destination, person, document, or command..."
                    class="flex-1 bg-transparent border-none text-sm text-slate-900 placeholder-slate-400 focus:outline-none"
                    oninput="handlePaletteSearch(this.value)">
                <kbd class="px-2 py-0.5 text-[10px] bg-slate-200 text-slate-600 rounded">ESC</kbd>
            </div>
            <div id="palette-results" class="max-h-80 overflow-y-auto p-2 space-y-1 text-xs">
                <div class="text-[10px] uppercase font-bold text-slate-400 px-3 py-1">Quick Navigation</div>
                <a href="{{ route('portal.dashboard') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-800 font-medium">
                    <i class="fa-solid fa-house w-6 text-[#1E3A5F]"></i> Portal Home
                </a>
                <a href="{{ route('portal.work') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-800 font-medium">
                    <i class="fa-solid fa-briefcase w-6 text-[#1E3A5F]"></i> Attendance & Shifts
                </a>
                <a href="{{ route('portal.requests') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-800 font-medium">
                    <i class="fa-solid fa-calendar-check w-6 text-[#1E3A5F]"></i> Leave Requests
                </a>
                <a href="{{ route('portal.pay') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-800 font-medium">
                    <i class="fa-solid fa-file-invoice-dollar w-6 text-[#1E3A5F]"></i> Payslips & Compensation
                </a>
                <a href="{{ route('portal.documents') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-800 font-medium">
                    <i class="fa-solid fa-folder-open w-6 text-[#1E3A5F]"></i> Documents & Policies
                </a>
                <a href="{{ route('portal.directory') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-800 font-medium">
                    <i class="fa-solid fa-address-book w-6 text-[#1E3A5F]"></i> Employee Directory
                </a>
                <a href="{{ route('portal.privacy') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-800 font-medium">
                    <i class="fa-solid fa-user-shield w-6 text-[#1E3A5F]"></i> Privacy & Data Rights
                </a>
                <a href="{{ route('portal.billing.index') }}" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-100 text-slate-800 font-medium">
                    <i class="fa-solid fa-receipt w-6 text-[#1E3A5F]"></i> Subscription & Billing
                </a>
            </div>
            <div class="p-2.5 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-500 flex justify-between items-center">
                <span>Navigate with arrows, Enter to select</span>
                <button onclick="closePaletteModal()" class="hover:text-slate-800">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Global Toast Notification System
        window.showNotification = function(type, message, title = null, referenceId = null) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto p-4 rounded-xl shadow-2xl border flex items-start space-x-3 transition-all transform duration-300 translate-y-2 opacity-0 text-xs';

            let bgClass = 'bg-white border-slate-200 text-slate-800';
            let icon = 'fa-circle-info text-blue-500';
            let defaultTitle = 'Notification';

            if (type === 'success') {
                bgClass = 'bg-white border-emerald-200 text-slate-800 shadow-emerald-500/10';
                icon = 'fa-circle-check text-emerald-600 text-base';
                defaultTitle = 'Success';
            } else if (type === 'error' || type === 'danger') {
                bgClass = 'bg-white border-rose-200 text-slate-800 shadow-rose-500/10';
                icon = 'fa-circle-exclamation text-rose-600 text-base';
                defaultTitle = 'Action Failed';
            } else if (type === 'warning') {
                bgClass = 'bg-white border-amber-200 text-slate-800 shadow-amber-500/10';
                icon = 'fa-triangle-exclamation text-amber-500 text-base';
                defaultTitle = 'Attention';
            }

            toast.className += ` ${bgClass}`;
            toast.innerHTML = `
                <i class="fa-solid ${icon} shrink-0 mt-0.5"></i>
                <div class="flex-1">
                    <div class="font-bold text-slate-900 leading-tight">${title || defaultTitle}</div>
                    <div class="text-slate-600 mt-0.5">${message}</div>
                    ${referenceId ? `<div class="mt-1 font-mono text-[10px] text-slate-400">Ref: ${referenceId}</div>` : ''}
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 shrink-0">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 10);

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        };

        // Double-Submit & Async Button Helper
        window.submitAsync = async function(btn, task) {
            if (!btn || btn.dataset.loading === 'true') return;
            btn.dataset.loading = 'true';
            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Processing...';

            try {
                return await task();
            } finally {
                btn.disabled = false;
                btn.dataset.loading = 'false';
                btn.innerHTML = originalHtml;
            }
        };

        // Header Dropdowns
        function toggleNotificationsDropdown() {
            const dd = document.getElementById('notifications-dropdown');
            const userDd = document.getElementById('user-dropdown');
            userDd?.classList.add('hidden');
            dd?.classList.toggle('hidden');
        }

        function toggleUserDropdown() {
            const dd = document.getElementById('user-dropdown');
            const notifDd = document.getElementById('notifications-dropdown');
            notifDd?.classList.add('hidden');
            dd?.classList.toggle('hidden');
        }

        function markAllNotificationsRead() {
            const badge = document.getElementById('notif-badge');
            if (badge) badge.remove();
            window.showNotification('success', 'All notifications marked as read.');
            document.getElementById('notifications-dropdown')?.classList.add('hidden');
        }

        // Close dropdowns on click outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#notifications-dropdown') && !e.target.closest('#btn-notifications')) {
                document.getElementById('notifications-dropdown')?.classList.add('hidden');
            }
            if (!e.target.closest('#user-dropdown') && !e.target.closest('button[onclick="toggleUserDropdown()"]')) {
                document.getElementById('user-dropdown')?.classList.add('hidden');
            }
        });

        // Command Palette & Global Search Modal
        const searchInput = document.getElementById('global-search');
        if (searchInput) {
            searchInput.addEventListener('focus', function() {
                openPaletteModal();
                searchInput.blur();
            });
        }

        document.addEventListener('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                openPaletteModal();
            } else if (e.key === 'Escape') {
                closePaletteModal();
            }
        });

        function openPaletteModal() {
            const modal = document.getElementById('global-search-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => document.getElementById('palette-input')?.focus(), 50);
        }

        function closePaletteModal() {
            const modal = document.getElementById('global-search-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function handlePaletteSearch(val) {
            const term = val.toLowerCase().trim();
            const results = document.getElementById('palette-results');
            if (!results) return;
            const links = results.querySelectorAll('a');
            links.forEach(a => {
                if (a.textContent.toLowerCase().includes(term)) {
                    a.style.display = 'flex';
                } else {
                    a.style.display = 'none';
                }
            });
        }

        // Flash Messages on page load
        @if(session('success'))
            document.addEventListener('DOMContentLoaded', () => window.showNotification('success', "{{ session('success') }}"));
        @endif
        @if(session('error'))
            document.addEventListener('DOMContentLoaded', () => window.showNotification('error', "{{ session('error') }}"));
        @endif
        @if(session('warning'))
            document.addEventListener('DOMContentLoaded', () => window.showNotification('warning', "{{ session('warning') }}"));
        @endif

        function toggleAiDrawer() {
            const drawer = document.getElementById('ai-drawer');
            const backdrop = document.getElementById('ai-drawer-backdrop');
            const isOpen = !drawer.classList.contains('translate-x-full');

            if (isOpen) {
                drawer.classList.add('translate-x-full');
                backdrop.classList.add('hidden');
            } else {
                drawer.classList.remove('translate-x-full');
                backdrop.classList.remove('hidden');
                document.getElementById('ai-chat-input').focus();
            }
        }

        async function handleAiSubmit(e) {
            e.preventDefault();
            const input = document.getElementById('ai-chat-input');
            const prompt = input.value.trim();
            if (!prompt) return;
            input.value = '';
            await sendAiPrompt(prompt);
        }

        async function sendAiPrompt(prompt) {
            const container = document.getElementById('ai-chat-messages');

            // Append user bubble
            const userMsg = document.createElement('div');
            userMsg.className = 'bg-indigo-600/30 border border-indigo-500/40 p-3 rounded-xl text-slate-100 ml-6';
            userMsg.innerHTML = '<div class="font-bold text-indigo-300 mb-0.5 text-[10px] uppercase">You</div>' + prompt;
            container.appendChild(userMsg);
            container.scrollTop = container.scrollHeight;

            // Append loading indicator
            const loadingMsg = document.createElement('div');
            loadingMsg.className = 'bg-slate-800/80 p-3 rounded-xl border border-slate-700/60 text-slate-300 mr-6 flex items-center space-x-2';
            loadingMsg.id = 'ai-loading';
            loadingMsg.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-indigo-400"></i><span>Consulting HCM knowledge...</span>';
            container.appendChild(loadingMsg);
            container.scrollTop = container.scrollHeight;

            try {
                const res = await fetch('/api/me/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Tenant-ID': '{{ $currentPerson?->tenant_id ?? "default" }}',
                        'X-Employee-ID': '{{ $currentPerson?->id ?? "" }}'
                    },
                    body: JSON.stringify({ prompt })
                });
                const data = await res.json();
                document.getElementById('ai-loading')?.remove();

                const aiMsg = document.createElement('div');
                aiMsg.className = 'bg-slate-800/80 p-3 rounded-xl border border-slate-700/60 text-slate-200 mr-6';
                aiMsg.innerHTML = '<div class="font-bold text-indigo-400 mb-1 flex items-center"><i class="fa-solid fa-robot mr-1.5"></i> HR Assistant</div>' + 
                    (data.response || data.message || 'I could not find an answer for that request.');
                container.appendChild(aiMsg);
            } catch (err) {
                document.getElementById('ai-loading')?.remove();
                const errMsg = document.createElement('div');
                errMsg.className = 'bg-red-500/20 p-3 rounded-xl border border-red-500/40 text-red-300 mr-6';
                errMsg.innerHTML = 'Unable to reach HR Concierge service. Please try again.';
                container.appendChild(errMsg);
            }
            container.scrollTop = container.scrollHeight;
        }
    </script>
</body>
</html>
