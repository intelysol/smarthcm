@extends('expenses.layout')

@section('title', 'Accounting & GL Exports')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">General Ledger Accounting &amp; Exports</h1>
            <p class="text-sm text-slate-400">Generate balanced double-entry accounting export payloads for Finance and enforce period locking controls.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-950/60 text-emerald-400 border border-emerald-800/50">
                <i class="fa-solid fa-arrows-split-up-and-left mr-1"></i> Double-Entry Balanced
            </span>
        </div>
    </div>

    <!-- Quick Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Generate Export Form Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
            <h2 class="text-base font-bold text-white mb-1"><i class="fa-solid fa-file-export mr-2 text-indigo-400"></i> Generate GL Export Batch</h2>
            <p class="text-xs text-slate-400 mb-4">Export approved expense claims into staged GL debit/credit entries for Finance integration.</p>

            <form action="{{ route('expenses.accounting.export') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Period Start</label>
                        <input type="date" name="start_date" value="{{ now()->startOfMonth()->toDateString() }}" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Period End</label>
                        <input type="date" name="end_date" value="{{ now()->endOfMonth()->toDateString() }}" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                </div>
                <button type="submit" class="w-full py-2 px-4 rounded-lg text-sm font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/30 transition">
                    <i class="fa-solid fa-play mr-1"></i> Generate Export Batch
                </button>
            </form>
        </div>

        <!-- Lock Period Form Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
            <h2 class="text-base font-bold text-white mb-1"><i class="fa-solid fa-lock mr-2 text-amber-400"></i> Lock Accounting Period</h2>
            <p class="text-xs text-slate-400 mb-4">Prevent claims and expenses from being added, edited, or modified in finalized fiscal periods.</p>

            <form action="{{ route('expenses.accounting.lock') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Period Label</label>
                    <input type="text" name="period_name" placeholder="e.g. FY2026-Q3 Final" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ now()->subMonth()->startOfMonth()->toDateString() }}" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ now()->subMonth()->endOfMonth()->toDateString() }}" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                </div>
                <button type="submit" class="w-full py-2 px-4 rounded-lg text-sm font-semibold bg-amber-600 hover:bg-amber-500 text-white shadow-md shadow-amber-600/30 transition">
                    <i class="fa-solid fa-lock mr-1"></i> Enforce Period Lock
                </button>
            </form>
        </div>
    </div>

    <!-- Export Batches Table -->
    <div class="space-y-3">
        <h2 class="text-lg font-bold text-white">Generated GL Export Batches</h2>
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-850 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-6 py-4">Batch Number</th>
                            <th class="px-6 py-4">Export Date</th>
                            <th class="px-6 py-4">Period Scope</th>
                            <th class="px-6 py-4">Total Amount</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">GL Entries</th>
                            <th class="px-6 py-4">Exported By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($exports as $batch)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-6 py-4 font-mono font-bold text-indigo-400">
                                {{ $batch->batch_number }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-300">{{ $batch->export_date }}</td>
                            <td class="px-6 py-4 text-xs font-mono text-slate-400">
                                {{ $batch->period_start }} &rarr; {{ $batch->period_end }}
                            </td>
                            <td class="px-6 py-4 text-white font-bold font-mono">
                                ${{ number_format((float)$batch->total_amount, 2) }} {{ $batch->currency }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-950/60 text-emerald-400 border border-emerald-800/50">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400">
                                {{ $batch->gl_export_payload['entries_count'] ?? 0 }} lines
                                @if(!empty($batch->gl_export_payload['is_balanced']))
                                    <span class="text-emerald-400 ml-1"><i class="fa-solid fa-circle-check"></i> Balanced</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-300">
                                {{ $batch->exportedBy?->name ?? 'System User' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">No accounting export batches generated yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Active Period Locks -->
    <div class="space-y-3">
        <h2 class="text-lg font-bold text-white">Active Period Locks</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($periodLocks as $lock)
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white">{{ $lock->period_name }}</h3>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-950/60 text-rose-400 border border-rose-800/50">
                        <i class="fa-solid fa-lock mr-1"></i> Locked
                    </span>
                </div>
                <div class="text-xs font-mono text-slate-400 mt-2">
                    {{ $lock->start_date }} &rarr; {{ $lock->end_date }}
                </div>
                <div class="text-xs text-slate-500 mt-2">
                    Enforced by {{ $lock->lockedBy?->name ?? 'Finance Admin' }} on {{ $lock->locked_at ? \Carbon\Carbon::parse($lock->locked_at)->format('Y-m-d') : 'N/A' }}
                </div>
            </div>
            @empty
            <div class="col-span-3 bg-slate-900 border border-slate-800 rounded-xl p-6 text-center text-slate-500">
                No active accounting period locks.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
