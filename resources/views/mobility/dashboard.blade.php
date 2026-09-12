@extends('mobility.layout')

@section('title', 'Global Mobility Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Global Mobility & Expatriate Hub</h1>
            <p class="text-sm text-slate-400">Enterprise cross-border assignment orchestration, cost modeling, relocation, and compliance tracking.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('mobility.programs.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-700 rounded-lg text-sm font-medium bg-slate-850 hover:bg-slate-800 text-slate-200 transition">
                <i class="fa-solid fa-plus mr-2 text-indigo-400"></i> New Program
            </a>
            <a href="{{ route('mobility.requests.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition">
                <i class="fa-solid fa-file-circle-plus mr-2"></i> Initiate Request
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Mobility Programs</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ number_format($totalPrograms) }}</div>
                <span class="text-xs text-indigo-400">Standardized Policies</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Assignments</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-passport"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ number_format($activeAssignments) }}</div>
                <span class="text-xs text-emerald-400">Expatriates in Host Countries</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending Requests</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ number_format($pendingRequests) }}</div>
                <span class="text-xs text-amber-400">Awaiting Eligibility / Approval</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Relocations</span>
                <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ number_format($activeRelocations) }}</div>
                <span class="text-xs text-purple-400">Moving & Settling-in Cases</span>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Business Travelers</span>
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                    <i class="fa-solid fa-plane-departure"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ number_format($activeTravelers) }}</div>
                <span class="text-xs text-cyan-400">Monitored for PE Risk</span>
            </div>
        </div>
    </div>

    <!-- Main Content Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Assignments -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-white">Recent Assignments</h2>
                <a href="{{ route('mobility.assignments.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">View all &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-slate-400 border-b border-slate-800 uppercase tracking-wider">
                        <tr>
                            <th class="py-2.5">Assignment</th>
                            <th class="py-2.5">Employee</th>
                            <th class="py-2.5">Route</th>
                            <th class="py-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($recentAssignments as $asn)
                        <tr class="hover:bg-slate-850/50">
                            <td class="py-3 font-mono text-indigo-300">{{ $asn->assignment_number }}</td>
                            <td class="py-3">{{ $asn->employee?->first_name }} {{ $asn->employee?->last_name }}</td>
                            <td class="py-3 font-medium">{{ $asn->home_country }} &rarr; {{ $asn->host_country }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $asn->status->value === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-700 text-slate-300' }}">
                                    {{ ucfirst(str_replace('_', ' ', $asn->status->value)) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-slate-500">No mobility assignments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Requests -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-white">Recent Requests</h2>
                <a href="{{ route('mobility.requests.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">View all &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-slate-400 border-b border-slate-800 uppercase tracking-wider">
                        <tr>
                            <th class="py-2.5">Request #</th>
                            <th class="py-2.5">Employee</th>
                            <th class="py-2.5">Eligibility</th>
                            <th class="py-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($recentRequests as $req)
                        <tr class="hover:bg-slate-850/50">
                            <td class="py-3 font-mono text-indigo-300">{{ $req->request_number }}</td>
                            <td class="py-3">{{ $req->employee?->first_name }} {{ $req->employee?->last_name }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $req->eligibility_status->value === 'eligible' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                                    {{ ucfirst(str_replace('_', ' ', $req->eligibility_status->value)) }}
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-slate-300">
                                    {{ ucfirst($req->status->value) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-slate-500">No requests submitted yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
