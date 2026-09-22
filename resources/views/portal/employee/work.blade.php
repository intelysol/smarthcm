@extends('portal.layout')

@section('title', 'My Work & Attendance')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-wide">My Work &amp; Attendance</h1>
            <p class="text-xs text-slate-400 mt-1">Review your shifts, record attendance punches, and view recent timesheet entries.</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-xs text-slate-400">Current status:</span>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $dashboard['attendance']['status'] === 'CLOCKED_IN' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-300' }}">
                {{ str_replace('_', ' ', $dashboard['attendance']['status']) }}
            </span>
        </div>
    </div>

    <!-- Today's Work Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Card 1: Shift Details -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block mb-2">Today's Assigned Shift</span>
            <div class="text-lg font-bold text-white">{{ $dashboard['schedule']['shift_name'] }}</div>
            <div class="mt-2 text-xs text-indigo-400 font-medium flex items-center">
                <i class="fa-regular fa-clock mr-1.5"></i>
                {{ $dashboard['schedule']['start_time'] }} &ndash; {{ $dashboard['schedule']['end_time'] }}
            </div>
            <div class="mt-1 text-xs text-slate-400 flex items-center">
                <i class="fa-solid fa-location-dot mr-1.5"></i>
                {{ $dashboard['schedule']['location'] }}
            </div>
        </div>

        <!-- Card 2: Attendance Punch -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block mb-2">Live Attendance Clock</span>
            <div class="text-xs text-slate-300">
                Clock In: <span class="font-bold text-white">{{ $dashboard['attendance']['clock_in'] ?? '--:--' }}</span>
            </div>
            <div class="text-xs text-slate-300 mt-1">
                Clock Out: <span class="font-bold text-white">{{ $dashboard['attendance']['clock_out'] ?? '--:--' }}</span>
            </div>
            <div class="mt-3">
                <button onclick="handleWorkClockToggle()" id="work-clock-btn" class="w-full py-2 rounded-xl text-xs font-bold shadow transition flex items-center justify-center space-x-2 {{ $dashboard['attendance']['status'] === 'CLOCKED_IN' ? 'bg-amber-600 hover:bg-amber-500 text-white' : 'bg-emerald-600 hover:bg-emerald-500 text-white' }}">
                    <i class="fa-solid fa-fingerprint"></i>
                    <span>{{ $dashboard['attendance']['status'] === 'CLOCKED_IN' ? 'Clock Out Now' : 'Clock In Now' }}</span>
                </button>
            </div>
        </div>

        <!-- Card 3: Timesheet Summary -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block mb-2">Current Period Hours</span>
            <div class="flex items-baseline space-x-2">
                <span class="text-2xl font-black text-indigo-400">{{ $dashboard['attendance']['worked_hours'] }}</span>
                <span class="text-xs text-slate-400">hours recorded today</span>
            </div>
            <div class="mt-3 text-[11px] text-slate-400">
                Weekly target: 40.0 hours &bull; Overtime: 0.0 hrs
            </div>
        </div>
    </div>

    <!-- Attendance History Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-3">
            <h3 class="text-sm font-bold text-white flex items-center">
                <i class="fa-solid fa-clock-rotate-left mr-2 text-indigo-400"></i> Recent Attendance Sessions
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[10px] uppercase font-bold text-slate-400 border-b border-slate-800 bg-slate-950/50">
                    <tr>
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Clock In</th>
                        <th class="py-2.5 px-3">Clock Out</th>
                        <th class="py-2.5 px-3">Worked Hours</th>
                        <th class="py-2.5 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($history as $item)
                        <tr class="hover:bg-slate-850/50 transition">
                            <td class="py-3 px-3 font-semibold text-slate-200">{{ date('D, M d, Y', strtotime($item->session_date)) }}</td>
                            <td class="py-3 px-3">{{ $item->clock_in ? date('H:i', strtotime($item->clock_in)) : '--:--' }}</td>
                            <td class="py-3 px-3">{{ $item->clock_out ? date('H:i', strtotime($item->clock_out)) : '--:--' }}</td>
                            <td class="py-3 px-3 font-mono font-medium">{{ $item->worked_hours ?? 0 }} hrs</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ !empty($item->clock_out) ? 'bg-emerald-500/20 text-emerald-400' : 'bg-indigo-500/20 text-indigo-300' }}">
                                    {{ $item->status ?? 'COMPLETED' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">No recent attendance session records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    async function handleWorkClockToggle() {
        const btn = document.getElementById('work-clock-btn');
        btn.disabled = true;
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
                window.showNotification('success', 'Attendance punch recorded successfully.');
                setTimeout(() => location.reload(), 600);
            } else {
                window.showNotification('error', data.error?.message || 'Unable to record attendance.', null, data.request_id);
            }
        } catch (err) {
            window.showNotification('error', 'Unable to record attendance. Please check network connection.');
        } finally {
            btn.disabled = false;
        }
    }
</script>
@endsection
