@extends('recruitment.layout')

@section('title', 'Executive Recruitment & ATS Dashboard — Flow HCM')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-1">
                <span>Enterprise HCM</span>
                <span>&bull;</span>
                <span>Applicant Tracking System (ATS)</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Recruitment & Talent Acquisition</h1>
            <p class="text-sm text-slate-500 mt-1">
                End-to-end recruitment lifecycle from workforce planning requisition to interview scorecards, versioned offers, and Core HR handoff.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ route('careers.index') }}" target="_blank" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <i class="fa-solid fa-globe mr-1.5 text-indigo-500"></i>View Careers Portal
            </a>
            <button class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                <i class="fa-solid fa-plus mr-1.5"></i>Create Requisition
            </button>
        </div>
    </div>

    <!-- Top KPI Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Open Requisitions -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>OPEN REQUISITIONS</span>
                <i class="fa-solid fa-briefcase text-indigo-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($kpis['open_requisitions'] ?? 14) }}</div>
            <div class="mt-1 text-xs text-slate-500">Approved vacancies in market</div>
        </div>

        <!-- Total Candidates -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>TALENT POOL</span>
                <i class="fa-solid fa-users text-blue-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($kpis['total_candidates'] ?? 1420) }}</div>
            <div class="mt-1 text-xs text-slate-500">Candidate profiles on file</div>
        </div>

        <!-- Active Applications -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>ACTIVE PIPELINE</span>
                <i class="fa-solid fa-network-wired text-emerald-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($kpis['active_applications'] ?? 86) }}</div>
            <div class="mt-1 text-xs text-slate-500">Applications currently in process</div>
        </div>

        <!-- Pending Offers -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>OFFERS EXTENDED</span>
                <i class="fa-solid fa-envelope-open-text text-amber-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($kpis['pending_offers'] ?? 8) }}</div>
            <div class="mt-1 text-xs text-slate-500">Awaiting candidate signature</div>
        </div>

        <!-- Average Time to Hire -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>AVG TIME TO HIRE</span>
                <i class="fa-solid fa-clock-rotate-left text-purple-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-purple-600">{{ $kpis['average_time_to_hire_days'] ?? 32 }} <span class="text-sm font-normal text-slate-500">days</span></div>
            <div class="mt-1 text-xs text-slate-500">Application to formal acceptance</div>
        </div>
    </div>

    <!-- AI Recruiter Advisory Card -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-xl p-6 text-white shadow-md border border-indigo-700/50">
        <div class="flex items-start space-x-4">
            <div class="w-10 h-10 rounded-full bg-indigo-500/20 border border-indigo-400/40 flex items-center justify-center flex-shrink-0 text-indigo-300">
                <i class="fa-solid fa-wand-magic-sparkles text-lg"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center space-x-2">
                    <h3 class="text-base font-bold text-white">AI Recruitment Assistant & Match Analyzer</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">Advisory Only</span>
                </div>
                <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                    AI evaluated candidate profiles for the <strong>Senior Cloud Architect</strong> requisition. Top match exhibits <strong>92% competency alignment</strong> across Kubernetes, Microservices, and Go with 7 years verified experience. Recommendation: prioritize for technical interview stage.
                </p>
                <div class="mt-3 flex items-center text-xs text-indigo-300/80 space-x-4">
                    <span><i class="fa-solid fa-shield-check mr-1 text-emerald-400"></i>AI Hiring Guardrail Verified: Zero autonomous candidate rejection.</span>
                    <span><i class="fa-solid fa-user-check mr-1 text-blue-400"></i>Human Decision Mandatory for Offer Sanctioning</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pipeline Kanban Overview -->
    <div class="space-y-3" id="pipeline">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900">Recruitment Pipeline Stages</h2>
            <span class="text-xs text-slate-500 font-medium">Active Applicants Across Open Requisitions</span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
            <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
                <div class="text-xs font-semibold uppercase text-slate-500">1. New</div>
                <div class="mt-2 text-2xl font-bold text-slate-800">{{ $kanbanCounts['new'] ?? 24 }}</div>
                <div class="mt-1 text-[11px] text-slate-400">Awaiting review</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
                <div class="text-xs font-semibold uppercase text-blue-600">2. Screening</div>
                <div class="mt-2 text-2xl font-bold text-blue-700">{{ $kanbanCounts['screening'] ?? 18 }}</div>
                <div class="mt-1 text-[11px] text-slate-400">Resume review</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
                <div class="text-xs font-semibold uppercase text-indigo-600">3. Shortlisted</div>
                <div class="mt-2 text-2xl font-bold text-indigo-700">{{ $kanbanCounts['shortlisted'] ?? 14 }}</div>
                <div class="mt-1 text-[11px] text-slate-400">Manager approved</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
                <div class="text-xs font-semibold uppercase text-purple-600">4. Interview</div>
                <div class="mt-2 text-2xl font-bold text-purple-700">{{ $kanbanCounts['interview'] ?? 20 }}</div>
                <div class="mt-1 text-[11px] text-slate-400">Panel & technical</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
                <div class="text-xs font-semibold uppercase text-amber-600">5. Offer</div>
                <div class="mt-2 text-2xl font-bold text-amber-700">{{ $kanbanCounts['offer'] ?? 8 }}</div>
                <div class="mt-1 text-[11px] text-slate-400">Negotiation</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 text-center bg-emerald-50/50 border-emerald-200">
                <div class="text-xs font-semibold uppercase text-emerald-600">6. Hired</div>
                <div class="mt-2 text-2xl font-bold text-emerald-700">{{ $kanbanCounts['hired'] ?? 12 }}</div>
                <div class="mt-1 text-[11px] text-emerald-600">Handoff to Core HR</div>
            </div>
        </div>
    </div>

    <!-- Active Requisitions & Funnel Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Requisitions Table -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" id="requisitions">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Active Job Requisitions</h2>
                    <p class="text-xs text-slate-500">Linked to Workforce Planning and Core HR position budgets</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-md">
                    Openings: {{ $kpis['open_requisitions'] ?? 14 }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 font-semibold text-left">
                        <tr>
                            <th class="px-6 py-3">Requisition</th>
                            <th class="px-6 py-3">Department</th>
                            <th class="px-6 py-3">Priority</th>
                            <th class="px-6 py-3 text-center">Openings</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">Senior Cloud Architect</div>
                                <div class="text-xs text-slate-500 font-mono">REQ-2026-ENG01</div>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-600">Engineering</td>
                            <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-red-100 text-red-800">Critical</span></td>
                            <td class="px-6 py-4 text-center font-bold text-slate-900">2</td>
                            <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">Hiring</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">Enterprise Account Executive</div>
                                <div class="text-xs text-slate-500 font-mono">REQ-2026-SAL04</div>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-600">Sales</td>
                            <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800">High</span></td>
                            <td class="px-6 py-4 text-center font-bold text-slate-900">4</td>
                            <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">Open</span></td>
                        </tr>
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">People Operations Specialist</div>
                                <div class="text-xs text-slate-500 font-mono">REQ-2026-HR02</div>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-600">Human Resources</td>
                            <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800">Medium</span></td>
                            <td class="px-6 py-4 text-center font-bold text-slate-900">1</td>
                            <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">Open</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Funnel Conversion Breakdown -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6" id="funnel">
            <div class="border-b border-slate-100 pb-4 mb-4">
                <h2 class="text-base font-bold text-slate-900">Recruitment Funnel Conversion</h2>
                <p class="text-xs text-slate-500">Stage-by-stage yield & conversion efficiency</p>
            </div>

            <div class="space-y-4 text-sm">
                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span>Applications &rarr; Screened</span>
                        <span class="font-semibold text-indigo-600">{{ $funnel['screened_conversion_pct'] ?? 68 }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $funnel['screened_conversion_pct'] ?? 68 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span>Screened &rarr; Interviewed</span>
                        <span class="font-semibold text-blue-600">{{ $funnel['interview_conversion_pct'] ?? 42 }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $funnel['interview_conversion_pct'] ?? 42 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span>Interviewed &rarr; Offer Extended</span>
                        <span class="font-semibold text-purple-600">{{ $funnel['offer_conversion_pct'] ?? 28 }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $funnel['offer_conversion_pct'] ?? 28 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span>Offer &rarr; Hired (Acceptance Rate)</span>
                        <span class="font-semibold text-emerald-600">{{ $funnel['hire_conversion_pct'] ?? 85 }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $funnel['hire_conversion_pct'] ?? 85 }}%"></div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-between items-center text-xs">
                    <span class="text-slate-500 font-medium">Overall Funnel Yield:</span>
                    <span class="font-bold text-slate-900 font-mono">{{ $funnel['overall_yield_pct'] ?? 4.8 }}% of applicants</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
