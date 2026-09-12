@extends('payroll.layout')

@section('title', 'Bank Payment Batches')
@section('page_title', 'Bank Payment Batches & Direct Disbursement')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-white">Payment Batches</h3>
            <p class="text-sm text-slate-400">Generate, review, and export bank transfer clearing files (CSV / NACHA / SEPA).</p>
        </div>
    </div>

    <div class="bg-slate-950 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900/80 border-b border-slate-800 text-slate-400 text-xs uppercase font-medium">
                <tr>
                    <th class="px-6 py-4">Batch #</th>
                    <th class="px-6 py-4">Payroll Run</th>
                    <th class="px-6 py-4">Method</th>
                    <th class="px-6 py-4">Format</th>
                    <th class="px-6 py-4">Records</th>
                    <th class="px-6 py-4">Total Amount</th>
                    <th class="px-6 py-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                @forelse($batches as $b)
                <tr class="hover:bg-slate-900/50 transition">
                    <td class="px-6 py-4 font-mono text-xs text-white font-medium">{{ $b->batch_number }}</td>
                    <td class="px-6 py-4 font-medium text-white">{{ $b->run?->name }}</td>
                    <td class="px-6 py-4 text-xs">{{ ucfirst(str_replace('_', ' ', $b->payment_method)) }}</td>
                    <td class="px-6 py-4 font-mono text-xs uppercase">{{ $b->bank_format }}</td>
                    <td class="px-6 py-4 font-mono">{{ $b->total_records }}</td>
                    <td class="px-6 py-4 font-mono text-xs text-emerald-400 font-bold">${{ number_format($b->total_amount, 2) }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2.5 py-1 rounded text-xs font-semibold {{ $b->status === 'paid' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-800 text-slate-300' }}">
                            {{ strtoupper($b->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-slate-500">No payment batches generated.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $batches->links() }}
    </div>
</div>
@endsection
