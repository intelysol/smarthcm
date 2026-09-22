<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commercial Operations & Billing Center &bull; Enterprise Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --brand-navy: #1E3A5F;
            --brand-dark-navy: #142A44;
            --brand-gold: #C9A227;
            --bg-canvas: #F7F9FC;
        }
    </style>
</head>
<body class="bg-[#F7F9FC] text-[#1F2937] min-h-screen flex flex-col antialiased">
    <!-- Top Nav Header -->
    <header class="bg-[#1E3A5F] text-white border-b border-[#142A44] sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="/portal" class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-lg bg-[#C9A227] text-[#142A44] font-black flex items-center justify-center text-sm shadow">HCM</span>
                    <span class="font-bold tracking-tight text-white">Commercial Command Center</span>
                </a>
                <span class="text-xs bg-emerald-500/20 text-emerald-300 font-semibold px-2.5 py-0.5 rounded-full border border-emerald-500/30">Billing 2.0 Active</span>
            </div>
            <div class="flex items-center space-x-3 text-xs">
                <a href="/operations/system-health" class="text-slate-300 hover:text-white px-3 py-1.5 rounded transition">System Health</a>
                <a href="/portal/billing" class="text-slate-300 hover:text-white px-3 py-1.5 rounded transition">Tenant Portal View</a>
                <a href="/portal" class="bg-[#142A44] hover:bg-black/30 text-[#C9A227] font-semibold px-3 py-1.5 rounded border border-[#C9A227]/40 transition">Exit to Portal</a>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-8">
        <!-- Metric Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- MRR -->
            <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#6B7280]">Monthly Recurring (MRR)</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-3xl font-extrabold text-[#1E3A5F]">${{ number_format($metrics['mrr'], 2) }}</div>
                <div class="text-xs text-[#6B7280] mt-1">ARR: ${{ number_format($metrics['arr'], 2) }} annualized</div>
            </div>

            <!-- Active Subscriptions -->
            <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#6B7280]">Active Subscriptions</span>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ $metrics['active_subscriptions'] }} Live</span>
                </div>
                <div class="text-3xl font-extrabold text-[#1E3A5F]">{{ $metrics['active_subscriptions'] }}</div>
                <div class="text-xs text-[#6B7280] mt-1">{{ $metrics['trial_subscriptions'] }} trials &bull; {{ $metrics['past_due_subscriptions'] }} past due</div>
            </div>

            <!-- Billed MTD -->
            <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#6B7280]">Billed MTD</span>
                    <span class="text-xs font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded">{{ $metrics['collection_rate'] }}% Settled</span>
                </div>
                <div class="text-3xl font-extrabold text-[#1E3A5F]">${{ number_format($metrics['total_billed_mtd'], 2) }}</div>
                <div class="text-xs text-[#6B7280] mt-1">Collected: ${{ number_format($metrics['total_collected_mtd'], 2) }}</div>
            </div>

            <!-- ARPT -->
            <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#6B7280]">Avg Revenue / Tenant</span>
                    <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">ARPT</span>
                </div>
                <div class="text-3xl font-extrabold text-[#1E3A5F]">${{ number_format($metrics['average_revenue_per_tenant'], 2) }}</div>
                <div class="text-xs text-[#6B7280] mt-1">Outstanding: ${{ number_format($metrics['outstanding_balance'], 2) }}</div>
            </div>
        </div>

        <!-- Plans & Pricing Catalog -->
        <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] overflow-hidden">
            <div class="px-6 py-4 border-b border-[#E5E7EB] flex items-center justify-between bg-slate-50/50">
                <div>
                    <h2 class="text-base font-bold text-[#1E3A5F]">Commercial Plans Catalog</h2>
                    <p class="text-xs text-[#6B7280]">Configured subscription tiers, pricing brackets, and entitlements</p>
                </div>
                <span class="text-xs text-indigo-700 font-semibold bg-indigo-50 px-3 py-1 rounded-md border border-indigo-100">3 Standard Tiers</span>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($plans as $plan)
                    <div class="border rounded-xl p-5 flex flex-col justify-between hover:shadow-md transition {{ $plan->code === 'hcm-enterprise' ? 'border-[#C9A227] bg-amber-50/10 ring-1 ring-[#C9A227]/30' : 'border-slate-200' }}">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-lg text-[#1E3A5F]">{{ $plan->name }}</h3>
                                <span class="text-[11px] uppercase font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $plan->billing_interval->value }}</span>
                            </div>
                            <div class="mb-4">
                                <span class="text-3xl font-black text-[#1E3A5F]">${{ number_format((float)$plan->base_price, 0) }}</span>
                                <span class="text-xs text-[#6B7280]">/ month</span>
                            </div>
                            <p class="text-xs text-[#6B7280] mb-4">{{ $plan->description }}</p>
                            
                            <div class="border-t border-slate-100 pt-3 space-y-2">
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Entitlements & Limits</span>
                                @foreach($plan->entitlements as $ent)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-600 font-mono">{{ $ent->entitlement_key }}</span>
                                        @if($ent->limit_value !== null)
                                            <span class="font-bold text-[#1E3A5F]">{{ $ent->limit_value }}</span>
                                        @else
                                            <span class="{{ $ent->is_enabled ? 'text-emerald-600 font-bold' : 'text-slate-400' }}">{{ $ent->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Split Grid: Recent Subscriptions & Recent Invoices -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Subscriptions -->
            <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] overflow-hidden">
                <div class="px-6 py-4 border-b border-[#E5E7EB] flex items-center justify-between">
                    <h2 class="text-sm font-bold text-[#1E3A5F]">Active Tenant Subscriptions</h2>
                    <span class="text-xs text-slate-500">Latest records</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold text-[10px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Tenant</th>
                                <th class="px-4 py-3">Plan</th>
                                <th class="px-4 py-3">Seats</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Renewal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentSubscriptions as $sub)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-semibold text-[#1E3A5F]">{{ $sub->tenant->name ?? 'Unknown' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $sub->plan->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-mono font-bold">{{ $sub->quantity }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                            {{ $sub->status->value === 'active' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                            {{ $sub->status->value === 'trialing' ? 'bg-indigo-50 text-indigo-700' : '' }}
                                            {{ $sub->status->value === 'past_due' ? 'bg-rose-50 text-rose-700' : '' }}
                                        ">{{ strtoupper($sub->status->value) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ $sub->current_cycle_end ? $sub->current_cycle_end->format('M d, Y') : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No subscriptions created yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] overflow-hidden">
                <div class="px-6 py-4 border-b border-[#E5E7EB] flex items-center justify-between">
                    <h2 class="text-sm font-bold text-[#1E3A5F]">Commercial Invoices</h2>
                    <span class="text-xs text-slate-500">Latest activity</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold text-[10px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Invoice #</th>
                                <th class="px-4 py-3">Tenant</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3">Balance</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentInvoices as $inv)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-mono font-bold text-[#1E3A5F]">{{ $inv->invoice_number }}</td>
                                    <td class="px-4 py-3 text-slate-600 truncate max-w-[120px]">{{ $inv->tenant->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-bold">${{ number_format((float)$inv->total_amount, 2) }}</td>
                                    <td class="px-4 py-3 font-bold {{ $inv->balance_due > 0 ? 'text-rose-600' : 'text-emerald-600' }}">${{ number_format((float)$inv->balance_due, 2) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                            {{ $inv->status->value === 'paid' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                            {{ $inv->status->value === 'issued' ? 'bg-amber-50 text-amber-700' : '' }}
                                            {{ $inv->status->value === 'past_due' ? 'bg-rose-50 text-rose-700' : '' }}
                                        ">{{ strtoupper($inv->status->value) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No invoices issued yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
