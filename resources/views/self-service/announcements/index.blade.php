@extends('self-service.layout')

@section('title', 'Company Announcements & Notices')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Company Announcements</h1>
            <p class="text-sm text-slate-400">Official company notices, policy updates, holiday circulars &amp; HR communications.</p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($announcements as $ann)
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-900/60 text-teal-300 border border-teal-700/50 uppercase">
                        {{ $ann->category }}
                    </span>
                    @if($ann->priority === 'urgent' || $ann->priority === 'high')
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-900/60 text-rose-300 uppercase">Important</span>
                    @endif
                </div>
                <span class="text-xs text-slate-500 font-mono">{{ $ann->published_at?->format('F d, Y') }}</span>
            </div>

            <h2 class="text-lg font-bold text-white">{{ $ann->title }}</h2>
            <p class="text-sm text-slate-300 whitespace-pre-line leading-relaxed">{{ $ann->content }}</p>

            @if($ann->requires_acknowledgement)
            <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                <span class="text-xs text-amber-400 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i> Formal Employee Acknowledgement Required</span>
                
                @if($ann->acknowledgements && $ann->acknowledgements->count() > 0)
                <span class="text-xs text-emerald-400 font-medium"><i class="fa-solid fa-check-double mr-1"></i> Acknowledged on {{ $ann->acknowledgements->first()->acknowledged_at?->format('Y-m-d') }}</span>
                @else
                <form action="{{ route('api.self-service.announcements.acknowledge', $ann) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-1.5 bg-teal-600 hover:bg-teal-500 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                        Acknowledge &amp; Accept &rarr;
                    </button>
                </form>
                @endif
            </div>
            @endif
        </div>
        @empty
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-8 text-center text-slate-500">
            No company announcements at this time.
        </div>
        @endforelse
    </div>
</div>
@endsection
