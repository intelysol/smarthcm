@extends('shells.operations')

@section('title', 'Incident Management — Operations')

@section('content')
<div class="space-y-6">

    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-mono text-[#C9A227] font-semibold uppercase tracking-wider mb-1">
                <i class="fa-solid fa-triangle-exclamation text-[#C9A227]"></i>
                <span>Incident Response &amp; Mitigation</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Platform Incidents</h1>
            <p class="text-xs text-zinc-400 mt-0.5">Authoritative triage ledger for SEV-0 through SEV-4 incidents, event timelines, and root causes.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('operations.dashboard') }}" class="px-3.5 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 font-semibold text-xs transition border border-zinc-700 flex items-center">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Control Plane
            </a>
        </div>
    </div>

    <!-- Filter & Incident Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-zinc-800 text-zinc-400 uppercase tracking-wider">
                        <th class="pb-3 font-semibold">Incident</th>
                        <th class="pb-3 font-semibold">Severity</th>
                        <th class="pb-3 font-semibold">Status</th>
                        <th class="pb-3 font-semibold">Root Cause</th>
                        <th class="pb-3 font-semibold">Declared</th>
                        <th class="pb-3 font-semibold text-right">Resolved</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/50">
                    @forelse($incidents as $inc)
                    <tr class="hover:bg-zinc-800/30 transition">
                        <td class="py-3 font-medium text-white">
                            <div>{{ $inc->title }}</div>
                            <div class="text-[11px] text-zinc-400">{{ Str::limit($inc->description, 60) }}</div>
                        </td>
                        <td class="py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ in_array(strtolower($inc->severity), ['critical', 'sev-0', 'major', 'sev-1']) ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                                {{ $inc->severity }}
                            </span>
                        </td>
                        <td class="py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $inc->status === 'resolved' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-blue-500/10 text-blue-400' }}">
                                {{ $inc->status }}
                            </span>
                        </td>
                        <td class="py-3 text-zinc-300 font-mono text-[11px]">
                            {{ $inc->root_cause ?? 'Pending investigation' }}
                        </td>
                        <td class="py-3 text-zinc-400 font-mono text-[11px]">
                            {{ $inc->created_at?->format('Y-m-d H:i') }}
                        </td>
                        <td class="py-3 text-right text-zinc-400 font-mono text-[11px]">
                            {{ $inc->resolved_at ? $inc->resolved_at->format('Y-m-d H:i') : 'Active' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-zinc-500 text-xs">
                            <i class="fa-solid fa-check-circle text-emerald-400 text-lg mb-1 block"></i>
                            No incidents recorded in the system.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($incidents->hasPages())
        <div class="mt-4 pt-4 border-t border-zinc-800">
            {{ $incidents->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
