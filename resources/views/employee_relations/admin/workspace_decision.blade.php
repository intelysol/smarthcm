@extends('layouts.employee_relations')

@section('title', 'Decisions - Case ' . $case->case_number)

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <div class="text-xs text-indigo-400 font-mono">Case {{ $case->case_number }}</div>
            <h1 class="text-2xl font-bold text-white">Decisions & Corrective Actions</h1>
        </div>
        <a href="{{ route('er.admin.case', $case) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-xs border border-slate-700">
            &larr; Back to Case
        </a>
    </div>

    <!-- Decisions List -->
    <div class="space-y-6">
        @forelse($decisions as $decision)
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl space-y-4">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-xs text-slate-400">Decision Outcome</span>
                        <h2 class="text-lg font-bold text-white uppercase tracking-wide">{{ str_replace('_', ' ', $decision->decision) }}</h2>
                    </div>
                    <div class="text-right text-xs text-slate-400">
                        <div>Decided by: <span class="text-slate-200">{{ $decision->decisionMaker?->name }}</span></div>
                        <div>Date: <span class="text-slate-200">{{ $decision->decided_at->toFormattedDateString() }}</span></div>
                    </div>
                </div>

                <div class="bg-slate-950 p-4 rounded-lg border border-slate-800">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Reason & Grounds</div>
                    <p class="text-sm text-slate-300 leading-relaxed">{{ $decision->reason }}</p>
                </div>

                <!-- Corrective Actions for this decision -->
                @if($decision->correctiveActions->isNotEmpty())
                    <div class="space-y-2 pt-2">
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Corrective Action Plan</div>
                        @foreach($decision->correctiveActions as $action)
                            <div class="p-3 bg-slate-950 rounded border border-slate-800 flex justify-between items-center text-xs">
                                <div>
                                    <span class="font-medium text-white">{{ str_replace('_', ' ', $action->action_type) }}:</span>
                                    <span class="text-slate-300 ml-1">{{ $action->description }}</span>
                                    <span class="text-slate-500 ml-2">(Due: {{ $action->due_date->toDateString() }})</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-0.5 rounded bg-slate-800 text-indigo-400 capitalize">{{ $action->status }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-slate-900 border border-slate-800 p-12 text-center rounded-xl">
                <p class="text-slate-400 text-sm">No formal decision has been recorded for this case yet.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
