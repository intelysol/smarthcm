@extends('shells.tenant')

@section('title', 'Organization Settings')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Organization Settings &amp; Customization</h1>
            <p class="text-xs text-slate-500">Tenant-wide branding, regional localization, working calendar defaults, and currencies</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">&larr; Back to Overview</a>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-slate-900">Tenant Localization &amp; General Configuration</h2>
        <div class="space-y-3 text-xs">
            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-slate-600">Organization Legal Name:</span>
                <span class="font-bold text-slate-900">{{ $tenant->name }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-slate-600">Tenant Slug:</span>
                <span class="font-mono text-slate-700">{{ $tenant->slug }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-slate-600">Primary Currency:</span>
                <span class="font-bold text-slate-900">USD ($)</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="text-slate-600">Fiscal Year Start:</span>
                <span class="font-bold text-slate-900">January 01</span>
            </div>
        </div>
    </div>
</div>
@endsection
