@extends('payroll.layout')

@section('title', 'Payroll Runs')
@section('page_title', 'Payroll Calculation Runs')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-white">Payroll Runs & Batches</h3>
            <p class="text-sm text-slate-400">Manage regular, off-cycle, and bonus salary calculation runs.</p>
        </div>
    </div>

    <div class="bg-slate-950 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900/80 border-b border-slate-800 text-slate-400 text-xs uppercase font-medium">
                <tr>
                    <th class="px-6 py-4">Run #</th>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Type</th>
                    <th class="px-6 py-4">Employees</th>
                    <th class="px-6 py-4">Gross Total</th>
                    <th class="px-6 py-4">Net Total</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                @forelse($runs as $run)
                <tr class="hover:bg-slate-900/50 transition">
                    <td class="px-6 py-4 font-mono text-xs text-white font-medium">{{ $run->run_number }}</td>
                    <td class="px-6 py-4 font-medium text-white">{{ $run->name }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs bg-slate-800 text-slate-300">
                            {{ ucfirst($run->run_type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-mono">{{ $run->employee_count }}</td>
                    <td class="px-6 py-4 font-mono text-xs">${{ number_format($run->gross_total, 2) }}</td>
                    <td class="px-6 py-4 font-mono text-xs text-emerald-400 font-semibold">${{ number_format($run->net_total, 2) }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2.5 py-1 rounded text-xs font-semibold {{ $run->status === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                            {{ strtoupper($run->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('payroll.runs.show', $run) }}" class="text-xs px-3 py-1.5 rounded bg-emerald-600/20 text-emerald-400 hover:bg-emerald-600/30 border border-emerald-500/30 transition">
                            <i class="fa-solid fa-eye mr-1"></i> Review
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-slate-500">No payroll runs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $runs->links() }}
    </div>
</div>
@endsection
