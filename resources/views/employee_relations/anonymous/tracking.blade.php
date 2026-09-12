@extends('layouts.employee_relations')

@section('title', 'Track Anonymous Report')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="text-center space-y-1">
        <h1 class="text-2xl font-bold text-white">Anonymous Report Status</h1>
        <p class="text-xs text-slate-400">Secure Token Access</p>
    </div>

    @if($case)
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-slate-800">
            <div>
                <span class="text-xs text-slate-400">Case Reference</span>
                <div class="font-mono text-lg font-bold text-white">{{ $case->case_number }}</div>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                {{ ucfirst(str_replace('_', ' ', $case->status)) }}
            </span>
        </div>

        <div>
            <div class="text-xs text-slate-400 mb-1">Subject</div>
            <div class="text-sm font-medium text-white">{{ $case->title }}</div>
        </div>

        <div class="p-4 bg-slate-950 rounded-lg border border-slate-800 space-y-2 text-xs text-slate-300">
            <div><strong>Opened:</strong> {{ $case->opened_at->toFormattedDateString() }}</div>
            <div><strong>Status:</strong> Case has been safely received and is being processed by the designated investigation committee.</div>
        </div>
    </div>
    @else
    <div class="bg-slate-900 border border-slate-800 p-8 text-center rounded-xl">
        <p class="text-rose-400 text-sm">Invalid, expired, or revoked anonymous tracking token.</p>
    </div>
    @endif
</div>
@endsection
