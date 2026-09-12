@extends('productivity.layout')

@section('title', 'Executive Workforce Productivity Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Executive Workforce Productivity & ROI</h1>
            <p class="text-sm text-slate-500 mt-1">Enterprise-wide productivity, labor efficiency, performance-to-cost, and ROI intelligence</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                <i class="fa-solid fa-calendar mr-1.5"></i>Period: {{ date('F Y') }}
            </span>
            <a href="/api/v1/hcm/productivity/export/csv" class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-slate-900 text-white hover:bg-slate-800 transition">
                <i class="fa-solid fa-download mr-1.5"></i>Export CSV
            </a>
        </div>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Workforce Output</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-cubes text-sm"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900">124,580 <span class="text-xs font-normal text-slate-500">units</span></div>
                <div class="flex items-center mt-1 text-xs text-emerald-600 font-medium">
                    <i class="fa-solid fa-arrow-up mr-1"></i>+8.4% vs last period
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Productivity Rate</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-gauge-high text-sm"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900">14.2 <span class="text-xs font-normal text-slate-500">output/hr</span></div>
                <div class="flex items-center mt-1 text-xs text-blue-600 font-medium">
                    <i class="fa-solid fa-check-circle mr-1"></i>Productive hours base
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Labor Cost / Output</span>
                <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i class="fa-solid fa-dollar-sign text-sm"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900">$2.14 <span class="text-xs font-normal text-slate-500">per unit</span></div>
                <div class="flex items-center mt-1 text-xs text-emerald-600 font-medium">
                    <i class="fa-solid fa-arrow-down mr-1"></i>-$0.12 efficiency gain
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Labor Utilization</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-stopwatch text-sm"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900">86.8%</div>
                <div class="flex items-center mt-1 text-xs text-slate-500 font-medium">
                    Available capacity active
                </div>
            </div>
        </div>
    </div>

    <!-- Core Sections: Productivity by Department & Workforce ROI Highlights -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-slate-900">Departmental Productivity & Cost Economics</h2>
                <span class="text-xs text-slate-400">Aggregated via Epic 2.49</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-400 uppercase bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Department</th>
                            <th class="py-3 px-4">Output</th>
                            <th class="py-3 px-4">Rate (Units/Hr)</th>
                            <th class="py-3 px-4">Cost/Unit</th>
                            <th class="py-3 px-4">Utilization</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="py-3.5 px-4 font-medium text-slate-900">Manufacturing Ops</td>
                            <td class="py-3.5 px-4">58,200</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">18.4</td>
                            <td class="py-3.5 px-4">$1.85</td>
                            <td class="py-3.5 px-4">91.2%</td>
                            <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">Optimal</span></td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-4 font-medium text-slate-900">Customer Support</td>
                            <td class="py-3.5 px-4">32,450</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">12.1</td>
                            <td class="py-3.5 px-4">$2.40</td>
                            <td class="py-3.5 px-4">84.6%</td>
                            <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">Normal</span></td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-4 font-medium text-slate-900">Logistics & Dispatch</td>
                            <td class="py-3.5 px-4">21,180</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">9.8</td>
                            <td class="py-3.5 px-4">$2.80</td>
                            <td class="py-3.5 px-4">78.4%</td>
                            <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">Bottleneck</span></td>
                        </tr>
                        <tr>
                            <td class="py-3.5 px-4 font-medium text-slate-900">Quality Assurance</td>
                            <td class="py-3.5 px-4">12,750</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">15.2</td>
                            <td class="py-3.5 px-4">$2.10</td>
                            <td class="py-3.5 px-4">88.0%</td>
                            <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">Optimal</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Workforce ROI Panel -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-slate-900">Workforce Investment ROI</h2>
                <span class="text-xs px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-medium">Validated</span>
            </div>

            <div class="p-4 rounded-lg bg-slate-50 border border-slate-100 space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-medium text-slate-600">L&D: Technical Upskilling</span>
                    <span class="font-bold text-emerald-600">ROI: +184%</span>
                </div>
                <div class="text-xs text-slate-500">Payback: 4.2 mos | <span class="text-emerald-700 font-semibold">CORRELATION</span></div>
            </div>

            <div class="p-4 rounded-lg bg-slate-50 border border-slate-100 space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-medium text-slate-600">Recruitment: Senior Engineers</span>
                    <span class="font-bold text-emerald-600">ROI: +210%</span>
                </div>
                <div class="text-xs text-slate-500">Ramp-up: 2.5 mos | First-year value: $280k</div>
            </div>

            <div class="p-4 rounded-lg bg-slate-50 border border-slate-100 space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-medium text-slate-600">Overtime Productivity Drag</span>
                    <span class="font-bold text-rose-600">-18.5% rate</span>
                </div>
                <div class="text-xs text-slate-500">Overtime units/hr lower than regular; review fatigue limits.</div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100">
                <div class="flex items-center text-xs text-slate-500">
                    <i class="fa-solid fa-circle-info mr-2 text-indigo-500"></i>
                    AI Advisory: Insights are strictly advisory and based on operational metrics. Zero employee surveillance signals used.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
