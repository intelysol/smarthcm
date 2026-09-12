@extends('layouts.career')

@section('title', 'Team Skill Matrix')

@section('content')
<div class="space-y-6">
    <div class="bg-slate-800/80 p-6 rounded-2xl border border-slate-700">
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-table-cells text-emerald-400"></i> Team Skill Coverage Matrix
        </h1>
        <p class="text-sm text-slate-400 mt-1">Direct subordinate capability inventory and verification status.</p>
    </div>

    <div class="bg-slate-800/60 rounded-2xl border border-slate-700/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-900/80 text-xs uppercase font-semibold text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Designation</th>
                        <th class="px-6 py-4">Registered Skills</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($team as $member)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-bold text-white">{{ $member->first_name }} {{ $member->last_name }}</td>
                        <td class="px-6 py-4 text-slate-400">{{ $member->designation?->title ?? 'Staff' }}</td>
                        <td class="px-6 py-4">
                            <span class="bg-emerald-500/10 text-emerald-400 text-xs px-2.5 py-1 rounded-md border border-emerald-500/20">
                                {{ $member->skills->count() }} Skills
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-xs text-emerald-400 font-semibold hover:underline">View Skills &rarr;</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-400 text-sm">No team members found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
