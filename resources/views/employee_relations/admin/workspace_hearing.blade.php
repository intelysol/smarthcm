@extends('layouts.employee_relations')

@section('title', 'Hearings - Case ' . $case->case_number)

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <div class="text-xs text-indigo-400 font-mono">Case {{ $case->case_number }}</div>
            <h1 class="text-2xl font-bold text-white">Hearings Workspace</h1>
        </div>
        <a href="{{ route('er.admin.case', $case) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-xs border border-slate-700">
            &larr; Back to Case
        </a>
    </div>

    <div class="space-y-4">
        @forelse($hearings as $hearing)
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-base font-semibold text-white">{{ $hearing->title }}</h2>
                    <span class="px-2.5 py-0.5 rounded text-xs font-medium bg-slate-800 text-indigo-400 capitalize">{{ $hearing->status }}</span>
                </div>
                <div class="text-xs text-slate-400 mb-4">
                    Scheduled: <span class="text-slate-200">{{ $hearing->scheduled_at->toFormattedDateString() }}</span> |
                    Chairperson: <span class="text-slate-200">{{ $hearing->chairperson?->name }}</span> |
                    Location: <span class="text-slate-200">{{ $hearing->location }}</span>
                </div>
                @if($hearing->outcome)
                    <div class="p-4 bg-slate-950 border border-slate-800 rounded-lg">
                        <div class="text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Hearing Outcome</div>
                        <div class="text-sm text-slate-300">{{ $hearing->outcome->summary }}</div>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-slate-900 border border-slate-800 p-12 text-center rounded-xl">
                <p class="text-slate-400 text-sm">No formal hearings scheduled.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
