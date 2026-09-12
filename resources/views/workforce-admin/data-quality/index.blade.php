@extends('workforce-admin.layout')

@section('title', 'Data Quality Monitoring')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Workforce Data Quality</h1>
            <p class="text-sm text-slate-400">Multi-dimensional quality scoring across completeness, validity, consistency, and uniqueness.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-slate-400 bg-slate-850/60 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Run #</th>
                        <th class="py-3 px-4">Overall Score</th>
                        <th class="py-3 px-4">Records Scanned</th>
                        <th class="py-3 px-4">Violations Found</th>
                        <th class="py-3 px-4">Scanned At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($runs as $r)
                    <tr class="hover:bg-slate-850/50">
                        <td class="py-3 px-4 font-mono text-emerald-300">{{ $r->run_number }}</td>
                        <td class="py-3 px-4 font-bold text-white">{{ $r->overall_score }}%</td>
                        <td class="py-3 px-4 font-mono">{{ $r->total_records_scanned }}</td>
                        <td class="py-3 px-4 font-mono text-amber-400">{{ $r->total_violations_found }}</td>
                        <td class="py-3 px-4">{{ $r->scanned_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500">No data quality scans executed.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
