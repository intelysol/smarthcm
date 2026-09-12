@extends('layouts.employee_relations')

@section('title', 'Investigation - Case ' . $case->case_number)

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <div class="text-xs text-indigo-400 font-mono">Case {{ $case->case_number }}</div>
            <h1 class="text-2xl font-bold text-white">Investigation Workspace</h1>
        </div>
        <a href="{{ route('er.admin.case', $case) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-xs border border-slate-700">
            &larr; Back to Case
        </a>
    </div>

    @if($investigation)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Scope -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl">
                <h2 class="text-base font-semibold text-white mb-2">Investigation Scope</h2>
                <div class="text-sm text-slate-300 bg-slate-950 p-4 rounded-lg border border-slate-800">{{ $investigation->scope }}</div>
            </div>

            <!-- Steps Plan -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl">
                <h2 class="text-base font-semibold text-white mb-3">Investigation Plan & Steps</h2>
                <div class="space-y-2">
                    @foreach($investigation->steps as $step)
                        <div class="p-3 bg-slate-950 rounded border border-slate-800 flex justify-between items-center text-xs">
                            <div>
                                <span class="font-medium text-white">{{ $step->title }}</span>
                                <span class="text-slate-500 ml-2">({{ ucfirst(str_replace('_', ' ', $step->step_type)) }})</span>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 capitalize">{{ $step->status }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Lead Investigator</h3>
                <div class="text-sm font-medium text-white">{{ $investigation->investigator?->name }}</div>
                <div class="text-xs text-slate-400">Target: {{ $investigation->target_completion_date?->toDateString() }}</div>
            </div>
        </div>
    </div>
    @else
    <div class="bg-slate-900 border border-slate-800 p-12 text-center rounded-xl">
        <p class="text-slate-400 text-sm">No formal investigation initiated for this case yet.</p>
    </div>
    @endif
</div>
@endsection
