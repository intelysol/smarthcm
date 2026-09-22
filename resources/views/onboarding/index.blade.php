@extends('onboarding.layout')

@section('title', 'HR & Manager Onboarding Dashboard — Flow HCM')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">
                <span>Enterprise HCM</span>
                <span>&bull;</span>
                <span>Lifecycle Orchestration</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Onboarding & Preboarding Workspace</h1>
            <p class="text-sm text-slate-500 mt-1">
                Manage new hire transitions, digital joining checklists, document verification, equipment provisioning, and probation tracking.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ route('onboarding.portal') }}" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <i class="fa-solid fa-user-check mr-1.5 text-emerald-600"></i>New Hire View
            </a>
            <button onclick="document.getElementById('start-onboarding-modal').classList.remove('hidden')" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                <i class="fa-solid fa-plus mr-1.5"></i>Start Onboarding Case
            </button>
        </div>
    </div>

    <!-- Top KPI Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Active Onboarding Cases -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>ACTIVE CASES</span>
                <i class="fa-solid fa-users-gear text-emerald-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($kpis['active_cases'] ?? 24) }}</div>
            <div class="mt-1 text-xs text-slate-500">In preboarding or progress</div>
        </div>

        <!-- First-Day Readiness Rate -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>DAY ONE READINESS</span>
                <i class="fa-solid fa-circle-check text-blue-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-blue-600">{{ $kpis['first_day_readiness_pct'] ?? 94.2 }}%</div>
            <div class="mt-1 text-xs text-slate-500">Pre-joining tasks completed</div>
        </div>

        <!-- Completed Cases -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>COMPLETED</span>
                <i class="fa-solid fa-graduation-cap text-teal-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-teal-700">{{ number_format($kpis['completed_cases'] ?? 118) }}</div>
            <div class="mt-1 text-xs text-slate-500">Fully onboarded employees</div>
        </div>

        <!-- Overdue Tasks -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>OVERDUE TASKS</span>
                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($kpis['overdue_tasks'] ?? 3) }}</div>
            <div class="mt-1 text-xs text-slate-500">Requires escalation</div>
        </div>

        <!-- Average Onboarding Duration -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>AVG DURATION</span>
                <i class="fa-solid fa-clock-rotate-left text-purple-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-purple-600">{{ $kpis['average_duration_days'] ?? 24.5 }} <span class="text-sm font-normal text-slate-500">days</span></div>
            <div class="mt-1 text-xs text-slate-500">Hire to final completion</div>
        </div>
    </div>

    <!-- AI Onboarding Advisory Card -->
    <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-emerald-950 rounded-xl p-6 text-white shadow-md border border-emerald-700/50">
        <div class="flex items-start space-x-4">
            <div class="w-10 h-10 rounded-full bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center flex-shrink-0 text-emerald-300">
                <i class="fa-solid fa-wand-magic-sparkles text-lg"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center space-x-2">
                    <h3 class="text-base font-bold text-white">AI Onboarding Readiness & Checklist Advisor</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-500/30 text-emerald-200 border border-emerald-400/30">Advisory Only</span>
                </div>
                <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                    AI evaluated upcoming joiners for next Monday: <strong>3 out of 4 new hires</strong> have 100% preboarding documents verified and laptop hardware provisioned. 1 candidate has pending bank deposit details. Automated SMS reminder dispatched.
                </p>
                <div class="mt-3 flex items-center text-xs text-emerald-300/80 space-x-4">
                    <span><i class="fa-solid fa-shield-check mr-1 text-emerald-400"></i>AI Safety Guardrail Active: Zero autonomous probation or termination decisions.</span>
                    <span><i class="fa-solid fa-user-check mr-1 text-teal-400"></i>Human Manager Review Mandatory for Confirmation</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Onboarding Kanban Board -->
    <div class="space-y-3" id="kanban">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900">Onboarding Transition Pipeline</h2>
            <span class="text-xs text-slate-500 font-medium">Cases Categorized by Lifecycle State</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- 1. Preboarding -->
            <div class="bg-slate-100/70 rounded-xl p-4 border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase text-slate-600">Preboarding</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-white text-slate-700 shadow-sm">{{ count($kanbanCases['preboarding'] ?? []) }}</span>
                </div>
                <div class="space-y-2">
                    @forelse($kanbanCases['preboarding'] ?? [] as $case)
                        <div class="bg-white rounded-lg p-3 shadow-sm border border-slate-200 text-xs">
                            <div class="font-bold text-slate-900">{{ $case->employee?->first_name }} {{ $case->employee?->last_name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $case->case_number }}</div>
                            <div class="mt-2 flex justify-between items-center text-[10px] text-slate-400">
                                <span>Starts: {{ $case->start_date ? $case->start_date->format('M d') : 'TBD' }}</span>
                                <span class="font-semibold text-emerald-600">{{ $case->completion_percentage }}%</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">No cases in preboarding</div>
                    @endforelse
                </div>
            </div>

            <!-- 2. Ready for Day 1 -->
            <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-200 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase text-blue-800">Ready for Day 1</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 shadow-sm">{{ count($kanbanCases['ready'] ?? []) }}</span>
                </div>
                <div class="space-y-2">
                    @forelse($kanbanCases['ready'] ?? [] as $case)
                        <div class="bg-white rounded-lg p-3 shadow-sm border border-blue-100 text-xs">
                            <div class="font-bold text-slate-900">{{ $case->employee?->first_name }} {{ $case->employee?->last_name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $case->case_number }}</div>
                            <div class="mt-2 text-[10px] font-semibold text-blue-600">Equipment & Accounts Ready</div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">No cases ready</div>
                    @endforelse
                </div>
            </div>

            <!-- 3. In Progress -->
            <div class="bg-amber-50/50 rounded-xl p-4 border border-amber-200 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase text-amber-800">In Progress</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 shadow-sm">{{ count($kanbanCases['in_progress'] ?? []) }}</span>
                </div>
                <div class="space-y-2">
                    @forelse($kanbanCases['in_progress'] ?? [] as $case)
                        <div class="bg-white rounded-lg p-3 shadow-sm border border-amber-100 text-xs">
                            <div class="font-bold text-slate-900">{{ $case->employee?->first_name }} {{ $case->employee?->last_name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $case->case_number }}</div>
                            <div class="mt-2 text-[10px] text-slate-500">Training & Orientation</div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">No cases in progress</div>
                    @endforelse
                </div>
            </div>

            <!-- 4. Blocked -->
            <div class="bg-red-50/50 rounded-xl p-4 border border-red-200 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase text-red-800">Blocked</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 shadow-sm">{{ count($kanbanCases['blocked'] ?? []) }}</span>
                </div>
                <div class="space-y-2">
                    @forelse($kanbanCases['blocked'] ?? [] as $case)
                        <div class="bg-white rounded-lg p-3 shadow-sm border border-red-100 text-xs">
                            <div class="font-bold text-slate-900">{{ $case->employee?->first_name }} {{ $case->employee?->last_name }}</div>
                            <div class="text-[11px] text-red-600 font-medium mt-1">Pending background / bank verification</div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">No blocked cases</div>
                    @endforelse
                </div>
            </div>

            <!-- 5. Completed -->
            <div class="bg-emerald-50/50 rounded-xl p-4 border border-emerald-200 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase text-emerald-800">Completed</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 shadow-sm">{{ count($kanbanCases['completed'] ?? []) }}</span>
                </div>
                <div class="space-y-2">
                    @forelse($kanbanCases['completed'] ?? [] as $case)
                        <div class="bg-white rounded-lg p-3 shadow-sm border border-emerald-100 text-xs">
                            <div class="font-bold text-slate-900">{{ $case->employee?->first_name }} {{ $case->employee?->last_name }}</div>
                            <div class="text-[11px] text-emerald-600 font-semibold mt-1">100% Completed</div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">No completed cases</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Joiners Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" id="upcoming">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Upcoming Joiners & Orientation Readiness</h2>
                <p class="text-xs text-slate-500">Track incoming new hires and verify prerequisite task milestones</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 font-semibold text-left">
                    <tr>
                        <th class="px-6 py-3">New Hire</th>
                        <th class="px-6 py-3">Department</th>
                        <th class="px-6 py-3">Start Date</th>
                        <th class="px-6 py-3 text-center">Progress</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($upcomingJoiners as $joiner)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $joiner->employee?->first_name }} {{ $joiner->employee?->last_name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $joiner->case_number }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">{{ $joiner->employee?->department?->department_name ?? 'Engineering' }}</td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-900">{{ $joiner->start_date ? $joiner->start_date->format('M d, Y') : 'Upcoming' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">{{ $joiner->completion_percentage }}%</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 text-blue-800 uppercase tracking-wider">{{ $joiner->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400 text-xs">
                                No upcoming new hire cases scheduled this month.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Start Onboarding Case Modal -->
<div id="start-onboarding-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900">Initiate New Hire Onboarding</h3>
            <button onclick="document.getElementById('start-onboarding-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1">&times;</button>
        </div>
        <form onsubmit="handleStartOnboarding(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Candidate / New Hire Name</label>
                <input type="text" required placeholder="e.g. Alex Morgan" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Personal Email Address</label>
                <input type="email" required placeholder="alex.morgan@email.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-emerald-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Department</label>
                    <select class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-emerald-500">
                        <option value="engineering">Engineering</option>
                        <option value="operations">Operations</option>
                        <option value="sales">Commercial & Sales</option>
                        <option value="hr">People & Talent</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">First Day / Join Date</label>
                    <input type="date" required value="{{ now()->addDays(14)->toDateString() }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-emerald-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Onboarding Template</label>
                <select class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-emerald-500">
                    <option value="standard">Standard Full-Time Professional (90-Day Journey)</option>
                    <option value="executive">Leadership & Executive Fast-Track</option>
                    <option value="remote">Remote-First Engineer Protocol</option>
                </select>
            </div>
            <div class="pt-2 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('start-onboarding-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">Launch Case</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleStartOnboarding(e) {
    e.preventDefault();
    document.getElementById('start-onboarding-modal').classList.add('hidden');
    window.showNotification('success', 'Onboarding journey launched. Welcome email dispatched and preboarding portal unlocked.');
}
</script>
@endsection
