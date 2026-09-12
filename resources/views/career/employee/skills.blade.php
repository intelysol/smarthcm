@extends('layouts.career')

@section('title', 'My Skills Inventory')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-800/80 p-6 rounded-2xl border border-slate-700">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-award text-emerald-400"></i> My Skills & Competencies
            </h1>
            <p class="text-sm text-slate-400 mt-1">Manage your technical, functional, and domain capabilities with verifiable evidence.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-emerald-500/10 text-emerald-400 text-xs px-3 py-1.5 rounded-lg border border-emerald-500/20 font-medium">
                {{ $skills->count() }} Skills Registered
            </span>
        </div>
    </div>

    <!-- Skills Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($skills as $item)
        <div class="bg-slate-800/60 rounded-xl p-5 border border-slate-700/80 hover:border-emerald-500/40 transition flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400">{{ $item->skill->category->name ?? 'Skill' }}</span>
                        <h3 class="text-lg font-bold text-white mt-0.5">{{ $item->skill->name }}</h3>
                        <p class="text-xs text-slate-400">{{ $item->skill->code }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $item->verification_status === 'manager_verified' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                        {{ ucwords(str_replace('_', ' ', $item->verification_status)) }}
                    </span>
                </div>

                <!-- Level Progress -->
                <div class="mt-4">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-slate-400">Proficiency Level</span>
                        <span class="text-white font-bold">Level {{ $item->current_level }} / 5</span>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-2">
                        <div class="bg-emerald-400 h-2 rounded-full" style="width: {{ ($item->current_level / 5) * 100 }}%"></div>
                    </div>
                </div>

                @if($item->evidence->isNotEmpty())
                <div class="mt-4 border-t border-slate-700/60 pt-3">
                    <span class="text-xs text-slate-400 block mb-1.5 font-medium">Evidence ({{ $item->evidence->count() }}):</span>
                    <ul class="text-xs space-y-1 text-slate-300">
                        @foreach($item->evidence->take(2) as $ev)
                        <li class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-paperclip text-slate-500"></i> {{ $ev->title }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <div class="mt-5 pt-3 border-t border-slate-700 flex justify-between items-center text-xs text-slate-400">
                <span>Assessed: {{ $item->last_assessed_at?->format('M Y') ?? 'Recent' }}</span>
                <span class="text-emerald-400 hover:underline cursor-pointer font-medium">Update Level &rarr;</span>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-slate-800/40 rounded-xl p-12 text-center border border-slate-700">
            <i class="fa-solid fa-brain text-slate-600 text-4xl mb-3"></i>
            <p class="text-slate-300 font-medium">No skills declared yet.</p>
            <p class="text-xs text-slate-400 mt-1">Start by adding your top skills from the catalog.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
