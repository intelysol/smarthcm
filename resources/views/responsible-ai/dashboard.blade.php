<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsible AI Governance & AI Operations Cockpit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans antialiased">
    <!-- Header -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-rose-600 flex items-center justify-center shadow-lg shadow-rose-500/20">
                    <i class="fa-solid fa-scale-balanced text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight tracking-wide text-white">Responsible AI Governance Center</h1>
                    <p class="text-xs text-slate-400">Model Risk Management, Fairness Surveillance & AI Control Plane</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-950 text-emerald-400 border border-emerald-800">
                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-emerald-400"></span>
                    Guardrails Enforced
                </span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Compliance & Trust Score</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-emerald-400">{{ $dashboard['overall_compliance_score'] }}%</span>
                    <span class="text-xs text-slate-400">Audit Ready</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">5/5 Core Control Baselines Passing</p>
            </div>

            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Governed AI Use Cases</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white">{{ $dashboard['total_use_cases'] }}</span>
                    <span class="text-xs text-slate-400">{{ $dashboard['high_risk_use_cases'] }} High/Critical</span>
                </div>
                <p class="mt-2 text-xs text-rose-400">{{ $dashboard['prohibited_use_cases'] }} Prohibited strictly blocked</p>
            </div>

            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Approved Models</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white">{{ $dashboard['total_approved_models'] }}</span>
                    <span class="text-xs text-teal-400 font-medium">Certified</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Data residency & cost profile verified</p>
            </div>

            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Active Kill Switches</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold {{ $dashboard['active_kill_switches'] > 0 ? 'text-amber-400' : 'text-slate-100' }}">{{ $dashboard['active_kill_switches'] }}</span>
                    <span class="text-xs text-slate-400">Emergency controls</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">{{ $dashboard['open_ai_incidents'] }} Open Safety Incidents</p>
            </div>
        </div>

        <!-- Controls Table & Status -->
        <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-slate-200 flex items-center">
                    <i class="fa-solid fa-shield-halved mr-2 text-rose-400"></i> Core Responsible AI Control Status
                </h3>
                <span class="text-xs text-slate-400">Automated Continuous Testing</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($dashboard['controls_evaluated'] as $control => $status)
                <div class="p-4 rounded-lg bg-slate-950/70 border border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-200">{{ str_replace('_', ' ', $control) }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Policy baseline verified</div>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        {{ $status }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </main>
</body>
</html>
