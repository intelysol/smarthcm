@extends('layouts.career')

@section('title', 'My Career Hub')

@section('content')
<div class="space-y-6">
    <!-- Top Hero Banner -->
    <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-6 rounded-2xl border border-slate-700 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xl border border-emerald-500/30">
                    {{ substr($employee?->first_name ?? 'U', 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $employee?->first_name }} {{ $employee?->last_name }}</h1>
                    <p class="text-sm text-slate-400">{{ $employee?->designation?->title ?? 'Employee' }} &bull; {{ $employee?->department?->department_name ?? 'Operations' }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="bg-slate-800/80 px-4 py-3 rounded-xl border border-slate-700 text-center">
                <span class="text-xs text-slate-400 block font-medium">Target Role</span>
                <span class="text-sm font-bold text-white">{{ $aspiration?->targetJob?->title ?? 'Leadership Track' }}</span>
            </div>
            <div class="bg-slate-800/80 px-4 py-3 rounded-xl border border-slate-700 text-center">
                <span class="text-xs text-slate-400 block font-medium">Readiness</span>
                <span class="text-sm font-bold text-emerald-400">{{ $activePlan ? ucwords(str_replace('_', ' ', $activePlan->readiness_level)) : 'Developing' }}</span>
            </div>
        </div>
    </div>

    <!-- Active Development Plan & Opportunities -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Active Plan -->
        <div class="lg:col-span-2 bg-slate-800/60 p-6 rounded-2xl border border-slate-700/80 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-route text-emerald-400"></i> Active Individual Development Plan (IDP)
                </h2>
                @if($activePlan)
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    {{ ucwords($activePlan->status) }}
                </span>
                @endif
            </div>

            @if($activePlan)
            <div class="p-4 bg-slate-900/60 rounded-xl border border-slate-800 space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-300">Target Role: <strong>{{ $activePlan->targetJob?->title ?? 'Senior Engineer' }}</strong></span>
                    <span class="text-slate-400 text-xs">Target Date: {{ $activePlan->target_date?->format('M Y') ?? 'Ongoing' }}</span>
                </div>
                <div class="space-y-1">
                    <div class="flex justify-between text-xs text-slate-400">
                        <span>Overall Capability Readiness</span>
                        <span class="font-bold text-emerald-400">{{ $activePlan->readiness_score }}%</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2">
                        <div class="bg-emerald-400 h-2 rounded-full" style="width: {{ $activePlan->readiness_score }}%"></div>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <h3 class="text-sm font-semibold text-slate-300">Key Development Actions</h3>
                @forelse($activePlan->actions as $act)
                <div class="flex items-center justify-between p-3 bg-slate-800/90 rounded-lg border border-slate-700 text-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-regular fa-circle-check text-emerald-400"></i>
                        <div>
                            <span class="text-white font-medium block">{{ $act->title }}</span>
                            <span class="text-xs text-slate-400">{{ ucwords(str_replace('_', ' ', $act->action_type)) }}</span>
                        </div>
                    </div>
                    <span class="text-xs text-slate-300">{{ $act->completion_percentage }}%</span>
                </div>
                @empty
                <p class="text-xs text-slate-400">No actions logged yet.</p>
                @endforelse
            </div>
            @else
            <div class="p-8 text-center bg-slate-900/40 rounded-xl border border-slate-800">
                <p class="text-slate-400 text-sm">No active career plan. Create a plan to track role milestones.</p>
            </div>
            @endif
        </div>

        <!-- Right 1 Col: Internal Opportunities -->
        <div class="bg-slate-800/60 p-6 rounded-2xl border border-slate-700/80 space-y-4">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-compass text-teal-400"></i> Internal Mobility
            </h2>
            <p class="text-xs text-slate-400">Matching opportunities based on your skills and experience:</p>

            <div class="space-y-3">
                @forelse($matchingOpportunities as $opp)
                <div class="p-4 bg-slate-900/60 rounded-xl border border-slate-700/60 hover:border-emerald-500/40 transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-sm font-bold text-white">{{ $opp['job_title'] }}</h4>
                            <span class="text-xs text-slate-400">Readiness: {{ ucwords(str_replace('_', ' ', $opp['readiness_level'])) }}</span>
                        </div>
                        <span class="text-xs font-bold text-emerald-400">{{ $opp['match_percentage'] }}% Match</span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400">No internal match recommendations available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
