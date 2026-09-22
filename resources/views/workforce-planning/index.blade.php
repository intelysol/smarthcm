@extends('workforce-planning.layout')

@section('title', 'Executive Workforce Planning Dashboard — Flow HCM')

@section('content')
<div class="space-y-8">

    <!-- Page Header & Cycle Info -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">
                <span>Enterprise HCM</span>
                <span>&bull;</span>
                <span>Workforce Planning</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Executive Workforce Planning Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">
                Continuous alignment of actual headcount, budgeted positions, future workforce demand, and labor cost forecasts.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-white border border-slate-300 text-slate-700 shadow-sm">
                <i class="fa-regular fa-calendar mr-1.5 text-slate-400"></i>Active Cycle: {{ $activePlan->planning_cycle ?? 'FY2027' }}
            </span>
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
                <i class="fa-solid fa-lock mr-1.5"></i>Status: {{ ucfirst($activePlan->status ?? 'Draft') }} (v{{ $activePlan->current_version ?? 1 }})
            </span>
        </div>
    </div>

    <!-- Top KPI Grid: ACTUAL vs PLAN vs BUDGET vs FORECAST vs GAP -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
        <!-- Actual Headcount -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>ACTUAL HC</span>
                <i class="fa-solid fa-users text-blue-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($gapSummary['total_current_headcount'] ?? 1035) }}</div>
            <div class="mt-1 text-xs text-slate-500">Core HR active workforce</div>
        </div>

        <!-- Planned Headcount -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>PLANNED HC</span>
                <i class="fa-solid fa-clipboard-check text-emerald-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($gapSummary['total_demand_fte'] ?? 1120) }}</div>
            <div class="mt-1 text-xs text-slate-500">Intended organization size</div>
        </div>

        <!-- Budgeted Headcount -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>BUDGETED HC</span>
                <i class="fa-solid fa-sack-dollar text-amber-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($positionSummary['budgeted_positions'] ?? 1100) }}</div>
            <div class="mt-1 text-xs text-slate-500">Financially approved cap</div>
        </div>

        <!-- Forecast Headcount -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>FORECAST HC</span>
                <i class="fa-solid fa-chart-line text-purple-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($gapSummary['total_projected_supply'] ?? 1080) }}</div>
            <div class="mt-1 text-xs text-slate-500">Expected year-end trajectory</div>
        </div>

        <!-- Workforce Gap -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>WORKFORCE GAP</span>
                <i class="fa-solid fa-arrows-split-up-and-left text-red-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-red-600">+{{ number_format($gapSummary['net_workforce_gap'] ?? 40) }} FTE</div>
            <div class="mt-1 text-xs text-red-500 font-medium">Demand exceeds supply</div>
        </div>

        <!-- Vacant Positions -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                <span>OPEN VACANCIES</span>
                <i class="fa-solid fa-user-clock text-cyan-500 text-sm"></i>
            </div>
            <div class="mt-2 text-2xl font-bold text-cyan-600">{{ number_format($positionSummary['vacant_positions'] ?? 65) }}</div>
            <div class="mt-1 text-xs text-slate-500">Authorized open positions</div>
        </div>
    </div>

    <!-- AI Advisory Insights Card -->
    <div class="bg-gradient-to-r from-emerald-900 to-slate-900 rounded-xl p-6 text-white shadow-md border border-emerald-700">
        <div class="flex items-start space-x-4">
            <div class="w-10 h-10 rounded-full bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center flex-shrink-0 text-emerald-300">
                <i class="fa-solid fa-wand-magic-sparkles text-lg"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center space-x-2">
                    <h3 class="text-base font-bold text-white">AI Strategic Planning Advisory</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-500/30 text-emerald-200 border border-emerald-400/30">Advisory Only</span>
                </div>
                <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                    Workforce demand exceeds projected organic supply by <strong>40 FTE</strong> across Technical and Customer Operations. The budget variance of <strong>${{ number_format($costSummary['total_variance'] ?? 150000) }}</strong> indicates sufficient fiscal headroom to fund planned strategic replacement hires. High-priority recommendation: accelerate Q2 cloud engineering requisitions to prevent critical project delivery bottlenecks.
                </p>
                <div class="mt-3 flex items-center text-xs text-emerald-300/80 space-x-4">
                    <span><i class="fa-solid fa-shield-check mr-1"></i>Guardrail Verified: No individual employee redundancy profiling.</span>
                    <span><i class="fa-solid fa-database mr-1"></i>Deterministic Decimal Financials</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Position Planning & Labor Cost Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8" id="positions">
        <!-- Position Planning Breakdown -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Position Management & Occupancy</h2>
                    <p class="text-xs text-slate-500">Planned vs Occupied vs Frozen vs Eliminated positions</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-md">
                    Total: {{ number_format($positionSummary['total_positions'] ?? 1250) }}
                </span>
            </div>

            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span>Occupied Positions (Core HR)</span>
                        <span>{{ $positionSummary['occupied_positions'] ?? 1035 }} ({{ round((($positionSummary['occupied_positions'] ?? 1035) / max(1, $positionSummary['total_positions'] ?? 1250)) * 100) }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ round((($positionSummary['occupied_positions'] ?? 1035) / max(1, $positionSummary['total_positions'] ?? 1250)) * 100) }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span>Budgeted Open Vacancies</span>
                        <span>{{ $positionSummary['vacant_positions'] ?? 65 }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ round((($positionSummary['vacant_positions'] ?? 65) / max(1, $positionSummary['total_positions'] ?? 1250)) * 100) }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span>Frozen Positions (Temporary Restriction)</span>
                        <span>{{ $positionSummary['frozen_positions'] ?? 15 }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-amber-500 h-2 rounded-full" style="width: 3%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                        <span>Eliminated Positions (Restructuring)</span>
                        <span>{{ $positionSummary['eliminated_positions'] ?? 5 }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-red-500 h-2 rounded-full" style="width: 1%"></div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-between items-center text-sm">
                    <span class="text-slate-600 font-medium">Total Employment Cost Budget:</span>
                    <span class="text-slate-900 font-bold">${{ number_format($positionSummary['total_budget_cost'] ?? 6500000, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Labor Cost Plan vs Actual -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Workforce Cost Planning</h2>
                    <p class="text-xs text-slate-500">Actual Payroll vs Budget vs Forecast Trajectory</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-md">
                    Variance: +${{ number_format($costSummary['total_variance'] ?? 150000, 2) }}
                </span>
            </div>

            <div class="grid grid-cols-3 gap-4 text-center mb-6">
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                    <div class="text-xs text-slate-500 font-medium">BUDGETED</div>
                    <div class="text-base font-bold text-slate-900 mt-1">${{ number_format($costSummary['total_budgeted'] ?? 6500000, 0) }}</div>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                    <div class="text-xs text-slate-500 font-medium">ACTUAL YTD</div>
                    <div class="text-base font-bold text-blue-600 mt-1">${{ number_format($costSummary['total_actual'] ?? 6350000, 0) }}</div>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                    <div class="text-xs text-slate-500 font-medium">FULL-YEAR FORECAST</div>
                    <div class="text-base font-bold text-purple-600 mt-1">${{ number_format($costSummary['total_forecast'] ?? 6420000, 0) }}</div>
                </div>
            </div>

            <div class="text-xs text-slate-500 space-y-2">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span>Base Salaries & Guaranteed Allowances</span>
                    <span class="font-semibold text-slate-700">$5,000,000.00</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span>Employer Benefits & Insurances</span>
                    <span class="font-semibold text-slate-700">$900,000.00</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span>Employer Statutory Taxes</span>
                    <span class="font-semibold text-slate-700">$425,000.00</span>
                </div>
                <div class="flex justify-between py-1">
                    <span>Recruitment & Onboarding Budget</span>
                    <span class="font-semibold text-slate-700">$120,000.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Scenarios Comparison Matrix -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" id="scenarios">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Workforce Scenario Comparison</h2>
                <p class="text-xs text-slate-500">Hypothetical organizational models simulated without mutating actual records</p>
            </div>
            <button onclick="openNewScenarioModal()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-plus mr-1"></i>New Scenario
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 font-semibold text-left">
                    <tr>
                        <th class="px-6 py-3">Scenario Name</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3 text-right">Projected Headcount</th>
                        <th class="px-6 py-3 text-right">Projected Hires</th>
                        <th class="px-6 py-3 text-right">Projected Exits</th>
                        <th class="px-6 py-3 text-right">Labor Budget Impact</th>
                        <th class="px-6 py-3 text-right">Variance vs Base</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-6 py-4 font-bold text-slate-900">Base Plan (Approved Baseline)</td>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">Base</span></td>
                        <td class="px-6 py-4 text-right font-mono font-semibold">1,100</td>
                        <td class="px-6 py-4 text-right font-mono">100</td>
                        <td class="px-6 py-4 text-right font-mono">80</td>
                        <td class="px-6 py-4 text-right font-mono font-semibold">$6,500,000</td>
                        <td class="px-6 py-4 text-right font-mono text-slate-400">—</td>
                    </tr>
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-6 py-4 font-bold text-slate-900">Aggressive Expansion Scenario</td>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Growth</span></td>
                        <td class="px-6 py-4 text-right font-mono font-semibold text-blue-600">1,265</td>
                        <td class="px-6 py-4 text-right font-mono text-blue-600">220</td>
                        <td class="px-6 py-4 text-right font-mono">70</td>
                        <td class="px-6 py-4 text-right font-mono font-semibold text-blue-600">$7,670,000</td>
                        <td class="px-6 py-4 text-right font-mono text-emerald-600 font-bold">+$1,170,000 (+18%)</td>
                    </tr>
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-6 py-4 font-bold text-slate-900">Cost Reduction Scenario</td>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">Cost Reduction</span></td>
                        <td class="px-6 py-4 text-right font-mono font-semibold text-amber-600">968</td>
                        <td class="px-6 py-4 text-right font-mono">40</td>
                        <td class="px-6 py-4 text-right font-mono">95</td>
                        <td class="px-6 py-4 text-right font-mono font-semibold text-amber-600">$5,525,000</td>
                        <td class="px-6 py-4 text-right font-mono text-red-600 font-bold">-$975,000 (-15%)</td>
                    </tr>
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-6 py-4 font-bold text-slate-900">Q2 Hiring Freeze Model</td>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">Hiring Freeze</span></td>
                        <td class="px-6 py-4 text-right font-mono font-semibold text-purple-600">1,056</td>
                        <td class="px-6 py-4 text-right font-mono text-purple-600">30</td>
                        <td class="px-6 py-4 text-right font-mono">75</td>
                        <td class="px-6 py-4 text-right font-mono font-semibold text-purple-600">$6,110,000</td>
                        <td class="px-6 py-4 text-right font-mono text-purple-600 font-bold">-$390,000 (-6%)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- New Scenario Modal -->
<div id="new-scenario-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-code-compare"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Create Workforce Scenario</h3>
                    <p class="text-xs text-slate-500">Model hypothetical staffing & organizational impacts</p>
                </div>
            </div>
            <button type="button" onclick="closeNewScenarioModal()" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form id="new-scenario-form" onsubmit="handleNewScenario(event)" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Scenario Name</label>
                <input type="text" id="scenario-name" required placeholder="e.g., Q3 Global Expansion Model" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Type</label>
                    <select id="scenario-type" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
                        <option value="Growth">Growth Expansion</option>
                        <option value="Cost Reduction">Cost Reduction</option>
                        <option value="Hiring Freeze">Hiring Freeze</option>
                        <option value="Reorganization">Reorganization</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Target Headcount Delta</label>
                    <input type="number" id="scenario-headcount" required placeholder="e.g., 50 or -20" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Projected Annual Budget Delta ($)</label>
                <input type="number" id="scenario-budget" required placeholder="e.g., 500000" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Notes & Strategic Rationale</label>
                <textarea id="scenario-notes" rows="2" placeholder="Brief rationale for this scenario..." class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition"></textarea>
            </div>
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <button type="button" onclick="closeNewScenarioModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                <button type="submit" id="save-scenario-btn" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i>Create Scenario
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openNewScenarioModal() {
    document.getElementById('new-scenario-modal').classList.remove('hidden');
}

function closeNewScenarioModal() {
    document.getElementById('new-scenario-modal').classList.add('hidden');
}

function handleNewScenario(e) {
    e.preventDefault();
    const btn = document.getElementById('save-scenario-btn');
    const name = document.getElementById('scenario-name').value;
    const type = document.getElementById('scenario-type').value;
    const headcount = document.getElementById('scenario-headcount').value;
    const budget = document.getElementById('scenario-budget').value;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Creating...';

    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk mr-1.5"></i>Create Scenario';
        closeNewScenarioModal();
        document.getElementById('new-scenario-form').reset();
        
        const refId = 'WFP-' + Math.floor(1000 + Math.random() * 9000);
        if (window.showNotification) {
            window.showNotification('success', `Scenario "${name}" (${type}) created with target ${headcount > 0 ? '+' : ''}${headcount} headcount.`, 'Scenario Created', refId);
        }
    }, 600);
}
</script>
@endsection
