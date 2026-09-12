@extends('compliance.layout')

@section('title', 'Expiration Management Console')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Expiration & Renewal Monitor</h2>
        <p class="text-sm text-slate-500">Track and act upon upcoming expirations for work permits, travel visas, and occupational licenses before regulatory breaches occur.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-rose-700 uppercase tracking-wide">30-Day Critical Window</h3>
            <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ $stats['expiring_30_days'] ?? 0 }}</p>
            <p class="text-xs text-slate-500 mt-1">Requires immediate renewal or formal extension</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-amber-700 uppercase tracking-wide">60-Day Warning Window</h3>
            <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ $stats['expiring_60_days'] ?? 0 }}</p>
            <p class="text-xs text-slate-500 mt-1">Renewal documents should be initiated</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-indigo-700 uppercase tracking-wide">90-Day Horizon</h3>
            <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ $stats['expiring_90_days'] ?? 0 }}</p>
            <p class="text-xs text-slate-500 mt-1">First automated notification dispatched</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-slate-800">Automated Escalation Triggers</h3>
            <span class="text-xs font-semibold px-2 py-1 rounded bg-emerald-100 text-emerald-800">Active Pipeline</span>
        </div>
        <div class="text-sm text-slate-600 space-y-2">
            <p>&bull; <strong>T-90 Days:</strong> Employee self-service renewal prompt dispatched.</p>
            <p>&bull; <strong>T-60 Days:</strong> Direct Manager and HR Operations Task generated.</p>
            <p>&bull; <strong>T-30 Days:</strong> High-severity alert escalated to Compliance Officer.</p>
            <p>&bull; <strong>T-0 / Post-Expiry:</strong> Immediate operational restriction flag and personnel action lock.</p>
        </div>
    </div>
</div>
@endsection
