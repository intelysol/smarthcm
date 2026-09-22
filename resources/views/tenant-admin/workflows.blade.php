@extends('shells.tenant')

@section('title', 'Workflows & Approval Policies')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Workflows &amp; Approval Policies</h1>
            <p class="text-xs text-slate-500">Routing chains for leave applications, expense claims, and promotions</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">&larr; Back to Overview</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Leave Applications</span>
            <div class="mt-2 text-base font-bold text-slate-900">Employee &rarr; Line Manager &rarr; HR</div>
            <p class="text-xs text-emerald-600 mt-1 font-semibold">Active &bull; Auto-escalation enabled</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Expense Claims</span>
            <div class="mt-2 text-base font-bold text-slate-900">Employee &rarr; Line Manager &rarr; Finance</div>
            <p class="text-xs text-emerald-600 mt-1 font-semibold">Active &bull; Tiered threshold</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Document Requests</span>
            <div class="mt-2 text-base font-bold text-slate-900">Employee &rarr; HR Operations</div>
            <p class="text-xs text-emerald-600 mt-1 font-semibold">Active &bull; Automated issuance</p>
        </div>
    </div>
</div>
@endsection
