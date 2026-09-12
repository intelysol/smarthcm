@extends('workforce-admin.layout')

@section('title', 'HR Operations Cockpit')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Workforce Administration Cockpit</h1>
            <p class="text-sm text-slate-400">Centralized operational oversight, queue orchestration, exception handling, and data governance.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('workforce_admin.bulk.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-700 rounded-lg text-sm font-medium bg-slate-850 hover:bg-slate-800 text-slate-200 transition">
                <i class="fa-solid fa-bolt mr-2 text-amber-400"></i> New Bulk Operation
            </a>
            <a href="{{ route('workforce_admin.exceptions.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium bg-cyan-600 hover:bg-cyan-500 text-white shadow-lg shadow-cyan-600/30 transition">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i> Manage Exceptions
            </a>
        </div>
    </div>

    <!-- Stat Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Workforce</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ number_format($summary['workforce']['active_employees'] ?? 0) }}</div>
                <span class="text-xs text-slate-400">Total: {{ number_format($summary['workforce']['total_employees'] ?? 0) }}</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending Queues</span>
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                    <i class="fa-solid fa-list-check"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ number_format($summary['queues']['pending_items'] ?? 0) }}</div>
                <span class="text-xs text-cyan-400">Items Awaiting Action</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Open Exceptions</span>
                <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ number_format($summary['exceptions']['open_total'] ?? 0) }}</div>
                <span class="text-xs text-rose-400">{{ number_format($summary['exceptions']['critical'] ?? 0) }} Critical</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">SLA Breaches</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ number_format($summary['queues']['sla_breaches'] ?? 0) }}</div>
                <span class="text-xs text-amber-400">Overdue Service Targets</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Incomplete Profiles</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                    <i class="fa-solid fa-address-card"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ number_format($summary['workforce']['incomplete_records'] ?? 0) }}</div>
                <span class="text-xs text-indigo-400">Missing Dept/Mgr</span>
            </div>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Exceptions -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-white">Recent Exceptions</h2>
                <a href="{{ route('workforce_admin.exceptions.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 font-medium">View all &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-slate-400 border-b border-slate-800 uppercase tracking-wider">
                        <tr>
                            <th class="py-2.5">Exception #</th>
                            <th class="py-2.5">Domain</th>
                            <th class="py-2.5">Severity</th>
                            <th class="py-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($recentExceptions as $exc)
                        <tr class="hover:bg-slate-850/50">
                            <td class="py-3 font-mono text-cyan-300">{{ $exc->exception_number }}</td>
                            <td class="py-3">{{ ucfirst($exc->domain) }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $exc->severity === 'critical' ? 'bg-rose-500/10 text-rose-400' : 'bg-amber-500/10 text-amber-400' }}">
                                    {{ strtoupper($exc->severity) }}
                                </span>
                            </td>
                            <td class="py-3 font-medium">{{ ucfirst($exc->status->value) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-slate-500">No active exceptions detected.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Bulk Operations -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-white">Recent Bulk Operations</h2>
                <a href="{{ route('workforce_admin.bulk.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 font-medium">View all &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-slate-400 border-b border-slate-800 uppercase tracking-wider">
                        <tr>
                            <th class="py-2.5">Operation #</th>
                            <th class="py-2.5">Type</th>
                            <th class="py-2.5">Records</th>
                            <th class="py-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($recentBulkOps as $op)
                        <tr class="hover:bg-slate-850/50">
                            <td class="py-3 font-mono text-amber-300">{{ $op->operation_number }}</td>
                            <td class="py-3">{{ ucwords(str_replace('_', ' ', $op->operation_type)) }}</td>
                            <td class="py-3 font-mono">{{ $op->total_records }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-slate-300">
                                    {{ ucfirst($op->status->value) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-slate-500">No bulk operations executed.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
