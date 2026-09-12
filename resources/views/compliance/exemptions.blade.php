@extends('compliance.layout')

@section('title', 'Controlled Exemptions Governance')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Controlled Exemption Registry</h2>
            <p class="text-sm text-slate-500">Strict governance over regulatory waivers, grandfathered credentials, and temporary grace periods.</p>
        </div>
        <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
            + Request Exemption
        </button>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="ml-3">
                <h3 class="text-sm font-semibold text-amber-800">Controlled Governance Rule</h3>
                <div class="mt-1 text-xs text-amber-700">
                    No compliance requirement is ever waived automatically. Every approved exemption requires an immutable justification, executive approver signature, explicit validity window, and periodic audit re-certification.
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-800">Currently Active Exemptions</h3>
            <span class="text-xs bg-slate-100 text-slate-700 font-semibold px-2 py-1 rounded">
                Total: {{ $stats['active_exemptions'] ?? 0 }}
            </span>
        </div>
        <div class="mt-4 text-center py-8 text-sm text-slate-500">
            Use the API endpoints or Administrative Exemption dialog to view, approve, or revoke active exemptions.
        </div>
    </div>
</div>
@endsection
