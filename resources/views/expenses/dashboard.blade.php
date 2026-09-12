@extends('expenses.layout')

@section('title', 'Expenses & Travel Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Expense & Travel Hub</h1>
            <p class="text-sm text-slate-400">Manage business travel requests, travel advances, multi-currency expense claims, and policy validations.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('expenses.travel.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-700 rounded-lg text-sm font-medium bg-slate-850 hover:bg-slate-800 text-slate-200 transition">
                <i class="fa-solid fa-plus mr-2 text-indigo-400"></i> New Travel Request
            </a>
            <a href="{{ route('expenses.claims.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition">
                <i class="fa-solid fa-receipt mr-2"></i> Submit Expense Claim
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Approved Expenses</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-check-double"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">${{ number_format($summary['approved_claims_amount'] ?? 0, 2) }}</div>
                <span class="text-xs text-slate-400">Total Claims: {{ number_format($summary['total_claims'] ?? 0) }}</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending Approvals</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">${{ number_format($summary['pending_approval_amount'] ?? 0, 2) }}</div>
                <span class="text-xs text-amber-400"><i class="fa-solid fa-hourglass-half mr-1"></i> Manager / Finance Review</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Travel Authorized</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                    <i class="fa-solid fa-plane-circle-check"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">${{ number_format($summary['total_travel_cost'] ?? 0, 2) }}</div>
                <span class="text-xs text-indigo-400">{{ number_format($summary['active_travel_count'] ?? 0) }} active trips</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Unsettled Advances</span>
                <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">${{ number_format($summary['outstanding_advances'] ?? 0, 2) }}</div>
                <span class="text-xs text-slate-400">Total Reimbursed: ${{ number_format($summary['total_reimbursed'] ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Expense Claims -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-white">Recent Expense Claims</h2>
                <a href="{{ route('expenses.claims.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">View All &rarr;</a>
            </div>
            <div class="divide-y divide-slate-800">
                @forelse($recentClaims as $claim)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-slate-200">{{ $claim->title }}</div>
                        <div class="text-xs text-slate-400 font-mono">{{ $claim->claim_number }} &bull; {{ $claim->employee?->first_name }} {{ $claim->employee?->last_name }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-white">${{ number_format((float)$claim->claimed_total, 2) }}</div>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-800 text-slate-300 border border-slate-700">
                            {{ ucfirst($claim->status) }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-sm text-slate-500">No expense claims recorded.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Business Travel -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-white">Recent Travel Requests</h2>
                <a href="{{ route('expenses.travel.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">View All &rarr;</a>
            </div>
            <div class="divide-y divide-slate-800">
                @forelse($recentTravel as $trip)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-slate-200">{{ $trip->destination }}</div>
                        <div class="text-xs text-slate-400 font-mono">{{ $trip->request_number }} &bull; {{ $trip->employee?->first_name }} {{ $trip->employee?->last_name }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-indigo-400">${{ number_format((float)$trip->estimated_cost, 2) }}</div>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-indigo-900/50 text-indigo-300 border border-indigo-700/50">
                            {{ ucfirst($trip->status) }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-sm text-slate-500">No travel requests recorded.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
