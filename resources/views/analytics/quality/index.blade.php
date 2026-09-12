@extends('analytics.layout')

@section('title', 'Data Quality Monitor — Flow HCM')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center">
                <i class="fa-solid fa-shield-halved text-teal-600 mr-3"></i>Data Quality & Integrity Health Score
            </h1>
            <p class="text-sm text-slate-500 mt-1">Continuous integrity monitoring, orphan record detection, and data completeness metrics.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <span class="text-2xl font-black text-teal-600 bg-teal-50 px-4 py-1.5 rounded-xl border border-teal-200">
                {{ $summary['overall_data_quality_score'] ?? 100 }}% Health
            </span>
        </div>
    </div>

    <!-- Data Quality Rules Results Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">Automated Integrity Rule Evaluations</h3>
            <span class="text-xs text-slate-500">{{ count($summary['results'] ?? []) }} Rules Evaluated</span>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase">
                <tr>
                    <th class="px-6 py-3 text-left">Rule Code & Name</th>
                    <th class="px-6 py-3 text-center">Severity</th>
                    <th class="px-6 py-3 text-right">Failing Records</th>
                    <th class="px-6 py-3 text-right">Evaluated Scope</th>
                    <th class="px-6 py-3 text-right">Health Score</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($summary['results'] ?? [] as $res)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900">{{ $res['check_name'] }}</div>
                        <div class="text-xs text-slate-400 font-mono">{{ $res['check_code'] }}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase {{ $res['severity'] === 'error' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $res['severity'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right font-bold {{ $res['failed_records'] > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                        {{ $res['failed_records'] }}
                    </td>
                    <td class="px-6 py-4 text-right text-slate-500">{{ $res['total_evaluated'] }} records</td>
                    <td class="px-6 py-4 text-right font-bold text-teal-600">{{ $res['quality_score'] }}%</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-4 text-center text-slate-400">No data quality rule results found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
