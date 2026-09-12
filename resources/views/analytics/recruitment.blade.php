@extends('analytics.layout')

@section('title', 'Talent Acquisition & Recruitment Analytics — Flow HCM')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center">
                <i class="fa-solid fa-bullseye text-amber-600 mr-3"></i>Talent Acquisition & Succession Analytics
            </h1>
            <p class="text-sm text-slate-500 mt-1">Recruitment funnel progression, offer acceptance velocity, time to hire, and talent bench strength.</p>
        </div>
    </div>

    <!-- Funnel Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Average Time to Hire</span>
            <div class="mt-3 text-3xl font-extrabold text-amber-600">{{ $funnel['average_time_to_hire_days'] ?? 0 }} days</div>
            <p class="text-xs text-slate-500 mt-1">From requisition opening to accepted offer</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Offer Acceptance Rate</span>
            <div class="mt-3 text-3xl font-extrabold text-emerald-600">{{ $funnel['offer_acceptance_rate_percent'] ?? 0 }}%</div>
            <p class="text-xs text-slate-500 mt-1">Candidate acceptance ratio</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Succession Coverage</span>
            <div class="mt-3 text-3xl font-extrabold text-indigo-600">{{ $talent['succession_coverage_percent'] ?? 0 }}%</div>
            <p class="text-xs text-slate-500 mt-1">{{ $talent['ready_now_successors_count'] ?? 0 }} Ready-Now Successors Identified</p>
        </div>
    </div>

    <!-- Funnel Stage Details -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden p-6">
        <h3 class="font-bold text-slate-900 text-sm mb-4">Hiring Funnel Stage Conversions</h3>
        <div class="space-y-4">
            @foreach($funnel['funnel'] ?? [] as $stage)
            <div>
                <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                    <span>{{ $stage['stage'] }}</span>
                    <span>{{ $stage['count'] }} Candidates ({{ $stage['conversion_rate'] }}%)</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-3">
                    <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-3 rounded-full" style="width: {{ min(100, $stage['conversion_rate']) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
