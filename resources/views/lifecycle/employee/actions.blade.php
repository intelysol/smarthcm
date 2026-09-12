@extends('lifecycle.layout')

@section('title', 'My Lifecycle & Personnel Actions — Flow HCM')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl p-8 text-white shadow-lg">
        <span class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider bg-white/20 text-white backdrop-blur-sm">
            Employee Self-Service
        </span>
        <h1 class="text-3xl font-extrabold mt-3">My Career Milestones & Personnel Actions</h1>
        <p class="text-sm text-indigo-100 mt-1 max-w-xl">
            Review your historical career progression, promotions, role changes, compensation adjustments, and complete electronic acknowledgements.
        </p>
    </div>

    <!-- Actions Timeline -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-bold text-slate-900">Career Progression History</h2>
            <p class="text-xs text-slate-500">Chronological audit of approved internal mobility and grade revisions</p>
        </div>

        <div class="space-y-6">
            @forelse($actions as $action)
                <div class="p-6 rounded-xl border border-slate-200 hover:border-indigo-300 transition space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700">
                                {{ $action->actionType?->name ?? 'Career Change' }}
                            </span>
                            <h3 class="text-base font-bold text-slate-900 mt-2 font-mono">{{ $action->request_number }}</h3>
                            <div class="text-xs text-slate-500 mt-0.5">
                                Effective: <strong>{{ $action->effective_date ? $action->effective_date->format('F d, Y') : 'Immediate' }}</strong>
                            </div>
                        </div>

                        <div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                @if($action->status === 'executed') bg-emerald-100 text-emerald-800
                                @elseif($action->status === 'scheduled') bg-blue-100 text-blue-800
                                @elseif($action->status === 'reversed') bg-rose-100 text-rose-800
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ $action->status }}
                            </span>
                        </div>
                    </div>

                    <!-- Changes Comparison -->
                    @if($action->changes->isNotEmpty())
                        <div class="bg-slate-50 rounded-lg p-4 space-y-2 text-xs">
                            <div class="font-bold text-slate-700 uppercase tracking-wider text-[10px]">Changes Summary</div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($action->changes as $change)
                                    <div class="flex items-center space-x-2">
                                        <span class="text-slate-400 capitalize">{{ str_replace('_', ' ', $change->field_name) }}:</span>
                                        <span class="line-through text-slate-400">{{ $change->old_value_label ?? $change->old_value ?? 'None' }}</span>
                                        <span class="text-indigo-600 font-bold">&rarr; {{ $change->new_value_label ?? $change->new_value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Acknowledgement & Documents -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                        <div>
                            @if($action->acknowledgement && $action->acknowledgement->status === 'acknowledged')
                                <span class="text-emerald-600 font-semibold flex items-center">
                                    <i class="fa-solid fa-circle-check mr-1 text-sm"></i> Acknowledged on {{ $action->acknowledgement->acknowledged_at->format('M d, Y') }}
                                </span>
                            @else
                                <form action="/api/v1/me/personnel-actions/{{ $action->id }}/acknowledge" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-sm transition">
                                        Sign Acknowledgement &rarr;
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="text-slate-400 text-[11px]">
                            Source: {{ ucfirst($action->source) }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-xs text-slate-400">
                    No personnel actions recorded for your employee profile.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
