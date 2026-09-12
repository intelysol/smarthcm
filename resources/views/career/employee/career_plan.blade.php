@extends('layouts.career')

@section('title', 'My Development Plans')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-800/80 p-6 rounded-2xl border border-slate-700">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-list-check text-emerald-400"></i> Individual Development Plans
            </h1>
            <p class="text-sm text-slate-400 mt-1">Track target roles, milestones, and development actions.</p>
        </div>
    </div>

    <div class="space-y-6">
        @forelse($plans as $plan)
        <div class="bg-slate-800/60 p-6 rounded-2xl border border-slate-700/80 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-700/60 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-white">{{ $plan->targetJob?->title ?? 'General Capability Plan' }}</h3>
                    <p class="text-xs text-slate-400">Target Date: {{ $plan->target_date?->format('M d, Y') ?? 'Ongoing' }} &bull; Visibility: {{ ucwords($plan->visibility) }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        {{ ucwords($plan->status) }}
                    </span>
                    <span class="text-sm font-bold text-emerald-400 bg-slate-900/60 px-3 py-1 rounded-lg border border-slate-800">
                        Readiness: {{ $plan->readiness_score }}%
                    </span>
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Development Actions ({{ $plan->actions->count() }})</h4>
                @foreach($plan->actions as $action)
                <div class="flex items-center justify-between p-3.5 bg-slate-900/60 rounded-xl border border-slate-800 text-sm">
                    <div>
                        <span class="text-white font-medium block">{{ $action->title }}</span>
                        <span class="text-xs text-slate-400">{{ ucwords(str_replace('_', ' ', $action->action_type)) }} &bull; Due: {{ $action->due_date?->format('M d, Y') ?? 'N/A' }}</span>
                    </div>
                    <span class="text-xs font-bold text-slate-300">{{ $action->completion_percentage }}% Completed</span>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-slate-800/40 p-12 text-center rounded-2xl border border-slate-700">
            <p class="text-slate-400 text-sm">No development plans on record.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
