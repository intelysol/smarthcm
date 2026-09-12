@extends('employee_documents.layout')

@section('title', 'HR Document Center & Personnel Files — Flow HCM')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-sky-600 uppercase tracking-wider mb-1">
                <span>Enterprise HCM</span>
                <span>&bull;</span>
                <span>Digital Personnel File & Governance Layer</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">HR Document Center</h1>
            <p class="text-sm text-slate-500 mt-1">
                Manage digital personnel files, monitor document compliance and verification queues, track expirations, and request mandatory employee records.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ route('employee_documents.employee.portal') }}" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <i class="fa-solid fa-user-tag mr-1.5 text-sky-600"></i>Self-Service Portal
            </a>
            <button class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition">
                <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i>Bulk Upload Documents
            </button>
        </div>
    </div>

    <!-- Top KPI Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Documents -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>TOTAL DOCUMENTS</span>
                <i class="fa-solid fa-folder text-slate-400 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($metrics['total_documents'] ?? 128) }}</div>
            <div class="mt-1 text-xs text-slate-500">Across 22 HCM categories</div>
        </div>

        <!-- Verified Documents -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>VERIFIED</span>
                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($metrics['verified_documents'] ?? 110) }}</div>
            <div class="mt-1 text-xs text-slate-500">Human-verified records</div>
        </div>

        <!-- Pending Verification -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>PENDING REVIEW</span>
                <i class="fa-solid fa-clock text-amber-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($metrics['pending_verification'] ?? 12) }}</div>
            <div class="mt-1 text-xs text-slate-500">Awaiting HR sign-off</div>
        </div>

        <!-- Expiring Within 30 Days -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>EXPIRING SOON</span>
                <i class="fa-solid fa-triangle-exclamation text-rose-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-rose-600">{{ number_format($metrics['expiring_within_30_days'] ?? 4) }}</div>
            <div class="mt-1 text-xs text-slate-500">Passports, visas, licenses</div>
        </div>

        <!-- Completeness Score -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>COMPLETENESS RATE</span>
                <i class="fa-solid fa-chart-pie text-indigo-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-indigo-600">{{ $metrics['average_completeness_rate'] ?? 92.5 }}%</div>
            <div class="mt-1 text-xs text-slate-500">Mandatory files verified</div>
        </div>
    </div>

    <!-- AI Document Metadata & Advisory Card -->
    <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-slate-950 rounded-xl p-6 text-white shadow-md border border-sky-700/40">
        <div class="flex items-start space-x-4">
            <div class="w-10 h-10 rounded-full bg-sky-500/20 border border-sky-400/40 flex items-center justify-center flex-shrink-0 text-sky-300">
                <i class="fa-solid fa-brain-circuit text-lg"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center space-x-2">
                    <h3 class="text-base font-bold text-white">AI Document Intelligence & Classification Advisor</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-sky-500/30 text-sky-200 border border-sky-400/30">Advisory Only</span>
                </div>
                <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                    AI scanned <strong>12 newly submitted files</strong> and extracted document numbers and expiry dates with <strong>94% average confidence</strong>. Zero duplicates or mismatched employee identifiers detected in recent batches.
                </p>
                <div class="mt-3 flex items-center text-xs text-sky-300/80 space-x-4">
                    <span><i class="fa-solid fa-shield-check mr-1 text-sky-400"></i>AI Safety Guardrails: Human review mandatory. Zero autonomous document approvals or employment decisions.</span>
                    <span><i class="fa-solid fa-lock mr-1 text-slate-400"></i>Medical & ER Confidentiality Enforced</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Pending Verifications Queue -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" id="verifications">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Verification Review Queue</h2>
                    <p class="text-xs text-slate-500">Employee-submitted documents requiring review</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                    {{ count($pendingVerifications) }} Pending
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 font-semibold text-left">
                        <tr>
                            <th class="px-6 py-3">Employee</th>
                            <th class="px-6 py-3">Document Title</th>
                            <th class="px-6 py-3">Category</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        @forelse($pendingVerifications as $doc)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-6 py-3 font-medium text-slate-900">{{ $doc->employee?->first_name }} {{ $doc->employee?->last_name }}</td>
                                <td class="px-6 py-3 font-medium text-sky-700">{{ $doc->title }}</td>
                                <td class="px-6 py-3 text-xs text-slate-500">{{ $doc->documentType?->category?->name ?? 'Personal' }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-amber-50 text-amber-700">
                                        {{ $doc->verification_status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-slate-400 text-xs">No documents awaiting verification.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expiring Soon -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" id="expirations">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Upcoming Expirations</h2>
                    <p class="text-xs text-slate-500">Identity cards, visas & professional certifications</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                    {{ count($expiringSoon) }} Expiring
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 font-semibold text-left">
                        <tr>
                            <th class="px-6 py-3">Employee</th>
                            <th class="px-6 py-3">Document Type</th>
                            <th class="px-6 py-3">Doc Number</th>
                            <th class="px-6 py-3">Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        @forelse($expiringSoon as $doc)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-6 py-3 font-medium text-slate-900">{{ $doc->employee?->first_name }} {{ $doc->employee?->last_name }}</td>
                                <td class="px-6 py-3 text-xs text-slate-600">{{ $doc->documentType?->name ?? 'Identity' }}</td>
                                <td class="px-6 py-3 font-mono text-xs text-slate-500">{{ $doc->document_number ?? 'N/A' }}</td>
                                <td class="px-6 py-3 text-xs font-bold text-rose-600">{{ $doc->expiry_date ? $doc->expiry_date->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-slate-400 text-xs">No documents expiring in the next 30 days.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
