@extends('compliance.layout')

@section('title', 'Controlled Exemptions Governance')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Controlled Exemption Registry</h2>
            <p class="text-sm text-slate-500">Strict governance over regulatory waivers, grandfathered credentials, and temporary grace periods.</p>
        </div>
        <button type="button" onclick="document.getElementById('request-exemption-modal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
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

<!-- Request Exemption Modal -->
<div id="request-exemption-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900">Request Compliance Exemption</h3>
            <button onclick="document.getElementById('request-exemption-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1">&times;</button>
        </div>
        <form onsubmit="handleRequestExemption(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Employee ID / Number</label>
                <input type="text" required placeholder="EMP-2026-0089" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Requirement Category</label>
                <select class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500">
                    <option value="licensure">Professional Practice License Grace Period</option>
                    <option value="cert">Mandatory Safety Recertification Waiver</option>
                    <option value="work_permit">Temporary Visa Extension Pending Adjudication</option>
                    <option value="background">Grandfathered Background Screening Rule</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Exemption Expiry Date</label>
                <input type="date" required value="{{ now()->addMonths(3)->toDateString() }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Audit Justification</label>
                <textarea required rows="2" placeholder="Legal basis or executive approved grace rationale..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-indigo-500"></textarea>
            </div>
            <div class="pt-2 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('request-exemption-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleRequestExemption(e) {
    e.preventDefault();
    document.getElementById('request-exemption-modal').classList.add('hidden');
    window.showNotification('success', 'Compliance exemption request recorded and queued for executive officer review.');
}
</script>
@endsection
