@extends('benefits.layout')

@section('title', 'Benefits & Loans Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Benefits, Insurance & Loans Hub</h1>
            <p class="text-sm text-slate-400">Enterprise total rewards, medical insurance claims, retirement funds, and employee loans management.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('benefits.plans.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-700 rounded-lg text-sm font-medium bg-slate-850 hover:bg-slate-800 text-slate-200 transition">
                <i class="fa-solid fa-plus mr-2 text-emerald-400"></i> New Plan
            </a>
            <a href="{{ route('benefits.loans.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 transition">
                <i class="fa-solid fa-file-signature mr-2"></i> Apply for Loan
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Enrollments</span>
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ number_format($summary['active_enrollments'] ?? 0) }}</div>
                <span class="text-xs text-emerald-400"><i class="fa-solid fa-arrow-trend-up mr-1"></i> Active Elections</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Approved Claims</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-shield-heart"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">${{ number_format($summary['total_approved_claims'] ?? 0, 2) }}</div>
                <span class="text-xs text-slate-400">Total Claimed: ${{ number_format($summary['total_claims_amount'] ?? 0, 2) }}</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Disbursed Loans</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ number_format($summary['active_disbursed_loans'] ?? 0) }} Active</div>
                <span class="text-xs text-slate-400">Scheduled payroll recovery</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Retirement Assets</span>
                <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center">
                    <i class="fa-solid fa-piggy-bank"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">${{ number_format($summary['total_retirement_fund_assets'] ?? 0, 2) }}</div>
                <span class="text-xs text-purple-400"><i class="fa-solid fa-vault mr-1"></i> Cumulative Ledger</span>
            </div>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Benefit Enrollments -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-white">Recent Benefit Enrollments</h2>
                <a href="{{ route('benefits.enrollments.index') }}" class="text-xs text-emerald-400 hover:text-emerald-300 font-medium">View All &rarr;</a>
            </div>
            <div class="divide-y divide-slate-800">
                @forelse($recentEnrollments as $enr)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-slate-200">{{ $enr->employee?->first_name }} {{ $enr->employee?->last_name }}</div>
                        <div class="text-xs text-slate-400">{{ $enr->plan?->name }} ({{ $enr->coverage_level }})</div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-900/50 text-emerald-300 border border-emerald-700/50">
                            {{ ucfirst($enr->status) }}
                        </span>
                        <div class="text-xs text-slate-400 mt-1">${{ number_format((float)$enr->employee_contribution, 2) }}/mo</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-sm text-slate-500">No active benefit enrollments recorded.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Employee Loans -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-white">Recent Loan Applications</h2>
                <a href="{{ route('benefits.loans.index') }}" class="text-xs text-emerald-400 hover:text-emerald-300 font-medium">View All &rarr;</a>
            </div>
            <div class="divide-y divide-slate-800">
                @forelse($recentLoans as $loan)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-slate-200">{{ $loan->employee?->first_name }} {{ $loan->employee?->last_name }}</div>
                        <div class="text-xs text-slate-400">{{ $loan->product?->name }} &bull; {{ $loan->application_number }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-white">${{ number_format((float)($loan->approved_amount ?? $loan->requested_amount), 2) }}</div>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-900/50 text-amber-300 border border-amber-700/50">
                            {{ ucfirst($loan->status) }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-sm text-slate-500">No loan applications recorded.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
