@extends('layouts.career')

@section('title', 'Career Path Progression')

@section('content')
<div class="space-y-6">
    <div class="bg-slate-800/80 p-6 rounded-2xl border border-slate-700">
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-stairs text-emerald-400"></i> Career Path & Step Progression
        </h1>
        <p class="text-sm text-slate-400 mt-1">{{ $path->title ?? 'Professional Track' }} &bull; Department: {{ $path->department?->department_name ?? 'Engineering' }}</p>
    </div>

    @if($path)
    <div class="relative border-l-2 border-slate-700 ml-6 space-y-8 py-4">
        @foreach($path->steps as $idx => $step)
        <div class="relative pl-8">
            <!-- Node dot -->
            <div class="absolute -left-3 top-1.5 w-6 h-6 rounded-full bg-slate-900 border-2 {{ $idx === 0 ? 'border-emerald-500 bg-emerald-500/20' : 'border-slate-600' }} flex items-center justify-center text-xs font-bold text-white">
                {{ $step->sequence }}
            </div>

            <div class="bg-slate-800/60 p-5 rounded-xl border border-slate-700/80 hover:border-emerald-500/40 transition">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">{{ $step->career_level ?? 'Level ' . $step->sequence }}</span>
                        <h3 class="text-lg font-bold text-white">{{ $step->job?->title ?? 'Role' }}</h3>
                    </div>
                    <span class="text-xs text-slate-400 bg-slate-900/60 px-3 py-1.5 rounded-lg border border-slate-800">
                        Min. Experience: {{ $step->minimum_experience_years }} yrs
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="bg-slate-900/40 p-3 rounded-lg border border-slate-800">
                        <span class="text-slate-400 font-semibold block mb-1">Required Skills:</span>
                        <span class="text-slate-300">{{ count($step->required_skills ?? []) }} Skills defined</span>
                    </div>
                    <div class="bg-slate-900/40 p-3 rounded-lg border border-slate-800">
                        <span class="text-slate-400 font-semibold block mb-1">Min Performance Rating:</span>
                        <span class="text-slate-300">{{ $step->performance_min_rating ?? '3.0' }} / 5.0</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-slate-800/40 p-12 text-center rounded-2xl border border-slate-700">
        <p class="text-slate-400 text-sm">No career path assigned to your current job family.</p>
    </div>
    @endif
</div>
@endsection
