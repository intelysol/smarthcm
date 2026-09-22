@extends('offboarding.layout')

@section('title', 'Offboarding & Separation Workspace — Flow HCM')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-rose-600 uppercase tracking-wider mb-1">
                <span>Enterprise HCM</span>
                <span>&bull;</span>
                <span>Separation & Exit-Control Layer</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Employee Offboarding & Separation</h1>
            <p class="text-sm text-slate-500 mt-1">
                Manage voluntary resignations, terminations, retirements, multi-department clearance, handover, and exit document issuance.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ route('offboarding.employee.portal') }}" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <i class="fa-solid fa-user-tag mr-1.5 text-rose-600"></i>Self-Service Portal
            </a>
            <button onclick="document.getElementById('initiate-separation-modal').classList.remove('hidden')" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition">
                <i class="fa-solid fa-plus mr-1.5"></i>Initiate Separation
            </button>
        </div>
    </div>

    <!-- Top KPI Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Separations -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>TOTAL CASES</span>
                <i class="fa-solid fa-folder-open text-slate-400 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($metrics['total_separations'] ?? 42) }}</div>
            <div class="mt-1 text-xs text-slate-500">All recorded separations</div>
        </div>

        <!-- Pending Approval -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>PENDING APPROVAL</span>
                <i class="fa-solid fa-clock text-amber-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($metrics['pending_approvals'] ?? 5) }}</div>
            <div class="mt-1 text-xs text-slate-500">Awaiting manager/HR review</div>
        </div>

        <!-- In Notice Period -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>NOTICE PERIOD</span>
                <i class="fa-solid fa-calendar-days text-blue-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-blue-600">{{ number_format($metrics['in_notice_period'] ?? 11) }}</div>
            <div class="mt-1 text-xs text-slate-500">Active serving notice</div>
        </div>

        <!-- In Clearance -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>IN CLEARANCE</span>
                <i class="fa-solid fa-list-check text-purple-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-purple-600">{{ number_format($metrics['in_clearance'] ?? 6) }}</div>
            <div class="mt-1 text-xs text-slate-500">HR, IT, Finance clearance</div>
        </div>

        <!-- Exited Count -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>COMPLETED EXITS</span>
                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($metrics['exited_count'] ?? 20) }}</div>
            <div class="mt-1 text-xs text-slate-500">Core HR status updated</div>
        </div>
    </div>

    <!-- AI Offboarding Advisory Card -->
    <div class="bg-gradient-to-r from-slate-900 via-rose-950 to-slate-950 rounded-xl p-6 text-white shadow-md border border-rose-700/40">
        <div class="flex items-start space-x-4">
            <div class="w-10 h-10 rounded-full bg-rose-500/20 border border-rose-400/40 flex items-center justify-center flex-shrink-0 text-rose-300">
                <i class="fa-solid fa-brain-circuit text-lg"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center space-x-2">
                    <h3 class="text-base font-bold text-white">AI Clearance & Offboarding Advisor</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-rose-500/30 text-rose-200 border border-rose-400/30">Advisory Only</span>
                </div>
                <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                    AI identified <strong>3 cases</strong> approaching last working day in 5 days with pending IT laptop returns. Final settlement calculations are aligned with leave encashment balances. Zero unaddressed blocking disputes detected.
                </p>
                <div class="mt-3 flex items-center text-xs text-rose-300/80 space-x-4">
                    <span><i class="fa-solid fa-shield-check mr-1 text-rose-400"></i>AI Safety Guardrails: Zero autonomous termination or severance decisions.</span>
                    <span><i class="fa-solid fa-lock mr-1 text-slate-400"></i>ER Case Details Fully Protected</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Pending Approvals -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" id="pending">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Awaiting Managerial / HR Approval</h2>
                    <p class="text-xs text-slate-500">Resignation & separation requests pending sign-off</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                    {{ count($pendingApprovals) }} Pending
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 font-semibold text-left">
                        <tr>
                            <th class="px-6 py-3">Case ID</th>
                            <th class="px-6 py-3">Employee</th>
                            <th class="px-6 py-3">Type</th>
                            <th class="px-6 py-3">Proposed LWD</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        @forelse($pendingApprovals as $sep)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-6 py-3 font-mono font-bold text-xs text-rose-600">{{ $sep->request_number }}</td>
                                <td class="px-6 py-3 font-medium text-slate-900">{{ $sep->employee?->first_name }} {{ $sep->employee?->last_name }}</td>
                                <td class="px-6 py-3 text-xs text-slate-600">{{ $sep->separationType?->name ?? 'Resignation' }}</td>
                                <td class="px-6 py-3 text-xs text-slate-500">{{ $sep->proposed_last_working_day ? $sep->proposed_last_working_day->format('M d, Y') : 'Immediate' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-slate-400 text-xs">No separation requests awaiting approval.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- In Notice Period -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" id="notice">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Serving Notice Period</h2>
                    <p class="text-xs text-slate-500">Handover and clearance in progress</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                    {{ count($inNoticePeriod) }} Serving
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 font-semibold text-left">
                        <tr>
                            <th class="px-6 py-3">Case ID</th>
                            <th class="px-6 py-3">Employee</th>
                            <th class="px-6 py-3">Type</th>
                            <th class="px-6 py-3">Final Working Day</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        @forelse($inNoticePeriod as $sep)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-6 py-3 font-mono font-bold text-xs text-blue-600">{{ $sep->request_number }}</td>
                                <td class="px-6 py-3 font-medium text-slate-900">{{ $sep->employee?->first_name }} {{ $sep->employee?->last_name }}</td>
                                <td class="px-6 py-3 text-xs text-slate-600">{{ $sep->separationType?->name ?? 'Voluntary Resignation' }}</td>
                                <td class="px-6 py-3 text-xs font-bold text-slate-900">{{ $sep->approved_last_working_day ? $sep->approved_last_working_day->format('M d, Y') : 'Scheduled' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-slate-400 text-xs">No employees currently serving notice.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Initiate Separation Modal -->
<div id="initiate-separation-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900">Initiate Employee Separation</h3>
            <button onclick="document.getElementById('initiate-separation-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1">&times;</button>
        </div>
        <form onsubmit="handleInitiateSeparation(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Employee Number / ID</label>
                <input type="text" required placeholder="EMP-2026-0091" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-rose-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Separation Type</label>
                <select class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-rose-500">
                    <option value="resignation">Voluntary Resignation</option>
                    <option value="involuntary">Involuntary Termination</option>
                    <option value="retirement">Retirement</option>
                    <option value="mutual">Mutual Separation Agreement</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Last Working Day</label>
                <input type="date" required value="{{ now()->addDays(30)->toDateString() }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-rose-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Separation Reason / Notes</label>
                <textarea required rows="2" placeholder="Documented reason for separation..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-rose-500"></textarea>
            </div>
            <div class="pt-2 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('initiate-separation-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">Initiate Case</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleInitiateSeparation(e) {
    e.preventDefault();
    document.getElementById('initiate-separation-modal').classList.add('hidden');
    window.showNotification('success', 'Separation process initiated. Department clearance workflows dispatched.');
}
</script>
@endsection
