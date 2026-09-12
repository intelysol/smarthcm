@extends('expenses.layout')

@section('title', 'Reimbursements Queue')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Expense Reimbursements Queue</h1>
            <p class="text-sm text-slate-400">Manage payment batches, payroll integrations, and bank settlements for approved claims.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-850 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Batch #</th>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Total Payable</th>
                        <th class="px-6 py-4">Method</th>
                        <th class="px-6 py-4">Payment Ref</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($reimbursements as $reimb)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-mono font-medium text-indigo-400">
                            {{ $reimb->reimbursement_number }}
                        </td>
                        <td class="px-6 py-4 font-medium text-white">
                            <div>{{ $reimb->employee?->first_name }} {{ $reimb->employee?->last_name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $reimb->employee?->employee_number }}</div>
                        </td>
                        <td class="px-6 py-4 text-emerald-400 font-bold">${{ number_format((float)$reimb->total_reimbursement_amount, 2) }}</td>
                        <td class="px-6 py-4 text-slate-300">
                            <span class="px-2 py-0.5 rounded text-xs bg-slate-800 text-slate-200 border border-slate-700">
                                {{ ucwords(str_replace('_', ' ', $reimb->reimbursement_method)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-400">{{ $reimb->payment_reference ?? 'Pending Settlement' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-900/50 text-indigo-300 border border-indigo-700/50">
                                {{ ucfirst($reimb->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">No reimbursement batches found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($reimbursements, 'links'))
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/60">
            {{ $reimbursements->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
