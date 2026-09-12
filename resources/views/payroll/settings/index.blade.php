@extends('payroll.layout')

@section('title', 'Payroll Settings & Policies')
@section('page_title', 'Payroll Policies & Tax Rules')

@section('content')
<div class="space-y-8">
    <!-- Statutory Tax Rules -->
    <div class="bg-slate-950 border border-slate-800 rounded-xl p-6">
        <h3 class="text-base font-bold text-white mb-4">Statutory Tax Rules</h3>
        <div class="space-y-4">
            @foreach($taxRules as $tr)
            <div class="border border-slate-800 rounded-lg p-4 bg-slate-900/40">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-white">{{ $tr->name }}</h4>
                        <p class="text-xs text-slate-400 font-mono">{{ $tr->code }} &bull; Mode: {{ $tr->calculation_mode }}</p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Payroll Policies -->
    <div class="bg-slate-950 border border-slate-800 rounded-xl p-6">
        <h3 class="text-base font-bold text-white mb-4">Proration & Rounding Policies</h3>
        <div class="space-y-4">
            @foreach($policies as $pol)
            <div class="border border-slate-800 rounded-lg p-4 bg-slate-900/40">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-semibold text-white">{{ $pol->name }}</h4>
                    <span class="font-mono text-xs text-slate-400">{{ $pol->code }}</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs text-slate-400 pt-2 border-t border-slate-800">
                    <div>Proration: <span class="text-white font-medium">{{ $pol->proration_method }}</span></div>
                    <div>Rounding: <span class="text-white font-medium">{{ $pol->rounding_method }}</span></div>
                    <div>Overtime Multiplier: <span class="text-white font-medium">{{ $pol->overtime_rate_multiplier }}x</span></div>
                    <div>Variance Threshold: <span class="text-white font-medium">{{ $pol->variance_threshold_percentage }}%</span></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
