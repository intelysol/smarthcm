@extends('shells.tenant')

@section('title', 'Organization Administration')

@section('content')
<div class="space-y-6">

    <!-- Hero Header -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center font-black text-xl shadow">
                {{ substr($tenant->name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl font-black text-[#1E3A5F] tracking-tight">{{ $tenant->name }}</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        {{ $metrics['status'] }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Organization Administration &amp; Multi-Department Governance</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.departments') }}" class="px-3.5 py-2 rounded-xl bg-[#1E3A5F] hover:bg-[#142A44] text-white font-semibold text-xs shadow transition flex items-center">
                <i class="fa-solid fa-sitemap mr-1.5 text-[#C9A227]"></i> Manage Departments
            </a>
            <a href="{{ route('admin.settings') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition border border-slate-200 flex items-center">
                <i class="fa-solid fa-gears mr-1.5 text-slate-500"></i> Org Settings
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Departments</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-sitemap"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-slate-900">{{ $metrics['departments_count'] }}</span>
                <span class="text-xs text-slate-400 font-medium">divisions</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                <span>Active organizational units</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Job Positions</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-id-badge"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-slate-900">{{ $metrics['positions_count'] }}</span>
                <span class="text-xs text-slate-400 font-medium">defined</span>
            </div>
            <div class="mt-2 text-[11px] text-emerald-700 flex items-center">
                <i class="fa-solid fa-check mr-1 text-[10px]"></i> Job architecture mapped
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Users &amp; Access</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-users-gear"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-slate-900">{{ $metrics['users_count'] }}</span>
                <span class="text-xs text-slate-400 font-medium">accounts</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                <span>{{ $metrics['employees_count'] }} linked employee profiles</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Setup Health</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-emerald-600">{{ $metrics['setup_health'] }}%</span>
                <span class="text-xs text-slate-400 font-medium">score</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                <span>{{ $metrics['policies_count'] }} compliance policies enforced</span>
            </div>
        </div>
    </div>

    <!-- Quick Operations Panels -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-3">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fa-solid fa-sitemap"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Organization Hierarchy</h3>
                    <p class="text-xs text-slate-500">Structure departments, business units, and offices.</p>
                </div>
            </div>
            <div class="pt-2">
                <a href="{{ route('admin.departments') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline flex items-center">
                    Manage Departments &rarr;
                </a>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-3">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-diagram-project"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Workflow Routing</h3>
                    <p class="text-xs text-slate-500">Configure multi-step approvals for leaves and requests.</p>
                </div>
            </div>
            <div class="pt-2">
                <a href="{{ route('admin.workflows') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline flex items-center">
                    Configure Approvals &rarr;
                </a>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-3">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Roles &amp; Permissions</h3>
                    <p class="text-xs text-slate-500">Assign role boundaries and tenant permissions.</p>
                </div>
            </div>
            <div class="pt-2">
                <a href="{{ route('admin.users') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline flex items-center">
                    Manage User Roles &rarr;
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
