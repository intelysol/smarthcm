@extends('productivity.layout')

@section('title', 'Finance & Labor Cost Economics')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Finance & Labor Cost Economics</h1>
            <p class="text-sm text-slate-500 mt-1">Direct linkage to Epic 2.49 Workforce Cost — Unit labor cost, output per workforce dollar, and economic variance</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800">
                <i class="fa-solid fa-coins mr-1.5"></i>Finance Actuals Authoritative
            </span>
        </div>
    </div>

    <!-- Finance KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Workforce Cost</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">$266,600 <span class="text-xs font-normal text-slate-500">USD</span></div>
            <div class="text-xs text-slate-500 font-medium mt-1">Direct labor + Burden + Overtime</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Avg Cost per Unit</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">$2.14 <span class="text-xs font-normal text-slate-500">/ unit</span></div>
            <div class="text-xs text-emerald-600 font-medium mt-1">-$0.12 vs Budget ($2.26)</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Output per Labor Dollar</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">0.467 <span class="text-xs font-normal text-slate-500">units/$</span></div>
            <div class="text-xs text-emerald-600 font-medium mt-1">+5.2% economic efficiency</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Overtime Cost Ratio</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">7.4%</div>
            <div class="text-xs text-slate-500 font-medium mt-1">Of total direct wage cost</div>
        </div>
    </div>

    <!-- Financial Reconciliation & Scenario Modeling -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-base font-semibold text-slate-900 mb-3">Unit Cost Breakdown by Operational Facet</h2>
            <div class="space-y-4 text-sm">
                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <span class="text-slate-600 font-medium">Direct Wages Cost</span>
                    <span class="text-slate-900 font-semibold">$1.42 / unit (66.3%)</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <span class="text-slate-600 font-medium">Labor Burden & Benefits (Epic 2.49)</span>
                    <span class="text-slate-900 font-semibold">$0.45 / unit (21.0%)</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <span class="text-slate-600 font-medium">Overtime Premium Drag</span>
                    <span class="text-slate-900 font-semibold">$0.16 / unit (7.5%)</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-slate-600 font-medium">Absence Substitution Overhead</span>
                    <span class="text-slate-900 font-semibold">$0.11 / unit (5.2%)</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
            <h2 class="text-base font-semibold text-slate-900">What-If Workforce Scenario Simulation</h2>
            <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 text-sm space-y-2">
                <div class="font-semibold text-slate-800">Scenario A: +5 Skilled Operators in Ops</div>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <div><span class="text-slate-500">Cost Delta:</span> <div class="font-bold text-slate-900">+$25,000</div></div>
                    <div><span class="text-slate-500">Output Delta:</span> <div class="font-bold text-emerald-600">+14,500 units</div></div>
                    <div><span class="text-slate-500">Projected Unit Cost:</span> <div class="font-bold text-indigo-600">$2.07 (-$0.07)</div></div>
                </div>
                <div class="text-xs text-slate-500 pt-1 border-t border-slate-200">
                    Expected ROI: <strong>+18.4%</strong> with 4.5 months payback period.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
