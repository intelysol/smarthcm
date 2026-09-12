@extends('layouts.employee_relations')

@section('title', 'Evidence - Case ' . $case->case_number)

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <div class="text-xs text-indigo-400 font-mono">Case {{ $case->case_number }}</div>
            <h1 class="text-2xl font-bold text-white">Evidence & Chain-of-Custody Workspace</h1>
        </div>
        <a href="{{ route('er.admin.case', $case) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-xs border border-slate-700">
            &larr; Back to Case
        </a>
    </div>

    <!-- Evidence Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-left text-sm text-slate-300">
                <thead class="bg-slate-950 text-xs uppercase tracking-wider text-slate-400 font-medium">
                    <tr>
                        <th class="px-6 py-3">Evidence ID</th>
                        <th class="px-6 py-3">Title & Type</th>
                        <th class="px-6 py-3">Source</th>
                        <th class="px-6 py-3">SHA-256 Checksum</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Collected By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($evidence as $item)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-mono font-medium text-white">{{ $item->evidence_number }}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-white">{{ $item->title }}</div>
                            <div class="text-xs text-slate-400">{{ ucfirst($item->evidence_type) }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs">{{ $item->source }}</td>
                        <td class="px-6 py-4 font-mono text-[10px] text-slate-400">
                            {{ $item->sha256_hash ? substr($item->sha256_hash, 0, 16) . '...' : 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-800 text-emerald-400">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-400">{{ $item->collector?->name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">No evidence items logged.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
