@extends('mobility.layout')

@section('title', 'Relocation Management')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Relocation & Settling-in Cases</h1>
            <p class="text-sm text-slate-400">Track shipments, household goods moves, temporary accommodation, and local registrations.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-slate-400 bg-slate-850/60 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Case #</th>
                        <th class="py-3 px-4">Employee</th>
                        <th class="py-3 px-4">Target Move Date</th>
                        <th class="py-3 px-4">Family Relocating</th>
                        <th class="py-3 px-4">Milestones</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($cases as $c)
                    <tr class="hover:bg-slate-850/50">
                        <td class="py-3 px-4 font-mono text-indigo-300">{{ $c->case_number }}</td>
                        <td class="py-3 px-4 font-semibold text-white">{{ $c->assignment?->employee?->first_name }} {{ $c->assignment?->employee?->last_name }}</td>
                        <td class="py-3 px-4">{{ $c->target_move_date?->format('Y-m-d') ?? 'TBD' }}</td>
                        <td class="py-3 px-4">
                            @if($c->family_relocating)
                                <span class="text-indigo-400 font-medium"><i class="fa-solid fa-users mr-1"></i> Yes</span>
                            @else
                                <span class="text-slate-500">Solo</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono">{{ $c->items->where('status', 'completed')->count() }} / {{ $c->items->count() }} items</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $c->status === 'completed' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                                {{ ucfirst($c->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">No relocation cases logged.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
