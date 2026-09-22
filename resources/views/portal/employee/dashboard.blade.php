@extends('portal.layout')

@section('title', 'Employee Home')

@section('content')
<div class="space-y-6">
    <!-- Top Welcome Banner -->
    <div class="bg-gradient-to-r from-indigo-900/50 via-slate-900 to-slate-900 border border-slate-800 p-6 rounded-2xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xl">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-300 text-2xl font-black shadow-inner">
                {{ substr($dashboard['employee']['first_name'] ?? 'E', 0, 1) }}
            </div>
            <div>
                <h1 class="text-xl font-bold text-white tracking-wide">
                    Good {{ date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening') }}, {{ $dashboard['employee']['first_name'] }}
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ $dashboard['employee']['designation'] }} &bull; {{ $dashboard['employee']['department'] }} &bull; ID: <span class="font-mono text-slate-300">{{ $dashboard['employee']['employee_code'] }}</span>
                </p>
            </div>
        </div>

        <!-- Clock In / Out Action Widget -->
        <div id="clock-widget" class="flex items-center space-x-3 bg-slate-950/80 border border-slate-800 p-2.5 rounded-xl">
            <div class="text-right">
                <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Attendance</div>
                <div id="clock-status-badge" class="text-xs font-bold {{ $dashboard['attendance']['status'] === 'CLOCKED_IN' ? 'text-emerald-400' : 'text-slate-300' }}">
                    <i class="fa-solid fa-circle text-[8px] mr-1 {{ $dashboard['attendance']['status'] === 'CLOCKED_IN' ? 'text-emerald-400 animate-pulse' : 'text-slate-500' }}"></i>
                    {{ str_replace('_', ' ', $dashboard['attendance']['status']) }}
                </div>
            </div>
            <button onclick="handleClockToggle()" id="clock-toggle-btn" class="px-3.5 py-2 rounded-lg text-xs font-bold transition shadow-lg flex items-center space-x-1.5 {{ $dashboard['attendance']['status'] === 'CLOCKED_IN' ? 'bg-amber-600 hover:bg-amber-500 text-white' : 'bg-emerald-600 hover:bg-emerald-500 text-white' }}">
                <i class="fa-solid fa-fingerprint"></i>
                <span id="clock-btn-label">{{ $dashboard['attendance']['status'] === 'CLOCKED_IN' ? 'Clock Out' : 'Clock In' }}</span>
            </button>
        </div>
    </div>

    <!-- Quick Action Pills Row -->
    <div class="overflow-x-auto pb-2">
        <div class="flex space-x-2.5">
            @foreach($dashboard['quick_actions'] as $qa)
                <a href="{{ $qa['route'] }}" class="flex items-center space-x-2 px-3.5 py-2 bg-slate-900 border border-slate-800 hover:border-indigo-500/50 rounded-xl text-xs font-medium text-slate-200 hover:text-white hover:bg-slate-850 transition whitespace-nowrap shadow-sm group">
                    <i class="fa-solid {{ $qa['icon'] }} text-indigo-400 group-hover:scale-110 transition"></i>
                    <span>{{ $qa['title'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- 4 Key Daily Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: My Schedule -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                <span class="font-semibold uppercase tracking-wider">Today's Schedule</span>
                <i class="fa-solid fa-calendar-day text-indigo-400"></i>
            </div>
            <div class="text-lg font-bold text-white">
                {{ $dashboard['schedule']['start_time'] }} &ndash; {{ $dashboard['schedule']['end_time'] }}
            </div>
            <p class="text-xs text-slate-400 mt-1">
                {{ $dashboard['schedule']['shift_name'] }} &bull; {{ $dashboard['schedule']['location'] }}
            </p>
            <a href="{{ route('portal.work') }}" class="mt-3 inline-flex items-center text-[11px] text-indigo-400 hover:text-indigo-300 font-semibold">
                View shift details <i class="fa-solid fa-arrow-right ml-1 text-[9px]"></i>
            </a>
        </div>

        <!-- Card 2: Leave Balance -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                <span class="font-semibold uppercase tracking-wider">Leave Balance</span>
                <i class="fa-solid fa-plane-departure text-teal-400"></i>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-2xl font-black text-teal-400">{{ $dashboard['leave_summary']['remaining_days'] }}</span>
                <span class="text-xs text-slate-400 font-medium">days available</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                {{ $dashboard['leave_summary']['used_days'] }} used &bull; {{ $dashboard['leave_summary']['pending_days'] }} pending
            </p>
            <a href="{{ route('portal.requests') }}?modal=leave" class="mt-3 inline-flex items-center text-[11px] text-teal-400 hover:text-teal-300 font-semibold">
                Apply for leave <i class="fa-solid fa-plus ml-1 text-[9px]"></i>
            </a>
        </div>

        <!-- Card 3: Pending Tasks -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                <span class="font-semibold uppercase tracking-wider">Action Items</span>
                <i class="fa-solid fa-list-check text-amber-400"></i>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-2xl font-black text-amber-400">{{ $dashboard['tasks']['pending_count'] }}</span>
                <span class="text-xs text-slate-400 font-medium">pending tasks</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Compliance &amp; review items
            </p>
            <a href="#pending-tasks" class="mt-3 inline-flex items-center text-[11px] text-amber-400 hover:text-amber-300 font-semibold">
                Review tasks <i class="fa-solid fa-arrow-down ml-1 text-[9px]"></i>
            </a>
        </div>

        <!-- Card 4: Latest Payslip -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                <span class="font-semibold uppercase tracking-wider">Latest Pay</span>
                <i class="fa-solid fa-file-invoice-dollar text-emerald-400"></i>
            </div>
            @if($dashboard['latest_payslip'])
                <div class="text-lg font-bold text-emerald-400">
                    {{ $dashboard['latest_payslip']['currency'] }} {{ number_format($dashboard['latest_payslip']['net_pay'], 2) }}
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $dashboard['latest_payslip']['period'] }} (Net Pay)
                </p>
            @else
                <div class="text-sm font-semibold text-slate-300">Statement on file</div>
                <p class="text-xs text-slate-400 mt-1">Standard compensation package</p>
            @endif
            <a href="{{ route('portal.pay') }}" class="mt-3 inline-flex items-center text-[11px] text-emerald-400 hover:text-emerald-300 font-semibold">
                View pay statement <i class="fa-solid fa-arrow-right ml-1 text-[9px]"></i>
            </a>
        </div>
    </div>

    <!-- Activities & Request Tracking Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Tasks & Requests -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Pending Tasks Section -->
            <div id="pending-tasks" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-list-check text-indigo-400 text-sm"></i>
                        <h2 class="text-sm font-bold text-white">My Tasks</h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300">
                            {{ $dashboard['tasks']['pending_count'] }}
                        </span>
                    </div>
                </div>

                <div class="divide-y divide-slate-800/80 mt-3">
                    @forelse($dashboard['tasks']['items'] as $task)
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div class="space-y-0.5">
                                <div class="font-semibold text-slate-200">{{ $task['title'] }}</div>
                                <div class="flex items-center space-x-3 text-[11px] text-slate-400">
                                    <span class="text-amber-400 font-medium"><i class="fa-regular fa-clock mr-1"></i> Due {{ $task['due_date'] }}</span>
                                    <span class="uppercase tracking-wider text-[10px] bg-slate-800 px-1.5 py-0.5 rounded">{{ $task['source_module'] }}</span>
                                </div>
                            </div>
                            <a href="{{ $task['action_url'] }}" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-[11px] shadow transition">
                                {{ $task['action_label'] }}
                            </a>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400">
                            <i class="fa-regular fa-circle-check text-2xl text-emerald-400 mb-2 block"></i>
                            You are all caught up! Zero pending tasks.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Active Requests Section -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-code-pull-request text-teal-400 text-sm"></i>
                        <h2 class="text-sm font-bold text-white">My Requests</h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/20 text-teal-300">
                            {{ $dashboard['requests']['total_count'] }}
                        </span>
                    </div>
                    <a href="{{ route('portal.requests') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">
                        View all <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
                    </a>
                </div>

                <div class="divide-y divide-slate-800/80 mt-3">
                    @forelse($dashboard['requests']['items'] as $req)
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div class="space-y-1">
                                <div class="font-semibold text-slate-200 flex items-center space-x-2">
                                    <span>{{ $req['title'] }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold 
                                        {{ $req['status'] === 'APPROVED' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}
                                        {{ in_array($req['status'], ['PENDING', 'SUBMITTED']) ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                                        {{ $req['status'] === 'REJECTED' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : '' }}">
                                        {{ $req['status'] }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-400 flex items-center space-x-3">
                                    <span>Submitted {{ date('M d, Y', strtotime($req['submitted_at'])) }}</span>
                                    <span>&bull;</span>
                                    <span class="text-indigo-300">Next: {{ $req['next_action'] }}</span>
                                </div>
                            </div>
                            <div class="text-right text-xs">
                                <div class="text-[10px] uppercase text-slate-400 font-mono">{{ $req['type_label'] }}</div>
                                <div class="text-slate-300 font-medium mt-0.5">{{ $req['current_step'] }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400">
                            <i class="fa-solid fa-inbox text-2xl text-slate-600 mb-2 block"></i>
                            No active requests submitted recently.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Col: Important Announcements & Helpful Links -->
        <div class="space-y-6">
            <!-- Announcements -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-3">
                    <h3 class="text-sm font-bold text-white flex items-center">
                        <i class="fa-solid fa-bullhorn text-amber-400 mr-2"></i> Announcements
                    </h3>
                </div>

                <div class="space-y-3">
                    @foreach($dashboard['announcements'] as $item)
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/80 text-xs">
                            <div class="flex items-center justify-between text-[10px] text-slate-400 mb-1">
                                <span class="px-1.5 py-0.5 rounded font-bold uppercase {{ $item['priority'] === 'HIGH' ? 'bg-red-500/20 text-red-400' : 'bg-indigo-500/20 text-indigo-300' }}">
                                    {{ $item['priority'] }}
                                </span>
                                <span>{{ $item['date'] }}</span>
                            </div>
                            <h4 class="font-bold text-slate-100 mb-1">{{ $item['title'] }}</h4>
                            <p class="text-slate-400 text-[11px] leading-relaxed">{{ $item['summary'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Need Help / Ask HR Concierge Card -->
            <div class="bg-gradient-to-br from-indigo-950 to-slate-900 border border-indigo-800/40 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-sm shadow">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white">AI HR Concierge</h4>
                        <p class="text-[10px] text-indigo-300">Instant answers 24/7</p>
                    </div>
                </div>
                <p class="text-xs text-slate-300 mb-4 leading-relaxed">
                    Have questions regarding your remaining annual leave, medical benefits, shift hours, or payroll deductions?
                </p>
                <button onclick="toggleAiDrawer()" class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition shadow flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-comment-dots"></i>
                    <span>Start Conversation</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    async function handleClockToggle() {
        const btn = document.getElementById('clock-toggle-btn');
        btn.disabled = true;
        btn.classList.add('opacity-75');

        try {
            const res = await fetch('/api/me/attendance/clock', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Tenant-ID': '{{ $employee->tenant_id }}',
                    'X-Employee-ID': '{{ $employee->id }}'
                },
                body: JSON.stringify({ action: 'toggle' })
            });
            const data = await res.json();
            if (data.success) {
                window.showNotification('success', 'Attendance punch recorded.');
                setTimeout(() => location.reload(), 600);
            } else {
                window.showNotification('error', data.error?.message || 'Unable to record punch.', null, data.request_id);
            }
        } catch (err) {
            window.showNotification('error', 'Unable to record attendance punch. Please check your network connection.');
        } finally {
            btn.disabled = false;
            btn.classList.remove('opacity-75');
        }
    }
</script>
@endsection
