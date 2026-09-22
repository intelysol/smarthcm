@extends('shells.tenant')

@section('title', 'Tenant Operations & System Health')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center font-black text-xl shadow">
                <i class="fa-solid fa-server"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl font-black text-[#1E3A5F] tracking-tight">Organization Operational Health</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        HEALTHY
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Tenant-isolated background job status, integration health, and operational alerts.</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <span class="text-xs text-slate-500 font-mono">Tenant ID: {{ substr($tenant->id, 0, 8) }}...</span>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tenant Alerts</span>
            <div class="mt-2 text-2xl font-black {{ $alerts->total() > 0 ? 'text-amber-600' : 'text-emerald-600' }} font-mono">
                {{ $alerts->total() }}
            </div>
            <p class="text-xs text-slate-400 mt-1">Tenant-scoped events</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Telemetry Points</span>
            <div class="mt-2 text-2xl font-black text-[#1E3A5F] font-mono">
                {{ $recentMetrics->count() }}
            </div>
            <p class="text-xs text-slate-400 mt-1">Recorded operations</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Audit Log Records</span>
            <div class="mt-2 text-2xl font-black text-[#1E3A5F] font-mono">
                {{ $auditCount }}
            </div>
            <p class="text-xs text-slate-400 mt-1">Immutable audit entries</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Service Isolation</span>
            <div class="mt-2 text-2xl font-black text-emerald-600">STRICT</div>
            <p class="text-xs text-slate-400 mt-1">Zero-Trust Partitioning</p>
        </div>
    </div>

    <!-- Active Tenant Alerts -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h2 class="text-base font-bold text-[#1E3A5F] mb-4 flex items-center gap-2">
            <i class="fa-solid fa-bell text-[#C9A227]"></i>
            Organization Alerts &amp; Notifications
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider">
                        <th class="pb-3 font-semibold">Message</th>
                        <th class="pb-3 font-semibold">Severity</th>
                        <th class="pb-3 font-semibold">Status</th>
                        <th class="pb-3 font-semibold text-right">Triggered At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alerts as $alert)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 font-medium text-slate-800">{{ $alert->message }}</td>
                        <td class="py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ strtolower($alert->severity) === 'critical' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $alert->severity }}
                            </span>
                        </td>
                        <td class="py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $alert->status === 'open' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $alert->status }}
                            </span>
                        </td>
                        <td class="py-3 text-right text-slate-400 font-mono text-[11px]">
                            {{ $alert->triggered_at?->format('Y-m-d H:i:s') ?? 'N/A' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-lg mb-1 block"></i>
                            No operational alerts reported for your organization.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
