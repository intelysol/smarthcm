@extends('payroll.layout')

@section('title', 'Payslips')
@section('page_title', 'Employee Payslip Registry')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-white">Generated Payslips</h3>
            <p class="text-sm text-slate-400">View, publish, and distribute employee digital payslips.</p>
        </div>
    </div>

    <div class="bg-slate-950 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900/80 border-b border-slate-800 text-slate-400 text-xs uppercase font-medium">
                <tr>
                    <th class="px-6 py-4">Payslip #</th>
                    <th class="px-6 py-4">Employee</th>
                    <th class="px-6 py-4">Period</th>
                    <th class="px-6 py-4">Gross</th>
                    <th class="px-6 py-4">Tax</th>
                    <th class="px-6 py-4">Net Pay</th>
                    <th class="px-6 py-4">Published</th>
                    <th class="px-6 py-4 text-right">View</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                @forelse($payslips as $ps)
                <tr class="hover:bg-slate-900/50 transition">
                    <td class="px-6 py-4 font-mono text-xs text-white font-medium">{{ $ps->payslip_number }}</td>
                    <td class="px-6 py-4 font-medium text-white">{{ $ps->employee?->fullName() }}</td>
                    <td class="px-6 py-4 text-xs text-slate-400">{{ $ps->period_start->format('M d') }} - {{ $ps->period_end->format('M d, Y') }}</td>
                    <td class="px-6 py-4 font-mono text-xs">${{ number_format($ps->gross_pay, 2) }}</td>
                    <td class="px-6 py-4 font-mono text-xs text-rose-400">${{ number_format($ps->total_tax, 2) }}</td>
                    <td class="px-6 py-4 font-mono text-xs text-emerald-400 font-bold">${{ number_format($ps->net_pay, 2) }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $ps->is_published ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                            {{ $ps->is_published ? 'Published' : 'Draft' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('payroll.payslips.show', $ps) }}" class="text-xs px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 transition">
                            <i class="fa-solid fa-file-invoice mr-1"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-slate-500">No payslips generated yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $payslips->links() }}
    </div>
</div>
@endsection
