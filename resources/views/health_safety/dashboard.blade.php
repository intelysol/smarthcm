@extends('health_safety.layout')

@section('title', 'EHS Executive Safety Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Occupational Health & Workplace Safety Dashboard</h1>
            <p class="mt-1 text-sm text-slate-400">Enterprise safety surveillance, medical clearance compliance, and OSHA recordable monitoring.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('health.incidents') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 focus:ring-offset-slate-900 transition">
                Report Safety Incident
            </a>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-400">Days Without Lost Time</span>
                <span class="inline-flex p-2 rounded-lg bg-emerald-500/10 text-emerald-400">⏱️</span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-white" id="stat-days-safe">248</span>
                <span class="text-xs text-emerald-400 font-medium">Consecutive Safe Days</span>
            </div>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-400">TRIR (Total Recordable Incident Rate)</span>
                <span class="inline-flex p-2 rounded-lg bg-blue-500/10 text-blue-400">📊</span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-white" id="stat-trir">0.60</span>
                <span class="text-xs text-slate-400 font-medium">Per 200k Hours Worked</span>
            </div>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-400">Active Medical Restrictions</span>
                <span class="inline-flex p-2 rounded-lg bg-amber-500/10 text-amber-400">⚠️</span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-white" id="stat-restrictions">14</span>
                <span class="text-xs text-amber-400 font-medium">Operational Limitations</span>
            </div>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-400">Active Return-to-Work Cases</span>
                <span class="inline-flex p-2 rounded-lg bg-purple-500/10 text-purple-400">🔄</span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-white" id="stat-rtw">5</span>
                <span class="text-xs text-purple-400 font-medium">In Phased Transition</span>
            </div>
        </div>
    </div>

    <!-- Safety Analytics and AI Advisory Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-xl border border-slate-800 bg-slate-950/40 p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-4">
                <div>
                    <h2 class="text-base font-semibold text-white">OSHA Recordable Incident Log (Form 300 Summary)</h2>
                    <p class="text-xs text-slate-400">Statutory recordkeeping of work-related injuries and illnesses.</p>
                </div>
                <span class="text-xs font-mono text-slate-400">CY 2026</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-900/60 text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Case #</th>
                            <th class="py-3 px-4">Employee & Job</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Lost Days</th>
                            <th class="py-3 px-4">Restricted Days</th>
                            <th class="py-3 px-4">Classification</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <tr class="hover:bg-slate-900/40 transition">
                            <td class="py-3 px-4 font-mono text-xs text-emerald-400">INC-20260901-0001</td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-white">Warehouse Specialist</div>
                                <div class="text-xs text-slate-400">Logistics Facility B</div>
                            </td>
                            <td class="py-3 px-4 text-xs">2026-09-01</td>
                            <td class="py-3 px-4 text-xs font-semibold text-amber-400">3</td>
                            <td class="py-3 px-4 text-xs">7</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex rounded-full bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-400 ring-1 ring-inset ring-amber-500/20">
                                    Lost Time
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-900/40 transition">
                            <td class="py-3 px-4 font-mono text-xs text-emerald-400">INC-20260815-0004</td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-white">Machine Operator</div>
                                <div class="text-xs text-slate-400">Plant 1 Line 4</div>
                            </td>
                            <td class="py-3 px-4 text-xs">2026-08-15</td>
                            <td class="py-3 px-4 text-xs">0</td>
                            <td class="py-3 px-4 text-xs font-semibold text-amber-400">14</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex rounded-full bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-400 ring-1 ring-inset ring-blue-500/20">
                                    Job Transfer / Restriction
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- AI Safety Copilot Panel -->
        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 border-b border-slate-800 pb-4 mb-4">
                    <span class="inline-flex p-1.5 rounded-md bg-emerald-500/10 text-emerald-400">✨</span>
                    <div>
                        <h2 class="text-base font-semibold text-white">AI Health & Safety Advisor</h2>
                        <span class="text-xs text-slate-400">Assistive Incident Triaging</span>
                    </div>
                </div>

                <div class="rounded-lg bg-slate-900/60 p-4 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Risk Advisory</span>
                        <span class="inline-flex rounded bg-emerald-500/10 px-1.5 py-0.5 text-[10px] font-medium text-emerald-400">ADVISORY ONLY</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Continuous scan of industrial hygiene measurements indicates a 12% rise in localized sound levels in Assembly Area 3. Recommend proactive audiogram verification for 6 uncertified operators.
                    </p>
                </div>
            </div>

            <div class="mt-6 border-t border-slate-800 pt-4">
                <span class="text-[11px] text-slate-400 leading-snug block">
                    AI suggestions do not substitute for official certified clinical or statutory OSHA compliance officer evaluations.
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
