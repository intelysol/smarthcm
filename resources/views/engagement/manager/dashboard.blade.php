@extends('layouts.engagement')

@section('title', 'Team Engagement & Culture')

@section('content')
<div class="space-y-8">
    <div class="bg-gradient-to-r from-slate-800 to-slate-800/80 p-6 rounded-2xl border border-slate-700/60 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-users text-pink-400"></i> Team Engagement Hub
            </h1>
            <p class="text-slate-400 text-sm mt-1">Review team sentiment trends, action plans, and participation metrics with built-in privacy protection.</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
            <i class="fa-solid fa-shield-halved mr-1.5"></i> Min 5 Responses Anonymity Rule
        </span>
    </div>

    <!-- Active Campaigns -->
    <div>
        <h2 class="text-lg font-semibold text-white mb-4">Survey Campaigns</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($campaigns as $camp)
            <div class="bg-slate-800/60 border border-slate-700/60 rounded-xl p-5 hover:border-pink-500/40 transition flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $camp->status === 'active' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-300' }}">
                            {{ ucfirst($camp->status) }}
                        </span>
                        <span class="text-xs text-slate-400">{{ $camp->start_date?->format('M d') }} - {{ $camp->end_date?->format('M d, Y') }}</span>
                    </div>
                    <h3 class="font-bold text-white text-base">{{ $camp->name }}</h3>
                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $camp->description }}</p>
                </div>
                <div class="mt-6 flex justify-between items-center pt-4 border-t border-slate-700/40">
                    <span class="text-xs text-slate-400">{{ $camp->survey?->title }}</span>
                    <a href="{{ route('engagement.manager.results', $camp->id) }}" class="px-3 py-1.5 bg-pink-500/10 hover:bg-pink-500/20 text-pink-400 border border-pink-500/30 text-xs font-semibold rounded-lg transition">
                        View Team Results &rarr;
                    </a>
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400 col-span-full">No active or past survey campaigns found.</p>
            @endforelse
        </div>
    </div>

    <!-- Action Plans -->
    <div>
        <h2 class="text-lg font-semibold text-white mb-4">Team Action Plans</h2>
        <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl overflow-hidden shadow-xl">
            <table class="min-w-full divide-y divide-slate-700/60 text-xs text-left">
                <thead class="bg-slate-900/50 text-slate-400 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Action Plan</th>
                        <th class="px-6 py-3.5">Scope</th>
                        <th class="px-6 py-3.5">Priority</th>
                        <th class="px-6 py-3.5">Due Date</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Items</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40 text-slate-200">
                    @forelse($actionPlans as $plan)
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="px-6 py-4 font-semibold text-white">{{ $plan->title }}</td>
                        <td class="px-6 py-4">{{ ucfirst($plan->scope_type) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded font-semibold {{ $plan->priority === 'critical' ? 'bg-rose-500/20 text-rose-300' : 'bg-slate-700 text-slate-300' }}">
                                {{ ucfirst($plan->priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-400">{{ $plan->due_date?->format('M d, Y') ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded font-semibold {{ $plan->status === 'completed' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }}">
                                {{ ucfirst(str_replace('_', ' ', $plan->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-400">{{ $plan->items?->count() ?? 0 }} tasks</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">No action plans assigned yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
