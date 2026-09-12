@extends('payroll.layout')

@section('title', 'Payroll Adjustments')
@section('page_title', 'Payroll Adjustments & Arrears')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-white">Manual Adjustments & Arrears</h3>
            <p class="text-sm text-slate-400">Review, approve, and track one-off salary earnings, deductions, and retroactive back-pay.</p>
        </div>
    </div>

    <div class="bg-slate-950 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900/80 border-b border-slate-800 text-slate-400 text-xs uppercase font-medium">
                <tr>
                    <th class="px-6 py-4">Employee</th>
                    <th class="px-6 py-4">Title / Code</th>
                    <th class="px-6 py-4">Type</th>
                    <th class="px-6 py-4">Amount</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Requested By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                @forelse($adjustments as $adj)
                <tr class="hover:bg-slate-900/50 transition">
                    <td class="px-6 py-4 font-medium text-white">{{ $adj->employee?->fullName() }}</td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-white">{{ $adj->title }}</div>
                        <div class="font-mono text-xs text-slate-400">{{ $adj->code }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $adj->adjustment_type === 'earning' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                            {{ ucfirst($adj->adjustment_type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-mono text-xs font-semibold">${{ number_format($adj->amount, 2) }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2.5 py-1 rounded text-xs font-semibold {{ $adj->status === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                            {{ strtoupper($adj->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-400">{{ $adj->requester?->name }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">No adjustments requested.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $adjustments->links() }}
    </div>
</div>
@endsection
