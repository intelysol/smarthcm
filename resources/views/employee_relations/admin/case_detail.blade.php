@extends('layouts.employee_relations')

@section('title', 'Case ' . $case->case_number)

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl flex justify-between items-start">
        <div class="space-y-1">
            <div class="flex items-center space-x-3">
                <span class="font-mono text-xs px-2.5 py-1 bg-slate-800 border border-slate-700 rounded text-slate-300">{{ $case->case_number }}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    {{ ucfirst(str_replace('_', ' ', $case->status)) }}
                </span>
                <span class="text-xs px-2.5 py-0.5 rounded bg-slate-800 text-slate-400">
                    Confidentiality: {{ ucfirst(str_replace('_', ' ', $case->confidentiality_level)) }}
                </span>
                @if($case->hasActiveLegalHold())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                        <i class="fa-solid fa-lock mr-1"></i> Legal Hold Active
                    </span>
                @endif
            </div>
            <h1 class="text-2xl font-bold text-white pt-2">{{ $case->title }}</h1>
            <p class="text-xs text-slate-400">
                Classification: <span class="text-slate-200">{{ $case->caseType?->name }}</span> |
                Subject: <span class="text-slate-200">{{ $case->subjectEmployee ? ($case->subjectEmployee->first_name . ' ' . $case->subjectEmployee->last_name) : $case->subject_name }}</span> |
                Opened: <span class="text-slate-200">{{ $case->opened_at->toFormattedDateString() }}</span>
            </p>
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('er.admin.investigation', $case) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium rounded border border-slate-700 transition">
                <i class="fa-solid fa-magnifying-glass mr-1"></i> Investigation
            </a>
            <a href="{{ route('er.admin.evidence', $case) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium rounded border border-slate-700 transition">
                <i class="fa-solid fa-box-archive mr-1"></i> Evidence
            </a>
            <a href="{{ route('er.admin.hearing', $case) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium rounded border border-slate-700 transition">
                <i class="fa-solid fa-gavel mr-1"></i> Hearing
            </a>
            <a href="{{ route('er.admin.decision', $case) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium rounded transition shadow">
                <i class="fa-solid fa-scale-balanced mr-1"></i> Decision
            </a>
        </div>
    </div>

    <!-- Main Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Details, Allegations, Timeline -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Summary -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl">
                <h2 class="text-base font-semibold text-white mb-3">Case Summary & Facts</h2>
                <div class="text-sm text-slate-300 leading-relaxed whitespace-pre-line bg-slate-950 p-4 rounded-lg border border-slate-800/80">{{ $case->summary }}</div>
            </div>

            <!-- Allegations -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl">
                <h2 class="text-base font-semibold text-white mb-3">Allegations ({{ $case->allegations->count() }})</h2>
                <div class="space-y-3">
                    @forelse($case->allegations as $alg)
                        <div class="p-4 bg-slate-950 border border-slate-800 rounded-lg">
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-mono text-xs text-indigo-400 font-medium">{{ $alg->allegation_number }}</span>
                                <span class="text-xs px-2 py-0.5 rounded bg-slate-800 text-slate-400">{{ ucfirst(str_replace('_', ' ', $alg->status)) }}</span>
                            </div>
                            <div class="font-medium text-white text-sm">{{ $alg->title }}</div>
                            <div class="text-xs text-slate-400 mt-1">{{ $alg->description }}</div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 italic">No formal allegations logged yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Unified Timeline -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl">
                <h2 class="text-base font-semibold text-white mb-4">Case Timeline</h2>
                <div class="space-y-4">
                    @foreach($timeline as $entry)
                        <div class="flex space-x-3 text-xs">
                            <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5"></div>
                            <div class="flex-grow">
                                <div class="font-semibold text-slate-200">{{ $entry['title'] }}</div>
                                <div class="text-slate-400">{{ $entry['description'] }}</div>
                                <div class="text-slate-500 text-[10px] mt-0.5">{{ $entry['timestamp'] }} by {{ $entry['actor'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Col: Assignments, SLAs, Team -->
        <div class="space-y-6">
            <!-- SLAs -->
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Case SLAs</h3>
                <div class="space-y-2">
                    @foreach($case->slas as $sla)
                        <div class="flex justify-between items-center text-xs p-2 bg-slate-950 rounded border border-slate-800">
                            <span class="text-slate-300">{{ ucfirst(str_replace('_', ' ', $sla->sla_type)) }}</span>
                            <span class="font-mono font-medium {{ $sla->status === 'breached' ? 'text-rose-400' : 'text-emerald-400' }}">
                                {{ ucfirst($sla->status) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Case Team -->
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Assigned Team</h3>
                <div class="space-y-2">
                    @foreach($case->assignments as $asgn)
                        <div class="text-xs p-2 bg-slate-950 rounded border border-slate-800">
                            <div class="font-medium text-white">{{ $asgn->user?->name }}</div>
                            <div class="text-slate-400 capitalize">{{ str_replace('_', ' ', $asgn->role) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
