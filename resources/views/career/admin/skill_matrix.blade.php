@extends('layouts.career')

@section('title', 'Organization Skill Matrix')

@section('content')
<div class="space-y-6">
    <div class="bg-slate-800/80 p-6 rounded-2xl border border-slate-700">
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-border-all text-emerald-400"></i> Organization Skill Heatmap & Matrix
        </h1>
        <p class="text-sm text-slate-400 mt-1">Cross-departmental skill inventory and proficiency distribution.</p>
    </div>

    <div class="bg-slate-800/60 rounded-2xl border border-slate-700/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/80 uppercase font-semibold text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="px-5 py-4">Employee</th>
                        <th class="px-5 py-4">Department</th>
                        @foreach($skills->take(6) as $s)
                        <th class="px-4 py-4 text-center">{{ $s->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($employees as $emp)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3 font-bold text-white whitespace-nowrap">{{ $emp->first_name }} {{ $emp->last_name }}</td>
                        <td class="px-5 py-3 text-slate-400 whitespace-nowrap">{{ $emp->department?->department_name ?? 'Operations' }}</td>
                        @foreach($skills->take(6) as $s)
                        @php
                            $lvl = $emp->skills->firstWhere('skill_id', $s->id)?->current_level ?? 0;
                        @endphp
                        <td class="px-4 py-3 text-center">
                            @if($lvl > 0)
                            <span class="inline-block px-2 py-0.5 rounded font-bold text-xs {{ $lvl >= 4 ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($lvl >= 2 ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : 'bg-slate-700 text-slate-300') }}">
                                L{{ $lvl }}
                            </span>
                            @else
                            <span class="text-slate-600">-</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-8 text-center text-slate-400">No employees registered.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
