@extends('layouts.employee_relations')

@section('title', 'My Case ' . $case->case_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <div class="text-xs text-indigo-400 font-mono">Case {{ $case->case_number }}</div>
            <h1 class="text-2xl font-bold text-white">{{ $case->title }}</h1>
        </div>
        <a href="{{ route('er.employee.dashboard') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-xs border border-slate-700">
            &larr; Back to My Cases
        </a>
    </div>

    <!-- Case Status Card -->
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl space-y-3">
        <div class="flex justify-between items-center">
            <span class="text-xs text-slate-400">Current Status</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                {{ ucfirst(str_replace('_', ' ', $case->status)) }}
            </span>
        </div>
        <p class="text-sm text-slate-300 bg-slate-950 p-4 rounded-lg border border-slate-800">{{ $case->summary }}</p>
    </div>

    <!-- Required Actions & Acknowledgements -->
    @if($employeeActions->isNotEmpty())
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl space-y-3">
        <h2 class="text-base font-semibold text-white">Required Corrective Actions</h2>
        <div class="space-y-2">
            @foreach($employeeActions as $act)
                <div class="p-4 bg-slate-950 rounded-lg border border-slate-800 flex justify-between items-center text-xs">
                    <div>
                        <div class="font-medium text-white">{{ str_replace('_', ' ', $act->action_type) }}</div>
                        <div class="text-slate-400 mt-0.5">{{ $act->description }}</div>
                        <div class="text-slate-500 mt-1">Due: {{ $act->due_date->toDateString() }} | Status: {{ ucfirst($act->status) }}</div>
                    </div>
                    <div>
                        @if($act->employee_acknowledgement_status === 'pending')
                            <span class="px-2 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded">Acknowledgement Required</span>
                        @else
                            <span class="px-2 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded capitalize">{{ $act->employee_acknowledgement_status }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
