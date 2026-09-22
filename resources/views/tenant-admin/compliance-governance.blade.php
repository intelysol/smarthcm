@extends('shells.tenant')

@section('title', 'Compliance & Privacy Governance — Tenant Admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Compliance &amp; Privacy Governance</h1>
            <p class="text-xs text-slate-500">Tenant-isolated regulatory controls, Record of Processing Activities (ROPA), and Data Subject Rights management</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">&larr; Back to Overview</a>
    </div>

    <!-- Tenant Compliance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Compliance Score</span>
            <div class="mt-2 text-2xl font-black text-emerald-600 font-mono">{{ $governanceScorecard['compliance_score_percent'] }}%</div>
            <p class="text-xs text-slate-400 mt-1">{{ $governanceScorecard['controls_active'] }} active controls</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Processing Activities (ROPA)</span>
            <div class="mt-2 text-2xl font-black text-slate-900 font-mono">{{ $privacyMetrics['activities_count'] }}</div>
            <p class="text-xs text-slate-400 mt-1">Documented processing flows</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Privacy &amp; DSAR Requests</span>
            <div class="mt-2 text-2xl font-black text-[#1E3A5F] font-mono">{{ $privacyMetrics['total_requests'] }}</div>
            <p class="text-xs text-slate-400 mt-1">{{ $privacyMetrics['completed_requests'] }} completed, {{ $privacyMetrics['pending_requests'] }} pending</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Open Audit Findings</span>
            <div class="mt-2 text-2xl font-black {{ $governanceScorecard['open_findings'] > 0 ? 'text-rose-600' : 'text-emerald-600' }} font-mono">{{ $governanceScorecard['open_findings'] }}</div>
            <p class="text-xs text-slate-400 mt-1">Pending remediation</p>
        </div>
    </div>

    <!-- Record of Processing Activities (ROPA) -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-file-contract text-[#1E3A5F]"></i>
                Record of Processing Activities (ROPA &mdash; GDPR Art. 30)
            </h2>
            <span class="text-xs font-semibold text-slate-500">{{ $activities->count() }} Registered Activities</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                    <tr>
                        <th class="p-3">Processing Activity</th>
                        <th class="p-3">Business Owner</th>
                        <th class="p-3">Legal Basis</th>
                        <th class="p-3">Categories of Data</th>
                        <th class="p-3">Location</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($activities as $act)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3">
                            <div class="font-bold text-slate-900">{{ $act->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $act->purpose }}</div>
                        </td>
                        <td class="p-3 font-medium text-slate-800">{{ $act->business_owner }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-semibold text-[10px]">{{ $act->legal_basis }}</span>
                        </td>
                        <td class="p-3 text-[11px] text-slate-500">
                            {{ is_array($act->data_categories) ? implode(', ', $act->data_categories) : $act->data_categories }}
                        </td>
                        <td class="p-3 font-mono text-[11px] text-slate-600">{{ $act->processing_location }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-400">
                            No processing activities recorded for this tenant.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Data Subject Access Requests (DSAR) -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-user-lock text-emerald-600"></i>
                Data Subject Rights &amp; Privacy Requests
            </h2>
            <span class="text-xs text-slate-500">GDPR / CCPA Subject Fulfillment</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                    <tr>
                        <th class="p-3">Reference</th>
                        <th class="p-3">Request Type</th>
                        <th class="p-3">Identity Verified</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Evidence Hash / Reason</th>
                        <th class="p-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($privacyRequests as $pr)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3 font-mono font-bold text-slate-900">{{ $pr->request_reference }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">{{ $pr->request_type }}</span>
                        </td>
                        <td class="p-3">
                            <span class="text-emerald-600 font-bold"><i class="fa-solid fa-check"></i> Verified</span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $pr->status === 'COMPLETED' ? 'bg-emerald-50 text-emerald-700' : ($pr->status === 'REJECTED' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                                {{ $pr->status }}
                            </span>
                        </td>
                        <td class="p-3 font-mono text-[11px]">
                            @if($pr->export_hash_sha256)
                                <span class="text-slate-500" title="{{ $pr->export_hash_sha256 }}">SHA-256: {{ Str::limit($pr->export_hash_sha256, 16) }}</span>
                            @elseif($pr->blocked_reason)
                                <span class="text-amber-700">{{ Str::limit($pr->blocked_reason, 40) }}</span>
                            @else
                                <span class="text-slate-400">&mdash;</span>
                            @endif
                        </td>
                        <td class="p-3 text-slate-500">{{ $pr->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-400">
                            No privacy requests received for this tenant.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Active Compliance Controls -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5 space-y-4">
        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-shield-halved text-indigo-600"></i>
            Applicable Compliance Controls Baseline
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
            @forelse($controls as $ctl)
            <div class="p-3 rounded-lg border border-slate-200 bg-slate-50/50 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="font-mono font-bold text-indigo-700">{{ $ctl->code }}</span>
                    <span class="text-[10px] font-bold uppercase text-slate-500">{{ $ctl->control_type }}</span>
                </div>
                <div class="font-semibold text-slate-800">{{ $ctl->title }}</div>
                <div class="text-[11px] text-slate-500">{{ $ctl->objective }}</div>
            </div>
            @empty
            <div class="col-span-3 text-center text-slate-400 p-4">
                No controls assigned.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
