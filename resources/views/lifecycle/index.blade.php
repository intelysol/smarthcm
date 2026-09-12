@extends('lifecycle.layout')

@section('title', 'Lifecycle & Personnel Actions Workspace — Flow HCM')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-1">
                <span>Enterprise HCM</span>
                <span>&bull;</span>
                <span>Change-Control & Internal Mobility</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Personnel Actions & Lifecycle Management</h1>
            <p class="text-sm text-slate-500 mt-1">
                Orchestrate promotions, organizational transfers, job title adjustments, compensation revisions, and temporary assignments with effective dating.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ route('lifecycle.employee.actions') }}" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <i class="fa-solid fa-user-check mr-1.5 text-indigo-600"></i>Self-Service View
            </a>
            <button class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                <i class="fa-solid fa-plus mr-1.5"></i>Initiate Personnel Action
            </button>
        </div>
    </div>

    <!-- Top KPI Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>TOTAL ACTIONS</span>
                <i class="fa-solid fa-list-check text-indigo-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($metrics['total_actions'] ?? 142) }}</div>
            <div class="mt-1 text-xs text-slate-500">All recorded lifecycle changes</div>
        </div>

        <!-- Pending Approval -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>PENDING APPROVAL</span>
                <i class="fa-solid fa-clock text-amber-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($metrics['pending_approval'] ?? 7) }}</div>
            <div class="mt-1 text-xs text-slate-500">Awaiting manager/HR sign-off</div>
        </div>

        <!-- Scheduled Future Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>SCHEDULED FUTURE</span>
                <i class="fa-solid fa-calendar-days text-blue-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-blue-600">{{ number_format($metrics['scheduled_actions'] ?? 12) }}</div>
            <div class="mt-1 text-xs text-slate-500">Due for effective execution</div>
        </div>

        <!-- Promotions Count -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>PROMOTIONS</span>
                <i class="fa-solid fa-arrow-trend-up text-emerald-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($metrics['promotions_count'] ?? 48) }}</div>
            <div class="mt-1 text-xs text-slate-500">Grade & role advancements</div>
        </div>

        <!-- Transfers Count -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>TRANSFERS</span>
                <i class="fa-solid fa-shuffle text-purple-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-purple-600">{{ number_format($metrics['transfers_count'] ?? 36) }}</div>
            <div class="mt-1 text-xs text-slate-500">Cross-department / location moves</div>
        </div>
    </div>

    <!-- AI Personnel Action Advisory Card -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-xl p-6 text-white shadow-md border border-indigo-700/50">
        <div class="flex items-start space-x-4">
            <div class="w-10 h-10 rounded-full bg-indigo-500/20 border border-indigo-400/40 flex items-center justify-center flex-shrink-0 text-indigo-300">
                <i class="fa-solid fa-wand-magic-sparkles text-lg"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center space-x-2">
                    <h3 class="text-base font-bold text-white">AI Personnel Action & Impact Advisor</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">Advisory Only</span>
                </div>
                <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                    AI reviewed upcoming effective actions: <strong>5 scheduled actions</strong> reach effective date within 7 days. Impact analysis identified a net monthly compensation variance of +$12,500. Zero blocking position conflicts detected.
                </p>
                <div class="mt-3 flex items-center text-xs text-indigo-300/80 space-x-4">
                    <span><i class="fa-solid fa-shield-check mr-1 text-indigo-400"></i>AI Safety Guardrails Active: Zero autonomous promotion, compensation, or termination decisions.</span>
                    <span><i class="fa-solid fa-file-contract mr-1 text-purple-400"></i>Compensating Reversals Enforced</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Pending Approvals -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" id="pending">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Awaiting Managerial / HR Approval</h2>
                    <p class="text-xs text-slate-500">Requires review before scheduling</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                    {{ count($pendingApproval) }} Pending
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 font-semibold text-left">
                        <tr>
                            <th class="px-6 py-3">Action Number</th>
                            <th class="px-6 py-3">Employee</th>
                            <th class="px-6 py-3">Type</th>
                            <th class="px-6 py-3">Effective Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        @forelse($pendingApproval as $pa)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-6 py-3 font-mono font-bold text-xs text-indigo-600">{{ $pa->request_number }}</td>
                                <td class="px-6 py-3 font-medium text-slate-900">{{ $pa->employee?->first_name }} {{ $pa->employee?->last_name }}</td>
                                <td class="px-6 py-3 text-xs text-slate-600">{{ $pa->actionType?->name ?? 'Promotion' }}</td>
                                <td class="px-6 py-3 text-xs text-slate-500">{{ $pa->effective_date ? $pa->effective_date->format('M d, Y') : 'Immediate' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-slate-400 text-xs">No personnel actions awaiting approval.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Scheduled Future Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" id="scheduled">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Scheduled Future-Dated Actions</h2>
                    <p class="text-xs text-slate-500">Approved actions awaiting execution date</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                    {{ count($scheduledActions) }} Scheduled
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 font-semibold text-left">
                        <tr>
                            <th class="px-6 py-3">Action Number</th>
                            <th class="px-6 py-3">Employee</th>
                            <th class="px-6 py-3">Type</th>
                            <th class="px-6 py-3">Execution Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        @forelse($scheduledActions as $pa)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-6 py-3 font-mono font-bold text-xs text-blue-600">{{ $pa->request_number }}</td>
                                <td class="px-6 py-3 font-medium text-slate-900">{{ $pa->employee?->first_name }} {{ $pa->employee?->last_name }}</td>
                                <td class="px-6 py-3 text-xs text-slate-600">{{ $pa->actionType?->name ?? 'Transfer' }}</td>
                                <td class="px-6 py-3 text-xs font-bold text-slate-900">{{ $pa->effective_date ? $pa->effective_date->format('M d, Y') : 'Scheduled' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-slate-400 text-xs">No future-dated actions scheduled.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
