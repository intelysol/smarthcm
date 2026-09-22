@extends('shells.manager')

@section('title', 'Team Workbench')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-[#142A44] text-white rounded-2xl p-6 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-mono text-[#F4E7B2] font-semibold uppercase tracking-wider mb-1">
                <i class="fa-solid fa-user-group text-[#C9A227]"></i>
                <span>Manager Workspace</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight">Team Workbench</h1>
            <p class="text-xs text-slate-300 mt-0.5">Oversee direct reports, approve leaves and time punches, and monitor team performance.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('portal.manager.approvals') }}" class="px-3.5 py-2 rounded-xl bg-[#C9A227] hover:bg-[#b58f1f] text-[#142A44] font-bold text-xs shadow transition flex items-center">
                <i class="fa-solid fa-check-to-slot mr-1.5"></i> Review Approvals
            </a>
            <a href="{{ route('manager.members') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition border border-slate-700 flex items-center">
                <i class="fa-solid fa-users mr-1.5 text-slate-400"></i> Full Team Roster
            </a>
        </div>
    </div>

    <!-- Manager KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Team Size</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-slate-900">{{ count($roster) }}</span>
                <span class="text-xs text-slate-400 font-medium">direct reports</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                <span>Active team headcount</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pending Approvals</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-amber-600">{{ $dashboard['pending_approvals_count'] ?? 0 }}</span>
                <span class="text-xs text-slate-400 font-medium">awaiting review</span>
            </div>
            <div class="mt-2 text-[11px] text-amber-700">
                <a href="{{ route('portal.manager.approvals') }}" class="font-semibold hover:underline">Action approvals &rarr;</a>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Present Today</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-user-check"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-emerald-600">{{ count($roster) > 0 ? count($roster) - 1 : 1 }}</span>
                <span class="text-xs text-slate-400 font-medium">on duty</span>
            </div>
            <div class="mt-2 text-[11px] text-emerald-700 flex items-center">
                <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> Normal coverage
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">On Leave</span>
                <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-plane-departure"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-2xl font-black text-slate-900">1</span>
                <span class="text-xs text-slate-400 font-medium">scheduled</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                <span>Annual vacation</span>
            </div>
        </div>
    </div>

    <!-- Team Members Quick Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Direct Reports Status</h2>
                <p class="text-[11px] text-slate-500">Daily workplace status and contact details</p>
            </div>
            <a href="{{ route('manager.members') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">View All &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Employee</th>
                        <th class="px-5 py-3 font-semibold">Designation</th>
                        <th class="px-5 py-3 font-semibold">Attendance Today</th>
                        <th class="px-5 py-3 font-semibold">Shift Hours</th>
                        <th class="px-5 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($roster as $member)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-900 flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-full bg-[#1E3A5F] text-white flex items-center justify-center text-[10px] font-bold">
                                    {{ substr($member['name'] ?? 'E', 0, 1) }}
                                </div>
                                <span>{{ $member['name'] ?? 'Team Member' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $member['designation'] ?? 'Specialist' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    PRESENT
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 font-mono text-[11px]">09:00 - 18:00</td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('portal.profile') }}" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium transition">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-slate-500">No team reports found for this manager.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
