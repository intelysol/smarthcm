@extends('shells.tenant')

@section('title', 'Data Retention & Archival Policies — Tenant Admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Data Retention &amp; Archival Policies</h1>
            <p class="text-xs text-slate-500">Tenant-scoped lifecycle rules, statutory compliance floors, and cold storage management</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">&larr; Back to Overview</a>
    </div>

    <!-- Tenant Storage Metrics Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tenant Storage Footprint</span>
            <div class="mt-2 text-2xl font-black text-slate-900 font-mono">{{ number_format($telemetry['total_storage_mb'], 1) }} <span class="text-xs text-slate-500 font-normal">MB</span></div>
            <p class="text-xs text-slate-400 mt-1">Operational: {{ number_format($telemetry['active_operational_storage_mb'], 1) }} MB</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Archived Packages</span>
            <div class="mt-2 text-2xl font-black text-emerald-600 font-mono">{{ $telemetry['total_archives_packages'] }}</div>
            <p class="text-xs text-slate-400 mt-1">Cold storage vault</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Legal Holds</span>
            <div class="mt-2 text-2xl font-black text-[#C9A227] font-mono">{{ $telemetry['active_legal_holds'] }}</div>
            <p class="text-xs text-slate-400 mt-1">Deletion suspension locks</p>
        </div>
    </div>

    <!-- Statutory Floor Protection Notice -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-shield-halved text-emerald-600"></i>
            Statutory Retention Floor Protection
        </h2>
        <p class="text-xs text-slate-600">
            Tenant retention policies inherit from the platform's global compliance baseline. In accordance with legal standards, tenant configurations cannot reduce retention below statutory floors (<code class="text-xs bg-slate-100 px-1 py-0.5 rounded font-mono">MAX(Platform Minimum, Tenant Request)</code>).
        </p>
        <div class="space-y-3 text-xs">
            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-slate-600">Domain:</span>
                <span class="font-bold text-slate-900">{{ $effectivePolicy['domain'] }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-slate-600">Data Class:</span>
                <span class="font-bold text-slate-900">{{ $effectivePolicy['data_class'] }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-slate-600">Platform Statutory Minimum:</span>
                <span class="font-mono font-bold text-slate-800">{{ $effectivePolicy['platform_statutory_floor_days'] }} days (7 years)</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-slate-600">Tenant Requested Retention:</span>
                <span class="font-mono text-slate-700">{{ $effectivePolicy['tenant_requested_days'] ?? 'Default' }} days</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-slate-600">Effective Enforced Retention:</span>
                <span class="font-mono font-bold text-emerald-700">{{ $effectivePolicy['effective_retention_days'] }} days</span>
            </div>
            <div class="flex justify-between items-center py-2">
                <span class="text-slate-600">Statutory Floor Enforced:</span>
                <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $effectivePolicy['statutory_floor_enforced'] ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                    {{ $effectivePolicy['statutory_floor_enforced'] ? 'YES (Statutory Floor Enforced)' : 'NO (Compliant)' }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
