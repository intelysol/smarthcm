@extends('self-service.layout')

@section('title', 'My HR Service Requests')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">My HR Service Requests</h1>
            <p class="text-sm text-slate-400">Track the status, assigned queue, resolution timeline, and communication for your submitted requests.</p>
        </div>
        <a href="{{ route('self-service.catalog.index') }}" class="px-4 py-2 bg-teal-600 hover:bg-teal-500 text-white rounded-lg text-sm font-medium transition">
            <i class="fa-solid fa-plus mr-1"></i> New Request
        </a>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-850 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Request # &amp; Subject</th>
                        <th class="px-6 py-4">Service</th>
                        <th class="px-6 py-4">Submitted</th>
                        <th class="px-6 py-4">Assigned Team</th>
                        <th class="px-6 py-4">SLA Due</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($requests as $req)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-medium text-white">
                            <div>{{ $req->subject }}</div>
                            <div class="text-xs text-teal-400 font-mono">{{ $req->request_number }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-300">
                            {{ $req->service?->name }}
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-400">
                            {{ $req->created_at?->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-300">
                            {{ $req->assignedQueue?->name ?? 'Triage Queue' }}
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-300">
                            {{ $req->due_at?->format('Y-m-d H:i') ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('self-service.requests.show', $req) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                                View &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-500">No service requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($requests, 'links'))
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/60">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
