@extends('workforce-admin.layout')

@section('title', 'Operational Queues')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Operational Queues</h1>
            <p class="text-sm text-slate-400">Manage cross-domain work queues, pending lifecycle tasks, and SLA targets.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-slate-400 bg-slate-850/60 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Queue Name</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Default Priority</th>
                        <th class="py-3 px-4">SLA Target</th>
                        <th class="py-3 px-4">Pending Items</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($queues as $q)
                    <tr class="hover:bg-slate-850/50">
                        <td class="py-3 px-4 font-mono text-cyan-300">{{ $q->code }}</td>
                        <td class="py-3 px-4 font-semibold text-white">{{ $q->name }}</td>
                        <td class="py-3 px-4">{{ ucfirst($q->category) }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-slate-300">
                                {{ ucfirst($q->default_priority) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono">{{ $q->target_sla_hours }} hours</td>
                        <td class="py-3 px-4 font-mono font-bold text-cyan-400">{{ $q->items_count }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">No operational queues configured.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
