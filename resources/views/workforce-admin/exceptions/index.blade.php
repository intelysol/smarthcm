@extends('workforce-admin.layout')

@section('title', 'Operational Exceptions')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">HCM Operational Exceptions</h1>
            <p class="text-sm text-slate-400">Investigate data-quality, integration, compliance, and payroll exceptions across HCM domains.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-slate-400 bg-slate-850/60 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Exception #</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Domain</th>
                        <th class="py-3 px-4">Severity</th>
                        <th class="py-3 px-4">Employee</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Detected</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($exceptions as $e)
                    <tr class="hover:bg-slate-850/50">
                        <td class="py-3 px-4 font-mono text-cyan-300">{{ $e->exception_number }}</td>
                        <td class="py-3 px-4 font-semibold text-white">{{ $e->exception_type }}</td>
                        <td class="py-3 px-4">{{ ucfirst($e->domain) }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $e->severity === 'critical' ? 'bg-rose-500/10 text-rose-400' : 'bg-amber-500/10 text-amber-400' }}">
                                {{ strtoupper($e->severity) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">{{ $e->employee ? $e->employee->first_name . ' ' . $e->employee->last_name : 'N/A' }}</td>
                        <td class="py-3 px-4 font-medium">{{ ucfirst($e->status->value) }}</td>
                        <td class="py-3 px-4">{{ $e->detected_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">No operational exceptions logged.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
