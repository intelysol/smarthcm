@extends('benefits.layout')

@section('title', 'Benefit Programs')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-emerald-400"></i> Benefit Programs
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Configure organizational benefit umbrella programs (Health & Medical, Protection, Retirement, Wellness).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-emerald-600/20 flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Program
            </button>
        </div>
    </div>

    <!-- Programs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($programs as $program)
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 hover:border-slate-700 transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-emerald-950 text-emerald-400 border border-emerald-800/50">
                            {{ str_replace('_', ' ', $program->category) }}
                        </span>
                        <span class="text-xs font-mono text-slate-500">v{{ $program->versions->count() ?: 1 }}</span>
                    </div>
                    <h3 class="text-lg font-bold text-white">{{ $program->name }}</h3>
                    <p class="text-xs font-mono text-slate-400 mt-0.5">{{ $program->code }}</p>
                    <p class="text-sm text-slate-300 mt-3 line-clamp-2">{{ $program->description ?? 'Comprehensive benefit package offering multi-tier coverage for employees.' }}</p>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-800/80">
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span><i class="fa-solid fa-shield-halved text-emerald-400 mr-1"></i> {{ $program->plans->count() }} Linked Plans</span>
                        <span class="inline-flex items-center gap-1.5 text-emerald-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Active
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full p-8 text-center bg-slate-900 border border-slate-800 rounded-xl">
                <i class="fa-solid fa-layer-group text-slate-600 text-3xl mb-3"></i>
                <p class="text-slate-400 text-sm">No benefit programs configured yet.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $programs->links() }}
    </div>
</div>
@endsection
