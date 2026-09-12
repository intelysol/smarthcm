@extends('payroll.layout')

@section('title', 'Payroll Analytics & Reports')
@section('page_title', 'Payroll Analytics & General Ledger Export')

@section('content')
<div class="space-y-8">
    @if($kpis && $latestRun)
    <!-- Executive KPI Summary -->
    <div class="bg-slate-950 border border-slate-800 rounded-xl p-6">
        <h3 class="text-base font-bold text-white mb-4">Latest Payroll Run Overview ({{ $latestRun->name }})</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="border-l-2 border-emerald-500 pl-4">
                <span class="text-xs text-slate-400">Total Payroll Cost</span>
                <p class="text-xl font-bold text-white font-mono mt-1">${{ number_format($kpis['total_payroll_cost'], 2) }}</p>
            </div>
            <div class="border-l-2 border-blue-500 pl-4">
                <span class="text-xs text-slate-400">Gross Salaries</span>
                <p class="text-xl font-bold text-white font-mono mt-1">${{ number_format($kpis['total_gross'], 2) }}</p>
            </div>
            <div class="border-l-2 border-purple-500 pl-4">
                <span class="text-xs text-slate-400">Employer Benefits Cost</span>
                <p class="text-xl font-bold text-white font-mono mt-1">${{ number_format($kpis['total_employer_cost'], 2) }}</p>
            </div>
            <div class="border-l-2 border-amber-500 pl-4">
                <span class="text-xs text-slate-400">Average Monthly Gross</span>
                <p class="text-xl font-bold text-white font-mono mt-1">${{ number_format($kpis['average_gross_salary'], 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Department Breakdown Table -->
    <div class="bg-slate-950 border border-slate-800 rounded-xl overflow-hidden">
        <div class="p-5 border-b border-slate-800">
            <h4 class="text-base font-semibold text-white">Department Cost Distribution</h4>
        </div>
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900/80 border-b border-slate-800 text-slate-400 text-xs uppercase font-medium">
                <tr>
                    <th class="px-6 py-4">Department</th>
                    <th class="px-6 py-4">Employees</th>
                    <th class="px-6 py-4">Gross Cost</th>
                    <th class="px-6 py-4">Net Payout</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                @foreach($kpis['department_breakdown'] as $dept => $data)
                <tr class="hover:bg-slate-900/50 transition">
                    <td class="px-6 py-4 font-medium text-white">{{ $dept }}</td>
                    <td class="px-6 py-4 font-mono">{{ $data['employees'] }}</td>
                    <td class="px-6 py-4 font-mono text-xs">${{ number_format($data['gross'], 2) }}</td>
                    <td class="px-6 py-4 font-mono text-xs font-bold text-emerald-400">${{ number_format($data['net'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="text-slate-500 text-center py-12">No calculated payroll runs available for analytics.</p>
    @endif
</div>
@endsection
