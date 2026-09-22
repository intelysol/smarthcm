@extends('shells.platform')

@section('title', 'Platform AI Governance')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">AI Governance &amp; Model Guardrails</h1>
            <p class="text-xs text-slate-400">Manage LLM configurations, prompt boundaries, and HITL policies</p>
        </div>
        <a href="{{ route('platform.control-center') }}" class="text-xs font-semibold text-[#C9A227] hover:underline">&larr; Back to Control Center</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs font-medium text-slate-400">HITL Status</span>
            <div class="mt-2 text-xl font-bold text-emerald-400">Mandatory Active</div>
            <p class="text-xs text-slate-400 mt-1">Actions start in PROPOSED status</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs font-medium text-slate-400">Autonomous Adverse Actions</span>
            <div class="mt-2 text-xl font-bold text-rose-400">Strictly Blocked</div>
            <p class="text-xs text-slate-400 mt-1">Zero automated termination/demotion</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs font-medium text-slate-400">Tenant Vector Scoping</span>
            <div class="mt-2 text-xl font-bold text-emerald-400">Isolated</div>
            <p class="text-xs text-slate-400 mt-1">No cross-tenant data leakage in RAG</p>
        </div>
    </div>
</div>
@endsection
