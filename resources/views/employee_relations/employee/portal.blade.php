@extends('layouts.employee_relations')

@section('title', 'My Employee Relations Cases')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">My Employee Relations Portal</h1>
            <p class="text-sm text-slate-400">Track cases you submitted or are named in, review notices, and acknowledge corrective actions.</p>
        </div>
        <a href="{{ route('er.employee.report') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-lg text-sm transition flex items-center shadow-lg shadow-indigo-600/30">
            <i class="fa-solid fa-plus mr-2"></i> Submit New Report
        </a>
    </div>

    <!-- My Cases List -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-left text-sm text-slate-300">
                <thead class="bg-slate-950 text-xs uppercase tracking-wider text-slate-400 font-medium">
                    <tr>
                        <th class="px-6 py-3">Case ID</th>
                        <th class="px-6 py-3">Topic & Classification</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Submitted Date</th>
                        <th class="px-6 py-3 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($myCases as $case)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-mono font-medium text-white">{{ $case->case_number }}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-white">{{ $case->title }}</div>
                            <div class="text-xs text-indigo-400">{{ $case->caseType?->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                {{ ucfirst(str_replace('_', ' ', $case->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-400">{{ $case->opened_at->toFormattedDateString() }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('er.employee.case', $case) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-indigo-400 font-medium rounded text-xs border border-slate-700 transition">
                                View Case
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">You currently have no open Employee Relations cases.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
