@extends('payroll.layout')

@section('title', 'Salary Structures')
@section('page_title', 'Compensation Components & Salary Structures')

@section('content')
<div class="space-y-8">
    <!-- Structures List -->
    <div>
        <h3 class="text-lg font-semibold text-white mb-4">Configured Salary Structures</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($structures as $st)
            <div class="bg-slate-950 border border-slate-800 rounded-xl p-6">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-base font-bold text-white">{{ $st->name }}</h4>
                    <span class="font-mono text-xs text-slate-400">{{ $st->code }} &bull; v{{ $st->version }}</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">{{ $st->description }}</p>

                <div class="border-t border-slate-800 pt-3 space-y-2">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Components:</span>
                    @foreach($st->structureComponents as $sc)
                    <div class="flex items-center justify-between text-xs py-1 border-b border-slate-900">
                        <span class="text-slate-300 font-medium">{{ $sc->component?->name }} ({{ $sc->component?->code }})</span>
                        <span class="font-mono text-slate-400">
                            @if($sc->calculation_type === 'percentage_of_basic')
                                {{ $sc->percentage }}% of Basic
                            @elseif($sc->default_amount)
                                ${{ number_format($sc->default_amount, 2) }}
                            @else
                                {{ ucfirst($sc->calculation_type) }}
                            @endif
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <p class="text-sm text-slate-500">No salary structures defined.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
