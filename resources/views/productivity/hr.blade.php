@extends('productivity.layout')

@section('title', 'HR Workforce Intelligence & Productivity Trends')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">HR Workforce Intelligence & Capability Economics</h1>
            <p class="text-sm text-slate-500 mt-1">Impact of turnover, new-hire ramp-up curves, learning investments, and organizational capability</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-purple-100 text-purple-800">
                <i class="fa-solid fa-users-gear mr-1.5"></i>HR Intelligence Scope
            </span>
        </div>
    </div>

    <!-- HR Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Avg Time-to-Productivity</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">48 <span class="text-xs font-normal text-slate-500">days</span></div>
            <div class="text-xs text-emerald-600 font-medium mt-1">-6 days from accelerated onboarding</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Turnover Output Drag</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">4,200 <span class="text-xs font-normal text-slate-500">units/mo</span></div>
            <div class="text-xs text-slate-500 font-medium mt-1">Vacancy + Ramp-up inefficiency loss</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">L&D Productivity Uplift</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">+14.6%</div>
            <div class="text-xs text-emerald-600 font-medium mt-1">Post-training measured differential</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Absence Drag on Capacity</span>
            <div class="text-2xl font-bold text-slate-900 mt-2">3.8%</div>
            <div class="text-xs text-slate-500 font-medium mt-1">Direct lost capacity hours</div>
        </div>
    </div>

    <!-- Learning & Ramp-up Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-base font-semibold text-slate-900 mb-3">Tenure & Ramp-Up Productivity Curve</h2>
            <p class="text-xs text-slate-500 mb-4">Relative productivity output indexed to full competency (100%)</p>
            <div class="space-y-3 text-sm">
                <div>
                    <div class="flex justify-between text-xs text-slate-700 mb-1">
                        <span>Month 1 (Onboarding & Shadowing)</span>
                        <span class="font-semibold text-slate-900">35%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-indigo-400 h-2 rounded-full" style="width: 35%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs text-slate-700 mb-1">
                        <span>Month 2 (Supervised Execution)</span>
                        <span class="font-semibold text-slate-900">65%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: 65%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs text-slate-700 mb-1">
                        <span>Month 3 (Autonomous Operations)</span>
                        <span class="font-semibold text-slate-900">92%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: 92%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs text-slate-700 mb-1">
                        <span>Month 6+ (Mastery / Peer Mentor)</span>
                        <span class="font-semibold text-emerald-600">105%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
            <h2 class="text-base font-semibold text-slate-900">Learning & Development ROI Tracking</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-slate-600">
                    <thead class="uppercase bg-slate-50 border-b border-slate-100 text-slate-400">
                        <tr>
                            <th class="py-2.5 px-3">Program</th>
                            <th class="py-2.5 px-3">Investment</th>
                            <th class="py-2.5 px-3">Uplift</th>
                            <th class="py-2.5 px-3">ROI %</th>
                            <th class="py-2.5 px-3">Causality</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="py-2.5 px-3 font-medium text-slate-800">Advanced Machine Ops</td>
                            <td class="py-2.5 px-3">$14,500</td>
                            <td class="py-2.5 px-3 text-emerald-600 font-semibold">+22%</td>
                            <td class="py-2.5 px-3 font-bold text-emerald-600">+195%</td>
                            <td class="py-2.5 px-3"><span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700">CORRELATION</span></td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-medium text-slate-800">Customer Resolution</td>
                            <td class="py-2.5 px-3">$8,200</td>
                            <td class="py-2.5 px-3 text-emerald-600 font-semibold">+11%</td>
                            <td class="py-2.5 px-3 font-bold text-emerald-600">+140%</td>
                            <td class="py-2.5 px-3"><span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700">CORRELATION</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
