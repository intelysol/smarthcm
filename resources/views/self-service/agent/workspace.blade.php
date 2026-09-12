@extends('self-service.layout')

@section('title', 'HR Agent Service Desk Workspace')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">HR Agent Service Desk</h1>
            <p class="text-sm text-slate-400">Queue management, ticket dispatching, internal notes, resolution tracking &amp; SLA compliance.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-900/60 text-amber-300 border border-amber-700/50">
                Agent Workstation Active
            </span>
        </div>
    </div>

    <!-- Active Queues Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach($queues as $q)
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono text-teal-400">{{ $q->code }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-300">{{ ucfirst(str_replace('_', ' ', $q->assignment_method)) }}</span>
            </div>
            <div class="text-lg font-bold text-white mt-2">{{ $q->name }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ $q->description }}</div>
            <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-500">Active Tickets</span>
                <span class="text-sm font-bold text-teal-400">{{ $q->requests_count }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Assigned to Me & Unassigned Tickets -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Assigned To Me -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-base font-bold text-white mb-4">My Assigned Tickets ({{ count($assignedToMe) }})</h2>
            <div class="divide-y divide-slate-800">
                @forelse($assignedToMe as $req)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <a href="{{ route('self-service.requests.show', $req) }}" class="text-sm font-semibold text-white hover:text-teal-400 transition">{{ $req->subject }}</a>
                        <div class="text-xs text-slate-400">{{ $req->employee?->first_name }} {{ $req->employee?->last_name }} &bull; {{ $req->service?->name }}</div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-slate-800 text-slate-300">
                            {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-sm text-slate-500">No tickets currently assigned to you.</div>
                @endforelse
            </div>
        </div>

        <!-- Unassigned Queue -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-base font-bold text-white mb-4">Unassigned Queue ({{ count($unassigned) }})</h2>
            <div class="divide-y divide-slate-800">
                @forelse($unassigned as $req)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <a href="{{ route('self-service.requests.show', $req) }}" class="text-sm font-semibold text-white hover:text-amber-400 transition">{{ $req->subject }}</a>
                        <div class="text-xs text-slate-400">{{ $req->employee?->first_name }} {{ $req->employee?->last_name }} &bull; {{ $req->created_at?->diffForHumans() }}</div>
                    </div>
                    <div class="text-right">
                        <a href="{{ route('self-service.requests.show', $req) }}" class="px-3 py-1 rounded bg-teal-600 hover:bg-teal-500 text-white text-xs font-medium transition">
                            Claim Ticket
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-sm text-slate-500">Queue is clear! No unassigned tickets.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
