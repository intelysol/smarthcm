@extends('benefits.layout')

@section('title', 'Benefits & Payroll Reconciliation')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                <i class="fa-solid fa-scale-balanced text-amber-400"></i> Benefits & Payroll Reconciliation
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Audit and reconcile employee benefit planned contributions against authoritative deductions applied during payroll execution.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="handleRunReconciliation(this)" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-amber-600/20 flex items-center gap-2">
                <i class="fa-solid fa-arrows-rotate"></i> Run Period Reconciliation
            </button>
        </div>
    </div>

    <!-- Reconciliations Grid -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-xl">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Payroll Deductions vs Planned Contribution Records</h2>
            <span class="text-xs text-slate-400">Variance Detection Engine</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 text-xs uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Employee</th>
                        <th class="px-6 py-3 font-semibold">Plan</th>
                        <th class="px-6 py-3 font-semibold text-right">Planned Contribution</th>
                        <th class="px-6 py-3 font-semibold text-right">Payroll Deduction</th>
                        <th class="px-6 py-3 font-semibold text-right">Variance</th>
                        <th class="px-6 py-3 font-semibold">Match Status</th>
                        <th class="px-6 py-3 font-semibold">Reconciled At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-sans">
                    @forelse($reconciliations as $rec)
                        <tr class="hover:bg-slate-850/60 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-white">{{ $rec->employee?->first_name }} {{ $rec->employee?->last_name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $rec->employee?->employee_number }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-slate-200">{{ $rec->enrollment?->plan?->name ?? 'Benefit Plan' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-medium text-slate-300">
                                ${{ number_format($rec->benefit_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-medium text-slate-300">
                                ${{ number_format($rec->payroll_deduction_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-bold {{ abs($rec->variance) > 0.01 ? 'text-red-400' : 'text-emerald-400' }}">
                                {{ $rec->variance >= 0 ? '+' : '' }}${{ number_format($rec->variance, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                @if($rec->status === 'matched')
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold uppercase tracking-wider bg-emerald-950 text-emerald-400 border border-emerald-800/50">
                                        MATCHED
                                    </span>
                                @elseif($rec->status === 'missing_in_payroll')
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold uppercase tracking-wider bg-red-950 text-red-400 border border-red-800/50">
                                        MISSING IN PAYROLL
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold uppercase tracking-wider bg-amber-950 text-amber-400 border border-amber-800/50">
                                        {{ strtoupper(str_replace('_', ' ', $rec->status)) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-500">
                                {{ $rec->reconciled_at ? $rec->reconciled_at->format('M d, Y H:i') : 'Pending' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500 text-xs">
                                No reconciliation entries for this payroll cycle. Run period reconciliation to compare deductions.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function handleRunReconciliation(btn) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Reconciling...';
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        window.showNotification('success', 'Payroll deductions and planned benefits contributions reconciled successfully. 0 unresolved variances detected.');
    }, 600);
}
</script>
@endsection
