@extends('layouts.employee_relations')

@section('title', 'HR Case Dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">Employee Relations Dashboard</h1>
            <p class="text-sm text-slate-400">Real-time overview of HR cases, investigations, SLAs, and compliance.</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('er.admin.intake') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-lg text-sm transition shadow-lg shadow-indigo-600/30 flex items-center">
                <i class="fa-solid fa-plus mr-2"></i> Log New Case
            </a>
            <a href="{{ route('er.admin.cases') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium rounded-lg text-sm transition flex items-center border border-slate-700">
                <i class="fa-solid fa-list-check mr-2"></i> View Queue
            </a>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Active Cases</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                    <i class="fa-solid fa-folder-open"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-white">{{ $metrics['open_cases'] ?? 0 }}</div>
                <div class="text-xs text-slate-400 mt-1">Out of {{ $metrics['total_cases'] ?? 0 }} total cases</div>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">SLA Compliance</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-emerald-400">{{ $metrics['sla_compliance_rate'] ?? 100 }}%</div>
                <div class="text-xs text-slate-400 mt-1">Target >= 95% response/triage</div>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Avg Resolution Time</span>
                <span class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                    <i class="fa-solid fa-calendar-check"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-white">{{ $metrics['average_resolution_days'] ?? 0 }} <span class="text-sm text-slate-400 font-normal">days</span></div>
                <div class="text-xs text-slate-400 mt-1">Target <= 14 days</div>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Action Completion</span>
                <span class="w-8 h-8 rounded-lg bg-violet-500/10 text-violet-400 flex items-center justify-center">
                    <i class="fa-solid fa-list-check"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-3xl font-extrabold text-violet-400">{{ $metrics['corrective_action_completion_rate'] ?? 0 }}%</div>
                <div class="text-xs text-slate-400 mt-1">Corrective actions verified</div>
            </div>
        </div>
    </div>

    <!-- Recent Cases Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h2 class="text-base font-semibold text-white">Recent Case Activity</h2>
            <a href="{{ route('er.admin.cases') }}" class="text-xs font-medium text-indigo-400 hover:text-indigo-300">View All Cases &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-left text-sm text-slate-300">
                <thead class="bg-slate-950 text-xs uppercase tracking-wider text-slate-400 font-medium">
                    <tr>
                        <th class="px-6 py-3">Case Number</th>
                        <th class="px-6 py-3">Title & Type</th>
                        <th class="px-6 py-3">Subject</th>
                        <th class="px-6 py-3">Priority</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Opened</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($recentCases as $case)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-mono font-medium text-white">{{ $case->case_number }}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-white">{{ $case->title }}</div>
                            <div class="text-xs text-slate-400">{{ $case->caseType?->name }}</div>
                        </td>
                        <td class="px-6 py-4">{{ $case->subjectEmployee ? ($case->subjectEmployee->first_name . ' ' . $case->subjectEmployee->last_name) : $case->subject_name }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-800 text-slate-200">
                                {{ ucfirst($case->priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                {{ ucfirst(str_replace('_', ' ', $case->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-400">{{ $case->opened_at->diffForHumans() }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('er.admin.case', $case) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-indigo-400 hover:text-indigo-300 font-medium rounded text-xs border border-slate-700 transition">
                                Open Case
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-500">No recent Employee Relations cases found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
