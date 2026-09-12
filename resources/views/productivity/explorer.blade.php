@extends('productivity.layout')

@section('title', 'Workforce Productivity Explorer')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Workforce Productivity Explorer</h1>
            <p class="text-sm text-slate-500 mt-1">Multi-dimensional operational analytics, filtering, dimensional drill-downs, and data exports</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="/api/v1/hcm/productivity/export/csv" class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-slate-900 text-white hover:bg-slate-800 transition">
                <i class="fa-solid fa-file-csv mr-1.5"></i>Export CSV
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
            <div>
                <label class="block font-medium text-slate-600 mb-1">Period Type</label>
                <select class="w-full rounded-md border-slate-300 shadow-sm text-xs py-1.5">
                    <option>Monthly</option>
                    <option>Quarterly</option>
                    <option>Annual</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-slate-600 mb-1">Department</label>
                <select class="w-full rounded-md border-slate-300 shadow-sm text-xs py-1.5">
                    <option>All Departments</option>
                    <option>Manufacturing Ops</option>
                    <option>Customer Support</option>
                    <option>Logistics & Dispatch</option>
                    <option>Quality Assurance</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-slate-600 mb-1">Metric Type</label>
                <select class="w-full rounded-md border-slate-300 shadow-sm text-xs py-1.5">
                    <option>All Metrics</option>
                    <option>Volume (Units/Hour)</option>
                    <option>Economic (Cost/Unit)</option>
                    <option>Utilization (%)</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-slate-600 mb-1">Quality Status</label>
                <select class="w-full rounded-md border-slate-300 shadow-sm text-xs py-1.5">
                    <option>All Records</option>
                    <option>VALID</option>
                    <option>ESTIMATED</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-600">
                <thead class="uppercase bg-slate-50 border-b border-slate-100 text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Period</th>
                        <th class="py-3 px-4">Department</th>
                        <th class="py-3 px-4">Output Volume</th>
                        <th class="py-3 px-4">Productive Hours</th>
                        <th class="py-3 px-4">Rate (Output/Hr)</th>
                        <th class="py-3 px-4">Utilization</th>
                        <th class="py-3 px-4">Labor Cost</th>
                        <th class="py-3 px-4">Cost/Unit</th>
                        <th class="py-3 px-4">Provenance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-3 px-4 font-medium text-slate-900">2026-10</td>
                        <td class="py-3 px-4">Operations BU / Manufacturing</td>
                        <td class="py-3 px-4 font-semibold text-slate-800">58,200</td>
                        <td class="py-3 px-4">3,163.0</td>
                        <td class="py-3 px-4 font-bold text-emerald-600">18.40</td>
                        <td class="py-3 px-4">91.2%</td>
                        <td class="py-3 px-4">$107,670</td>
                        <td class="py-3 px-4 font-semibold text-slate-900">$1.85</td>
                        <td class="py-3 px-4"><span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-medium">MES Punch Verified</span></td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-medium text-slate-900">2026-10</td>
                        <td class="py-3 px-4">Support BU / Customer Care</td>
                        <td class="py-3 px-4 font-semibold text-slate-800">32,450</td>
                        <td class="py-3 px-4">2,681.8</td>
                        <td class="py-3 px-4 font-bold text-blue-600">12.10</td>
                        <td class="py-3 px-4">84.6%</td>
                        <td class="py-3 px-4">$77,880</td>
                        <td class="py-3 px-4 font-semibold text-slate-900">$2.40</td>
                        <td class="py-3 px-4"><span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-medium">CRM Tickets Verified</span></td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-medium text-slate-900">2026-10</td>
                        <td class="py-3 px-4">Logistics BU / Dispatch</td>
                        <td class="py-3 px-4 font-semibold text-slate-800">21,180</td>
                        <td class="py-3 px-4">2,161.2</td>
                        <td class="py-3 px-4 font-bold text-amber-600">9.80</td>
                        <td class="py-3 px-4">78.4%</td>
                        <td class="py-3 px-4">$59,304</td>
                        <td class="py-3 px-4 font-semibold text-slate-900">$2.80</td>
                        <td class="py-3 px-4"><span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-medium">WMS Orders Verified</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
