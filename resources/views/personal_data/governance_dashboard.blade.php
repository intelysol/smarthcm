@extends('personal_data.layout')

@section('title', 'Master Data Governance Dashboard — Flow HCM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Master Data Governance & Quality Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">Monitor workforce personal data completeness, validity, verification confidence and active governance workflows.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-300">
                <i class="fa-solid fa-circle text-emerald-500 text-xs mr-1.5"></i>Governance Engine Active
            </span>
        </div>
    </div>

    <!-- Governance KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Overall Score -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Overall Data Quality</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-0.5">{{ $qualitySummary['avg_overall_score'] ?? 0 }}%</h3>
                <span class="text-xs text-slate-500">{{ $qualitySummary['total_employees_assessed'] ?? 0 }} employees scored</span>
            </div>
        </div>

        <!-- Completeness -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Completeness</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-0.5">{{ $qualitySummary['avg_completeness_score'] ?? 0 }}%</h3>
                <span class="text-xs text-slate-500">Core personal info & contacts</span>
            </div>
        </div>

        <!-- Validity & Verification -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-certificate"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Verified Records</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-0.5">{{ $qualitySummary['avg_verification_score'] ?? 0 }}%</h3>
                <span class="text-xs text-slate-500">Validity: {{ $qualitySummary['avg_validity_score'] ?? 0 }}%</span>
            </div>
        </div>

        <!-- Quality Issues -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Data Issues</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-0.5">{{ $qualitySummary['total_issues'] ?? 0 }}</h3>
                <span class="text-xs text-rose-600 font-medium">Requires employee/HR action</span>
            </div>
        </div>
    </div>

    <!-- Governance Queue Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bank Change Requests Queue -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-building-columns text-emerald-600"></i>
                    <h2 class="text-base font-bold text-slate-800">Payroll Bank Detail Requests</h2>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    {{ $pendingBankRequests }} Pending
                </span>
            </div>
            <p class="text-xs text-slate-500">
                Direct-deposit and bank changes submitted by employees are staged here for controlled review and secure synchronization with the Payroll disbursement engine.
            </p>
            <div class="flex items-center justify-between pt-2">
                <span class="text-xs text-slate-400">Account numbers are masked and encrypted</span>
                <a href="{{ route('personal-data.change-requests') }}" class="inline-flex items-center text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                    Review Queue <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Master Data Change Requests Queue -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-code-pull-request text-teal-600"></i>
                    <h2 class="text-base font-bold text-slate-800">Personal Data Change Requests</h2>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">
                    {{ $pendingDataChanges }} Pending
                </span>
            </div>
            <p class="text-xs text-slate-500">
                Controlled self-service profile and address change requests awaiting manager or HR administrator approval with side-by-side verification.
            </p>
            <div class="flex items-center justify-between pt-2">
                <span class="text-xs text-slate-400">Effective dating & audit trail enforced</span>
                <a href="{{ route('personal-data.change-requests') }}" class="inline-flex items-center text-xs font-semibold text-teal-600 hover:text-teal-700">
                    Review Changes <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Governance Actions & Integrity Checks -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
        <h2 class="text-base font-bold text-slate-800 flex items-center space-x-2">
            <i class="fa-solid fa-shield-halved text-slate-700"></i>
            <span>Data Hygiene & Master Governance Operations</span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
            <div class="border border-slate-200 rounded-lg p-4 bg-slate-50">
                <h4 class="text-sm font-semibold text-slate-800">Advisory Duplicate Detection</h4>
                <p class="text-xs text-slate-500 mt-1">Multi-factor heuristic duplicate scan (National ID, Email, Mobile, DOB). Automated merges strictly prohibited.</p>
            </div>
            <div class="border border-slate-200 rounded-lg p-4 bg-slate-50">
                <h4 class="text-sm font-semibold text-slate-800">Bulk Upload & Dry-Run Preview</h4>
                <p class="text-xs text-slate-500 mt-1">Upload CSV or JSON batches with instant pre-commit validation to catch malformed identifiers or missing references.</p>
            </div>
            <div class="border border-slate-200 rounded-lg p-4 bg-slate-50">
                <h4 class="text-sm font-semibold text-slate-800">Identifier Expiration Monitor</h4>
                <p class="text-xs text-slate-500 mt-1">Proactively monitors passports, visas, work permits and driver's licenses reaching expiration within 30-60 days.</p>
            </div>
        </div>
    </div>
</div>
@endsection
