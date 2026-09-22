@extends('portal.layout')

@section('title', 'Manager Workbench')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="bg-gradient-to-r from-amber-950/40 via-slate-900 to-slate-900 border border-slate-800 p-6 rounded-2xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xl">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-600/20 border border-amber-500/30 flex items-center justify-center text-amber-400 text-2xl font-black shadow-inner">
                <i class="fa-solid fa-users-gear"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl font-bold text-white tracking-wide">Manager Daily Workbench</h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">MSS</span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">
                    Supervising {{ $dashboard['team_summary']['total_members'] }} direct reports across {{ $manager->department?->name ?? 'Department' }}
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('portal.manager.approvals') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-500/20 transition flex items-center space-x-2">
                <i class="fa-solid fa-stamp"></i>
                <span>Approval Inbox ({{ $dashboard['pending_approvals_count'] }})</span>
            </a>
        </div>
    </div>

    <!-- Team Attendance & Capacity Metrics Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Present Today -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Present Today</span>
            <div class="flex items-baseline space-x-2 mt-2">
                <span class="text-3xl font-extrabold text-emerald-400">{{ $dashboard['team_summary']['present_today'] }}</span>
                <span class="text-xs text-slate-400">/ {{ $dashboard['team_summary']['total_members'] }} members</span>
            </div>
            <div class="text-[11px] text-emerald-400 mt-1 font-semibold">
                {{ $dashboard['team_summary']['attendance_rate'] }}% attendance rate
            </div>
        </div>

        <!-- Metric 2: On Leave -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Approved Leave Today</span>
            <div class="flex items-baseline space-x-2 mt-2">
                <span class="text-3xl font-extrabold text-teal-400">{{ $dashboard['team_summary']['on_leave_today'] }}</span>
                <span class="text-xs text-slate-400">on scheduled leave</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">
                Covered in capacity plan
            </div>
        </div>

        <!-- Metric 3: Absent / Unscheduled -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Unrecorded / Absent</span>
            <div class="flex items-baseline space-x-2 mt-2">
                <span class="text-3xl font-extrabold text-amber-400">{{ $dashboard['team_summary']['absent_today'] }}</span>
                <span class="text-xs text-slate-400">members</span>
            </div>
            <div class="text-[11px] text-amber-400 mt-1">
                Needs punch or confirmation
            </div>
        </div>

        <!-- Metric 4: Team Capacity -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Capacity Utilization</span>
            <div class="flex items-baseline space-x-2 mt-2">
                <span class="text-3xl font-extrabold text-indigo-400">{{ $dashboard['capacity']['utilization_rate'] }}%</span>
            </div>
            <div class="text-[11px] text-indigo-300 mt-1">
                {{ $dashboard['capacity']['scheduled_hours_today'] }} total scheduled hrs
            </div>
        </div>
    </div>

    <!-- Team Alerts Section -->
    @if(!empty($dashboard['alerts']))
        <div class="bg-slate-900 border border-amber-500/30 rounded-2xl p-5 shadow-sm space-y-3">
            <h3 class="text-sm font-bold text-amber-400 flex items-center">
                <i class="fa-solid fa-bell mr-2"></i> Actionable Team Alerts
            </h3>
            <div class="space-y-2">
                @foreach($dashboard['alerts'] as $alert)
                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-2 text-slate-200">
                            <i class="fa-solid fa-circle-exclamation text-amber-400"></i>
                            <span>{{ $alert['title'] }}</span>
                        </div>
                        <a href="{{ $alert['action_route'] }}" class="px-3 py-1 rounded bg-slate-800 hover:bg-slate-700 text-amber-300 font-semibold text-[11px]">
                            {{ $alert['action_label'] }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Team Roster Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white flex items-center">
                <i class="fa-solid fa-users mr-2 text-indigo-400"></i> Direct Reports Roster
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[10px] uppercase font-bold text-slate-400 border-b border-slate-800 bg-slate-950/50">
                    <tr>
                        <th class="py-2.5 px-3">Team Member</th>
                        <th class="py-2.5 px-3">Role &amp; Code</th>
                        <th class="py-2.5 px-3">Today's Attendance</th>
                        <th class="py-2.5 px-3">Punches</th>
                        <th class="py-2.5 px-3">Contact</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($roster as $member)
                        <tr class="hover:bg-slate-850/50 transition">
                            <td class="py-3 px-3 font-semibold text-white flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center font-bold text-indigo-400">
                                    {{ substr($member['name'], 0, 1) }}
                                </div>
                                <span>{{ $member['name'] }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <div class="text-slate-200">{{ $member['designation'] }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $member['employee_code'] }}</div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold 
                                    {{ $member['attendance_status'] === 'CLOCKED_IN' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}
                                    {{ $member['attendance_status'] === 'ON_LEAVE' ? 'bg-teal-500/20 text-teal-400 border border-teal-500/30' : '' }}
                                    {{ $member['attendance_status'] === 'CLOCKED_OUT' ? 'bg-slate-800 text-slate-400' : '' }}
                                    {{ $member['attendance_status'] === 'NOT_CLOCKED_IN' ? 'bg-amber-500/10 text-amber-400' : '' }}">
                                    {{ str_replace('_', ' ', $member['attendance_status']) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-mono text-[11px] text-slate-400">
                                In: {{ $member['attendance_detail']['clock_in'] ?? '--:--' }} | Out: {{ $member['attendance_detail']['clock_out'] ?? '--:--' }}
                            </td>
                            <td class="py-3 px-3 text-slate-400">
                                {{ $member['email'] ?? $member['phone'] ?? 'On file' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">
                                No direct reports mapped to your managerial hierarchy yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
