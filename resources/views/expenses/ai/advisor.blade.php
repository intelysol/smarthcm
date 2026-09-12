@extends('expenses.layout')

@section('title', 'Expense & Travel AI Advisor')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center">
                <i class="fa-solid fa-brain text-indigo-400 mr-2"></i> Expense &amp; Travel AI Advisor
            </h1>
            <p class="text-sm text-slate-400">Ask policy questions, pre-validate claims before submission, estimate per diem rates, and review advance settlement recommendations.</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-950/60 text-indigo-400 border border-indigo-800/50">
                <i class="fa-solid fa-scale-balanced mr-1"></i> Strictly Advisory &bull; No Autonomous Control
            </span>
        </div>
    </div>

    <!-- Governance Notice Banner -->
    <div class="bg-indigo-950/40 border border-indigo-800/40 rounded-xl p-4 flex items-start space-x-3 text-xs text-indigo-300">
        <i class="fa-solid fa-circle-info text-indigo-400 mt-0.5 text-base"></i>
        <div>
            <span class="font-bold text-white">Enterprise Governance Boundary:</span>
            The AI Advisor provides factual policy interpretations, pre-validation checks, and calculations. It is strictly constrained and will <strong>never</strong> autonomously approve expense claims, disburse travel advances, or modify accounting payloads.
        </div>
    </div>

    <!-- Advisory Workbench Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Input Tools Column -->
        <div class="space-y-4">
            <!-- Tool 1: Policy Consultation -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm space-y-3">
                <h2 class="text-sm font-bold text-white flex items-center">
                    <i class="fa-solid fa-book-open text-indigo-400 mr-2"></i> Policy Consultation
                </h2>
                <p class="text-xs text-slate-400">Query daily caps, receipt rules, and compliance guidance by expense category.</p>
                <div class="space-y-2">
                    <select id="policyCategorySelect" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:border-indigo-500 focus:outline-none">
                        <option value="">All Expense Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->code }}">{{ $cat->name }} ({{ $cat->code }})</option>
                        @endforeach
                    </select>
                    <button onclick="queryPolicy()" class="w-full py-2 px-3 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white transition">
                        Explain Policy Rules
                    </button>
                </div>
            </div>

            <!-- Tool 2: Claim Pre-Validation -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm space-y-3">
                <h2 class="text-sm font-bold text-white flex items-center">
                    <i class="fa-solid fa-list-check text-emerald-400 mr-2"></i> Pre-Validate Claim
                </h2>
                <p class="text-xs text-slate-400">Simulate policy compliance, missing receipts, and daily limits prior to formal submission.</p>
                <div class="space-y-2">
                    <select id="claimSelect" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:border-indigo-500 focus:outline-none">
                        @forelse($claims as $cl)
                        <option value="{{ $cl->id }}">{{ $cl->claim_number }} - {{ $cl->title }} (${{ number_format($cl->claimed_total, 2) }})</option>
                        @empty
                        <option value="">No draft claims found</option>
                        @endforelse
                    </select>
                    <button onclick="queryPreValidate()" class="w-full py-2 px-3 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white transition">
                        Check Claim Compliance
                    </button>
                </div>
            </div>

            <!-- Tool 3: Per Diem Estimator -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm space-y-3">
                <h2 class="text-sm font-bold text-white flex items-center">
                    <i class="fa-solid fa-calculator text-sky-400 mr-2"></i> Per Diem Estimator
                </h2>
                <p class="text-xs text-slate-400">Calculate trip daily allowance with departure/return day factors and meal deductions.</p>
                <div class="space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" id="perDiemCountry" placeholder="Country (e.g. UK)" class="bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-white focus:border-indigo-500 focus:outline-none">
                        <input type="number" id="perDiemDays" placeholder="Days" value="3" min="1" max="60" class="bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                    <button onclick="queryPerDiem()" class="w-full py-2 px-3 rounded-lg text-xs font-semibold bg-sky-600 hover:bg-sky-500 text-white transition">
                        Estimate Per Diem
                    </button>
                </div>
            </div>

            <!-- Tool 4: Advance Settlement Advisor -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm space-y-3">
                <h2 class="text-sm font-bold text-white flex items-center">
                    <i class="fa-solid fa-hand-holding-dollar text-amber-400 mr-2"></i> Advance Settlement Advisor
                </h2>
                <p class="text-xs text-slate-400">Calculate net payable or recovery amounts across open employee advances.</p>
                <div class="space-y-2">
                    <select id="advEmployeeSelect" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:border-indigo-500 focus:outline-none">
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_number }})</option>
                        @endforeach
                    </select>
                    <button onclick="queryAdvanceSettlement()" class="w-full py-2 px-3 rounded-lg text-xs font-semibold bg-amber-600 hover:bg-amber-500 text-white transition">
                        Recommend Settlement
                    </button>
                </div>
            </div>
        </div>

        <!-- Output Display Column -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm min-h-[500px] flex flex-col">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400" id="outputTitle">
                        <i class="fa-solid fa-robot mr-1 text-indigo-400"></i> Advisory Analysis Output
                    </span>
                    <span class="text-xs text-slate-500 font-mono" id="timestampBadge">Ready</span>
                </div>

                <div id="advisorOutput" class="flex-1 overflow-y-auto space-y-4">
                    <div class="text-center py-16 text-slate-500">
                        <i class="fa-solid fa-comments text-4xl mb-3 block text-slate-600"></i>
                        <p class="text-sm font-medium text-slate-400">Select an advisory tool on the left to start.</p>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">Simulate policy compliance, compute per diem allocations, or review advance offsets before submitting claims.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function queryAi(payload) {
    const outputContainer = document.getElementById('advisorOutput');
    const timestampBadge = document.getElementById('timestampBadge');
    outputContainer.innerHTML = '<div class="text-center py-16 text-slate-400"><i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-indigo-400"></i><p class="text-xs">Analyzing expense policy and records...</p></div>';

    try {
        const response = await fetch("{{ route('expenses.ai.query') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        timestampBadge.textContent = new Date().toLocaleTimeString();
        renderResult(data);
    } catch (e) {
        outputContainer.innerHTML = '<div class="p-4 bg-rose-950/40 border border-rose-800/50 rounded-lg text-rose-300 text-xs">Failed to connect to advisory service.</div>';
    }
}

function queryPolicy() {
    const cat = document.getElementById('policyCategorySelect').value;
    document.getElementById('outputTitle').innerHTML = '<i class="fa-solid fa-book-open text-indigo-400 mr-1"></i> Policy Explanation';
    queryAi({ type: 'explain_policy', category_code: cat });
}

function queryPreValidate() {
    const claimId = document.getElementById('claimSelect').value;
    if (!claimId) return;
    document.getElementById('outputTitle').innerHTML = '<i class="fa-solid fa-list-check text-emerald-400 mr-1"></i> Claim Pre-Validation Results';
    queryAi({ type: 'pre_validate_claim', claim_id: claimId });
}

function queryPerDiem() {
    const country = document.getElementById('perDiemCountry').value;
    const days = document.getElementById('perDiemDays').value;
    document.getElementById('outputTitle').innerHTML = '<i class="fa-solid fa-calculator text-sky-400 mr-1"></i> Per Diem Estimation';
    queryAi({ type: 'estimate_per_diem', country: country, days: days, destination_type: country ? 'international' : 'domestic' });
}

function queryAdvanceSettlement() {
    const empId = document.getElementById('advEmployeeSelect').value;
    document.getElementById('outputTitle').innerHTML = '<i class="fa-solid fa-hand-holding-dollar text-amber-400 mr-1"></i> Advance Settlement Recommendation';
    queryAi({ type: 'recommend_advance_settlement', employee_id: empId });
}

function renderResult(data) {
    const container = document.getElementById('advisorOutput');
    container.innerHTML = '';

    // Disclaimer box
    const disclaimer = document.createElement('div');
    disclaimer.className = 'p-3 bg-indigo-950/30 border border-indigo-800/40 rounded-lg text-xs text-indigo-300 flex items-center space-x-2';
    disclaimer.innerHTML = '<i class="fa-solid fa-shield-halved text-indigo-400"></i><span>' + (data.disclaimer || 'Advisory guidance only.') + '</span>';
    container.appendChild(disclaimer);

    // Pre-validation display
    if (data.overall_status !== undefined) {
        const statusBox = document.createElement('div');
        const color = data.overall_status === 'compliant' ? 'emerald' : (data.overall_status === 'blocked' ? 'rose' : 'amber');
        statusBox.className = 'p-4 bg-' + color + '-950/30 border border-' + color + '-800/40 rounded-lg space-y-2';
        statusBox.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase text-${color}-400">Status: ${data.overall_status.replace('_', ' ')}</span>
                <span class="text-xs font-mono font-bold text-white">Compliance Score: ${data.compliance_score}/100</span>
            </div>
        `;
        container.appendChild(statusBox);

        if (data.warnings && data.warnings.length) {
            const wBox = document.createElement('div');
            wBox.className = 'space-y-1';
            wBox.innerHTML = '<span class="text-xs font-bold text-amber-400"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Policy Warnings:</span>' +
                '<ul class="list-disc pl-5 text-xs text-slate-300 space-y-1 mt-1">' +
                data.warnings.map(w => '<li>' + w + '</li>').join('') + '</ul>';
            container.appendChild(wBox);
        }

        if (data.recommendations && data.recommendations.length) {
            const rBox = document.createElement('div');
            rBox.className = 'space-y-1';
            rBox.innerHTML = '<span class="text-xs font-bold text-indigo-400"><i class="fa-solid fa-lightbulb mr-1"></i> Suggested Actions:</span>' +
                '<ul class="list-disc pl-5 text-xs text-slate-300 space-y-1 mt-1">' +
                data.recommendations.map(r => '<li>' + r + '</li>').join('') + '</ul>';
            container.appendChild(rBox);
        }
        return;
    }

    // Per Diem display
    if (data.total_estimated_per_diem !== undefined) {
        const pdBox = document.createElement('div');
        pdBox.className = 'p-4 bg-sky-950/30 border border-sky-800/40 rounded-lg';
        pdBox.innerHTML = `
            <div class="text-sm font-bold text-white mb-2">Estimated Per Diem: $${data.total_estimated_per_diem} ${data.currency}</div>
            <div class="text-xs text-slate-400">Duration: ${data.days} days &bull; Base Rate: $${data.base_daily_rate}/day &bull; Destination: ${data.destination_type}</div>
        `;
        container.appendChild(pdBox);
        return;
    }

    // Advance Settlement display
    if (data.total_outstanding_advances !== undefined) {
        const advBox = document.createElement('div');
        advBox.className = 'p-4 bg-amber-950/30 border border-amber-800/40 rounded-lg space-y-2';
        advBox.innerHTML = `
            <div class="text-sm font-bold text-white">Outstanding Advance: $${data.total_outstanding_advances}</div>
            <div class="text-xs text-slate-300">Net Payable to Employee: <strong>$${data.net_payable_to_employee}</strong></div>
            <div class="text-xs text-slate-300">Refund Due from Employee: <strong>$${data.refund_due_from_employee}</strong></div>
        `;
        container.appendChild(advBox);

        if (data.recommended_steps && data.recommended_steps.length) {
            const steps = document.createElement('div');
            steps.className = 'space-y-1';
            steps.innerHTML = '<span class="text-xs font-bold text-slate-300">Recommended Steps:</span>' +
                '<ol class="list-decimal pl-5 text-xs text-slate-300 space-y-1 mt-1">' +
                data.recommended_steps.map(s => '<li>' + s + '</li>').join('') + '</ol>';
            container.appendChild(steps);
        }
        return;
    }

    // JSON fallback
    const code = document.createElement('pre');
    code.className = 'bg-slate-950 p-4 rounded-lg text-xs font-mono text-slate-300 overflow-x-auto';
    code.textContent = JSON.stringify(data, null, 2);
    container.appendChild(code);
}
</script>
@endsection
