@extends('self-service.layout')

@section('title', 'Manager Self-Service (MSS) Portal')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Manager Self-Service (MSS) Dashboard</h1>
            <p class="text-sm text-slate-400">Team oversight, pending multi-module approvals, team attendance &amp; requests inbox.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-teal-900/50 text-teal-300 border border-teal-700/50">
                Direct Reports: {{ $team_size }}
            </span>
        </div>
    </div>

    <!-- Manager Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Direct Reports</div>
            <div class="text-3xl font-bold text-white mt-2">{{ $team_size }}</div>
            <div class="text-xs text-slate-400 mt-1">Supervised team members</div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Service Request Approvals</div>
            <div class="text-3xl font-bold text-amber-400 mt-2">{{ $pending_service_approvals_count }}</div>
            <div class="text-xs text-slate-400 mt-1">Pending HR approvals</div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Expense Approvals</div>
            <div class="text-3xl font-bold text-teal-400 mt-2">{{ $pending_expense_approvals_count }}</div>
            <div class="text-xs text-slate-400 mt-1">Pending expense claim reviews</div>
        </div>
    </div>

    <!-- Team Member Roster -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-800 bg-slate-850 flex items-center justify-between">
            <h2 class="text-base font-bold text-white">My Direct Reports Roster</h2>
            <span class="text-xs text-slate-400 font-mono">{{ count($team_roster) }} Members</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-900 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3">Employee</th>
                        <th class="px-6 py-3">Designation</th>
                        <th class="px-6 py-3">Department</th>
                        <th class="px-6 py-3">Joining Date</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($team_roster as $member)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-medium text-white">
                            <div>{{ $member->first_name }} {{ $member->last_name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $member->employee_code ?? $member->employee_number }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-300">{{ $member->designation?->designation_name ?? 'Associate' }}</td>
                        <td class="px-6 py-4 text-slate-300">{{ $member->department?->department_name ?? 'Corporate' }}</td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-400">{{ $member->joining_date?->format('Y-m-d') ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-950 text-emerald-400 border border-emerald-800/50">
                                Active
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">No direct reports assigned.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
