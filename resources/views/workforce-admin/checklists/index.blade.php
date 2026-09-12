@extends('workforce-admin.layout')

@section('title', 'Operational Checklists')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">HR Operational Checklists</h1>
            <p class="text-sm text-slate-400 mt-1">Lifecycle transition task execution across Onboarding, Transfers, and Offboarding.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="px-3 py-1 bg-teal-500/10 text-teal-400 border border-teal-500/20 rounded-lg text-xs font-semibold">
                {{ $checklists->total() }} Active Instances
            </span>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-850 text-xs uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Reference #</th>
                        <th class="px-6 py-3 font-semibold">Template</th>
                        <th class="px-6 py-3 font-semibold">Employee</th>
                        <th class="px-6 py-3 font-semibold">Status</th>
                        <th class="px-6 py-3 font-semibold">Progress</th>
                        <th class="px-6 py-3 font-semibold">Target Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($checklists as $chk)
                        @php
                            $totalItems = $chk->items->count();
                            $completedItems = $chk->items->where('status', 'completed')->count();
                            $progressPct = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-850/50 transition">
                            <td class="px-6 py-4 font-mono text-cyan-400 font-semibold">{{ $chk->reference_number }}</td>
                            <td class="px-6 py-4 font-medium text-white">{{ $chk->template?->name ?? 'Custom Checklist' }}</td>
                            <td class="px-6 py-4">
                                @if($chk->employee)
                                    <div class="font-medium text-white">{{ $chk->employee->first_name }} {{ $chk->employee->last_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $chk->employee->employee_number }}</div>
                                @else
                                    <span class="text-slate-500 italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($chk->status === 'completed')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Completed</span>
                                @elseif($chk->status === 'in_progress')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">In Progress</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">Open</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="w-full bg-slate-800 rounded-full h-2 max-w-[120px] mb-1">
                                    <div class="bg-cyan-500 h-2 rounded-full" style="width: {{ $progressPct }}%"></div>
                                </div>
                                <span class="text-xs text-slate-400">{{ $completedItems }} / {{ $totalItems }} done ({{ $progressPct }}%)</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-slate-400">
                                {{ $chk->target_completion_date ? \Carbon\Carbon::parse($chk->target_completion_date)->format('M d, Y') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <i class="fa-solid fa-square-check text-3xl mb-2 text-slate-600 block"></i>
                                No active operational checklists.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($checklists->hasPages())
            <div class="px-6 py-4 border-t border-slate-800 bg-slate-850">
                {{ $checklists->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
