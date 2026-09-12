@extends('benefits.layout')

@section('title', 'Benefits Self-Service Enrollment Wizard')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Wizard Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-850 to-indigo-950/40 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-900/60 text-indigo-300 border border-indigo-700/50 uppercase tracking-wider">
                    Self-Service Portal
                </span>
                <h1 class="text-2xl sm:text-3xl font-bold text-white mt-2">Annual Benefits Enrollment</h1>
                <p class="text-sm text-slate-300 mt-1">
                    Select your health, protection, and retirement coverage for the upcoming plan year.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-indigo-600/20 flex items-center gap-2">
                    <i class="fa-solid fa-sparkles text-amber-300"></i> Ask Benefits AI
                </button>
            </div>
        </div>

        <!-- 9-Step Visual Timeline -->
        <div class="mt-8 pt-6 border-t border-slate-800 flex items-center justify-between text-xs overflow-x-auto pb-2 gap-2">
            <div class="flex flex-col items-center min-w-[70px] text-emerald-400 font-medium">
                <span class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-500 flex items-center justify-center mb-1">1</span>
                <span>Eligibility</span>
            </div>
            <div class="h-0.5 flex-1 bg-emerald-500"></div>
            <div class="flex flex-col items-center min-w-[70px] text-indigo-400 font-medium">
                <span class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center mb-1 shadow-md shadow-indigo-500/30">2</span>
                <span>Plan</span>
            </div>
            <div class="h-0.5 flex-1 bg-slate-800"></div>
            <div class="flex flex-col items-center min-w-[70px] text-slate-400">
                <span class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center mb-1 border border-slate-700">3</span>
                <span>Coverage</span>
            </div>
            <div class="h-0.5 flex-1 bg-slate-800"></div>
            <div class="flex flex-col items-center min-w-[70px] text-slate-400">
                <span class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center mb-1 border border-slate-700">4</span>
                <span>Dependents</span>
            </div>
            <div class="h-0.5 flex-1 bg-slate-800"></div>
            <div class="flex flex-col items-center min-w-[70px] text-slate-400">
                <span class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center mb-1 border border-slate-700">5</span>
                <span>Beneficiaries</span>
            </div>
            <div class="h-0.5 flex-1 bg-slate-800"></div>
            <div class="flex flex-col items-center min-w-[70px] text-slate-400">
                <span class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center mb-1 border border-slate-700">6</span>
                <span>Cost Calc</span>
            </div>
            <div class="h-0.5 flex-1 bg-slate-800"></div>
            <div class="flex flex-col items-center min-w-[70px] text-slate-400">
                <span class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center mb-1 border border-slate-700">7</span>
                <span>Documents</span>
            </div>
            <div class="h-0.5 flex-1 bg-slate-800"></div>
            <div class="flex flex-col items-center min-w-[70px] text-slate-400">
                <span class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center mb-1 border border-slate-700">8</span>
                <span>Review</span>
            </div>
            <div class="h-0.5 flex-1 bg-slate-800"></div>
            <div class="flex flex-col items-center min-w-[70px] text-slate-400">
                <span class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center mb-1 border border-slate-700">9</span>
                <span>Confirm</span>
            </div>
        </div>
    </div>

    <!-- Active Election Step Interface -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 sm:p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-white">Select Eligible Benefit Plans</h2>
                <p class="text-xs text-slate-400 mt-1">Review coverage options, monthly costs, and waivable conditions.</p>
            </div>
            <span class="text-xs text-slate-400">
                <i class="fa-solid fa-circle-info text-indigo-400 mr-1"></i> Cost estimates do not represent final payroll tax deductions.
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($plans as $plan)
                <div class="bg-slate-950/70 border border-slate-800 rounded-xl p-5 hover:border-indigo-500/50 transition flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider bg-slate-800 text-slate-300">
                                {{ $plan->benefit_type }}
                            </span>
                            @if($plan->is_mandatory)
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider bg-red-950 text-red-400 border border-red-800/50">
                                    Mandatory
                                </span>
                            @elseif($plan->is_waivable)
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider bg-emerald-950 text-emerald-400 border border-emerald-800/50">
                                    Waivable
                                </span>
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-white group-hover:text-indigo-300 transition">{{ $plan->name }}</h3>
                        <p class="text-xs text-slate-400 mt-1">{{ $plan->description ?? 'Comprehensive coverage for full-time employee and eligible dependents.' }}</p>

                        <div class="mt-4 grid grid-cols-2 gap-3 p-3 bg-slate-900/80 rounded-lg border border-slate-800 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Employee Cost:</span>
                                <span class="text-emerald-400 font-bold font-mono text-sm">${{ number_format($plan->employee_cost, 2) }}</span>
                                <span class="text-slate-400 text-[10px]">/mo</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Employer Pays:</span>
                                <span class="text-indigo-400 font-bold font-mono text-sm">${{ number_format($plan->employer_cost, 2) }}</span>
                                <span class="text-slate-400 text-[10px]">/mo</span>
                            </div>
                        </div>

                        <!-- Coverage Tiers -->
                        @if($plan->coverages->isNotEmpty())
                            <div class="mt-3">
                                <label class="text-[11px] text-slate-400 block mb-1">Coverage Tier:</label>
                                <select class="w-full bg-slate-900 border border-slate-800 rounded px-2.5 py-1.5 text-xs text-slate-200 focus:border-indigo-500 focus:outline-none">
                                    @foreach($plan->coverages as $cov)
                                        <option value="{{ $cov->id }}">{{ $cov->name }} (x{{ $cov->coverage_multiplier }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between gap-2">
                        <button class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-md shadow-indigo-600/20">
                            <i class="fa-solid fa-check"></i> Elect Plan
                        </button>
                        @if(! $plan->is_mandatory && $plan->is_waivable)
                            <button class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-xs font-semibold transition border border-slate-700">
                                Waive
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full p-6 text-center text-slate-400 text-sm">
                    No active benefit plans available for election.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
