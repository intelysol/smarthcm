@extends('layouts.employee_relations')

@section('title', 'ER Case Queue')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">Employee Relations Case Queue</h1>
            <p class="text-sm text-slate-400">All cases currently undergoing intake, triage, investigation, decision, or appeal.</p>
        </div>
        <a href="{{ route('er.admin.intake') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-lg text-sm transition flex items-center shadow-lg shadow-indigo-600/30">
            <i class="fa-solid fa-plus mr-2"></i> Log New Case
        </a>
    </div>

    <!-- Case Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-left text-sm text-slate-300">
                <thead class="bg-slate-950 text-xs uppercase tracking-wider text-slate-400 font-medium">
                    <tr>
                        <th class="px-6 py-3">Case ID</th>
                        <th class="px-6 py-3">Title & Classification</th>
                        <th class="px-6 py-3">Subject</th>
                        <th class="px-6 py-3">Priority / Severity</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Assigned Team</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($cases as $case)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-mono font-medium text-white">{{ $case->case_number }}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-white">{{ $case->title }}</div>
                            <div class="text-xs text-indigo-400">{{ $case->caseType?->name }}</div>
                        </td>
                        <td class="px-6 py-4">{{ $case->subjectEmployee ? ($case->subjectEmployee->first_name . ' ' . $case->subjectEmployee->last_name) : $case->subject_name }}</td>
                        <td class="px-6 py-4 text-xs">
                            <span class="font-medium text-slate-200">{{ ucfirst($case->priority) }}</span> /
                            <span class="text-slate-400">{{ ucfirst($case->severity) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                {{ ucfirst(str_replace('_', ' ', $case->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-400">
                            @foreach($case->assignments as $asgn)
                                <div>{{ $asgn->user?->name }} ({{ ucfirst($asgn->role) }})</div>
                            @endforeach
                            @if($case->assignments->isEmpty())
                                <span class="italic text-slate-500">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('er.admin.case', $case) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-indigo-400 font-medium rounded text-xs border border-slate-700 transition">
                                Open Workspace
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-500">No cases found in queue.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
