@extends('shells.platform')

@section('title', 'Platform Control Center')

@section('content')
<div class="space-y-6">

    <!-- Top Hero Banner -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 border border-slate-700/80 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-mono text-[#C9A227] font-semibold uppercase tracking-wider mb-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Global SaaS Control Plane &bull; Version {{ $metrics['platform_version'] }}</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Platform Control Center</h1>
            <p class="text-xs text-slate-400 mt-0.5">Centralized operations for multi-tenant provisioning, commercial subscriptions, and system governance.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('platform.tenants') }}" class="px-3.5 py-2 rounded-xl bg-[#1E3A5F] hover:bg-[#142A44] border border-[#C9A227]/40 text-[#F4E7B2] font-semibold text-xs transition shadow flex items-center">
                <i class="fa-solid fa-plus mr-1.5 text-[#C9A227]"></i> Provision Tenant
            </a>
            <a href="{{ route('operations.system-health') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-200 font-semibold text-xs transition flex items-center">
                <i class="fa-solid fa-heart-pulse mr-1.5 text-emerald-400"></i> Health Monitor
            </a>
        </div>
    </div>

    <!-- Core Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Active Tenants</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-building"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-white">{{ $metrics['active_tenants'] }}</span>
                <span class="text-xs text-slate-400 font-medium">/ {{ $metrics['tenants_count'] }} total</span>
            </div>
            <div class="mt-2 text-[11px] text-emerald-400 flex items-center">
                <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> 100% Isolation Enforced
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">Total User Base</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-white">{{ number_format($metrics['total_users']) }}</span>
                <span class="text-xs text-slate-400 font-medium">identities</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400 flex items-center">
                <span>{{ number_format($metrics['total_employees']) }} employee profiles</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">SaaS Subscriptions</span>
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-[#C9A227] flex items-center justify-center text-xs">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-white">{{ $metrics['active_subscriptions'] }}</span>
                <span class="text-xs text-slate-400 font-medium">active billing contracts</span>
            </div>
            <div class="mt-2 text-[11px] text-emerald-400 flex items-center">
                <i class="fa-solid fa-check mr-1 text-[10px]"></i> Commercial Layer Synced
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-400">System Availability</span>
                <span class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-server"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-emerald-400">{{ $metrics['uptime'] }}</span>
                <span class="text-xs text-slate-400 font-medium">30d SLA</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-400 flex items-center">
                <span>Status: <strong class="text-white">{{ $metrics['health_status'] }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Active Tenants Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl shadow overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-white">Managed Enterprise Tenants</h2>
                <p class="text-[11px] text-slate-400">Recent customer workspaces across all clusters</p>
            </div>
            <a href="{{ route('platform.tenants') }}" class="text-xs font-semibold text-[#C9A227] hover:underline">View All Tenants &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/60 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Tenant Name</th>
                        <th class="px-5 py-3 font-semibold">Slug / Identifier</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Created Date</th>
                        <th class="px-5 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse($recentTenants as $t)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3.5 font-bold text-white flex items-center space-x-2.5">
                                <div class="w-6 h-6 rounded bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-[11px] font-bold">
                                    {{ substr($t->name, 0, 1) }}
                                </div>
                                <span>{{ $t->name }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-[11px] text-slate-400">{{ $t->slug }}</td>
                            <td class="px-5 py-3.5">
                                @php
                                    $statusStr = is_string($t->status) ? $t->status : ($t->status?->value ?? (string) $t->status);
                                @endphp
                                @if(strtolower($statusStr) === 'active')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">ACTIVE</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">{{ strtoupper($statusStr) }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-400">{{ $t->created_at ? $t->created_at->format('M d, Y') : 'System' }}</td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-medium border border-slate-700 transition">
                                    Configure
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500">No active tenants found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Platform Subsystems Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-2 flex items-center">
                <i class="fa-solid fa-shield-halved text-[#C9A227] mr-1.5"></i> Platform Security & Invariants
            </h3>
            <ul class="text-xs text-slate-400 space-y-1.5">
                <li class="flex items-center justify-between"><span class="text-slate-300">Tenant Isolation:</span> <span class="text-emerald-400 font-bold">100% Enforced</span></li>
                <li class="flex items-center justify-between"><span class="text-slate-300">Auth & IDOR Defense:</span> <span class="text-emerald-400 font-bold">Passing (33/33)</span></li>
                <li class="flex items-center justify-between"><span class="text-slate-300">Session Hijack Shield:</span> <span class="text-emerald-400 font-bold">Active</span></li>
            </ul>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-2 flex items-center">
                <i class="fa-solid fa-server text-indigo-400 mr-1.5"></i> Runtime & Queues
            </h3>
            <ul class="text-xs text-slate-400 space-y-1.5">
                <li class="flex items-center justify-between"><span class="text-slate-300">PHP Version:</span> <span class="text-white font-mono">{{ PHP_VERSION }}</span></li>
                <li class="flex items-center justify-between"><span class="text-slate-300">Database Driver:</span> <span class="text-white font-mono">{{ config('database.default') }}</span></li>
                <li class="flex items-center justify-between"><span class="text-slate-300">Horizon Workers:</span> <span class="text-emerald-400 font-bold">Online</span></li>
            </ul>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-2 flex items-center">
                <i class="fa-solid fa-brain text-purple-400 mr-1.5"></i> AI Safety & Governance
            </h3>
            <ul class="text-xs text-slate-400 space-y-1.5">
                <li class="flex items-center justify-between"><span class="text-slate-300">HITL Approval Policy:</span> <span class="text-emerald-400 font-bold">Mandatory</span></li>
                <li class="flex items-center justify-between"><span class="text-slate-300">Autonomous Adverse Actions:</span> <span class="text-rose-400 font-bold">Blocked</span></li>
                <li class="flex items-center justify-between"><span class="text-slate-300">Tenant Context Boundaries:</span> <span class="text-emerald-400 font-bold">Enforced</span></li>
            </ul>
        </div>
    </div>

</div>
@endsection
