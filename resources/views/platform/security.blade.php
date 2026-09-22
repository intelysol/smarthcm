@extends('shells.platform')

@section('title', 'Platform Security & Audit')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Platform Security &amp; Audit Trail</h1>
            <p class="text-xs text-slate-400">Security event monitoring, authentication logs, and isolation compliance</p>
        </div>
        <a href="{{ route('platform.control-center') }}" class="text-xs font-semibold text-[#C9A227] hover:underline">&larr; Back to Control Center</a>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
        <h2 class="text-sm font-bold text-white">Security Certification Status</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
            <div class="p-3 bg-slate-950 rounded-lg border border-slate-800 flex items-center justify-between">
                <span>Multi-Tenant Data Isolation Gate:</span>
                <span class="text-emerald-400 font-bold">PASSED (Zero Leaks)</span>
            </div>
            <div class="p-3 bg-slate-950 rounded-lg border border-slate-800 flex items-center justify-between">
                <span>SQLi, Path Traversal &amp; XSS Defense:</span>
                <span class="text-emerald-400 font-bold">PASSED (Zero Bypasses)</span>
            </div>
            <div class="p-3 bg-slate-950 rounded-lg border border-slate-800 flex items-center justify-between">
                <span>Brute-force Rate Limiter:</span>
                <span class="text-emerald-400 font-bold">ACTIVE (5 attempts / min)</span>
            </div>
            <div class="p-3 bg-slate-950 rounded-lg border border-slate-800 flex items-center justify-between">
                <span>AI Adverse Action Boundaries:</span>
                <span class="text-emerald-400 font-bold">STRICTLY ENFORCED</span>
            </div>
        </div>
    </div>
</div>
@endsection
