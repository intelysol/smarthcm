<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription & Billing &bull; Enterprise Portal</title>
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
                    <span class="font-bold tracking-tight text-white">{{ $tenant->name }} &bull; Commercial Portal</span>
                </a>
            </div>
            <div class="flex items-center space-x-3 text-xs">
                <a href="/portal" class="text-slate-300 hover:text-white px-3 py-1.5 rounded transition">Back to Workplace</a>
                <a href="/operations/billing" class="text-slate-300 hover:text-white px-3 py-1.5 rounded transition">Admin Center</a>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-8">
        <!-- Notification Alerts -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('warning'))
            <div class="p-4 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-sm flex items-center justify-between">
                <span>{{ session('warning') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 text-rose-800 border border-rose-200 text-sm flex items-center justify-between">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Active Plan Banner Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#E5E7EB] p-6 lg:p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <h1 class="text-2xl font-black text-[#1E3A5F]">
                        {{ $subscription ? $subscription->plan->name : 'No Active Plan' }}
                    </h1>
                    @if($subscription)
                        <span class="px-3 py-0.5 rounded-full text-xs font-extrabold uppercase tracking-wide
                            {{ $subscription->status->value === 'active' ? 'bg-emerald-100 text-emerald-800' : '' }}
                            {{ $subscription->status->value === 'trialing' ? 'bg-indigo-100 text-indigo-800' : '' }}
                            {{ $subscription->status->value === 'past_due' ? 'bg-rose-100 text-rose-800' : '' }}
                        ">
                            {{ $subscription->status->value }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-[#6B7280]">
                    @if($subscription && $subscription->isTrialing())
                        Trial concludes on <strong class="text-slate-700">{{ $subscription->trial_ends_at ? $subscription->trial_ends_at->format('M d, Y') : '-' }}</strong>. Upgrade anytime to continue service without interruption.
                    @elseif($subscription && $subscription->current_cycle_end)
                        Current cycle renews on <strong class="text-slate-700">{{ $subscription->current_cycle_end->format('M d, Y') }}</strong> ({{ $subscription->plan->billing_interval->value }}).
                    @else
                        Select a commercial plan below to unlock modules and higher employee seat capacity.
                    @endif
                </p>
            </div>

            <!-- Wallet & Quick Stats -->
            <div class="flex items-center space-x-6 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-6">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Available Credit</span>
                    <span class="text-2xl font-black text-emerald-600">${{ number_format($creditBalance, 2) }}</span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Seats Provisioned</span>
                    <span class="text-2xl font-black text-[#1E3A5F]">{{ $subscription->quantity ?? 1 }}</span>
                </div>
            </div>
        </div>

        <!-- Usage Meters & Resource Gauges -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($meters as $key => $meter)
                <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-600 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                            @if($meter['alert'])
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $meter['alert'] === 'CRITICAL_100_PERCENT' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $meter['percentage'] }}%
                                </span>
                            @endif
                        </div>
                        <div class="text-2xl font-black text-[#1E3A5F]">
                            {{ number_format($meter['current'], 0) }}
                            <span class="text-xs font-normal text-slate-400">/ {{ $meter['limit'] ? number_format($meter['limit'], 0) : 'Unlimited' }}</span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-4 w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-500
                            {{ $meter['percentage'] >= 100 ? 'bg-rose-500' : ($meter['percentage'] >= 80 ? 'bg-amber-500' : 'bg-[#1E3A5F]') }}
                        " style="width: {{ min(100, $meter['percentage']) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Plan Selection & Upgrade Tiers -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#E5E7EB] p-6 lg:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-black text-[#1E3A5F]">Commercial Tiers & Upgrades</h2>
                <p class="text-xs text-[#6B7280]">Select a plan that fits your organization. Upgrades take effect immediately with prorated billing adjustments.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($availablePlans as $plan)
                    @php
                        $isCurrent = $subscription && $subscription->plan_id === $plan->id;
                    @endphp
                    <div class="border rounded-xl p-6 flex flex-col justify-between transition {{ $isCurrent ? 'border-emerald-500 bg-emerald-50/20 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:border-indigo-300' }}">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-lg text-[#1E3A5F]">{{ $plan->name }}</h3>
                                @if($isCurrent)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white">Current Plan</span>
                                @endif
                            </div>
                            <div class="mb-4">
                                <span class="text-3xl font-black text-[#1E3A5F]">${{ number_format((float)$plan->base_price, 0) }}</span>
                                <span class="text-xs text-[#6B7280]">/ month</span>
                            </div>
                            <p class="text-xs text-[#6B7280] mb-5 leading-relaxed">{{ $plan->description }}</p>

                            <ul class="space-y-2.5 text-xs text-slate-700 border-t border-slate-100 pt-4 mb-6">
                                @foreach($plan->entitlements as $ent)
                                    <li class="flex items-center space-x-2">
                                        @if($ent->is_enabled)
                                            <span class="text-emerald-600 font-bold">&check;</span>
                                        @else
                                            <span class="text-slate-300">&cross;</span>
                                        @endif
                                        <span class="{{ $ent->is_enabled ? 'text-slate-800' : 'text-slate-400 line-through' }}">
                                            {{ ucwords(str_replace(['_enabled', '_'], ['', ' '], $ent->entitlement_key)) }}
                                            @if($ent->limit_value)
                                                (Max {{ $ent->limit_value }})
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div>
                            @if($isCurrent)
                                <button disabled class="w-full py-2.5 px-4 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs cursor-not-allowed">
                                    Currently Active
                                </button>
                            @else
                                <form method="POST" action="/portal/billing/plan">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-[#1E3A5F] hover:bg-[#142A44] text-white font-bold text-xs shadow transition">
                                        Switch to {{ $plan->name }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Invoices & Billing History -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#E5E7EB] overflow-hidden">
            <div class="px-6 py-5 border-b border-[#E5E7EB] flex items-center justify-between bg-slate-50/50">
                <div>
                    <h2 class="text-base font-bold text-[#1E3A5F]">Invoices & Payment History</h2>
                    <p class="text-xs text-[#6B7280]">Historical tax invoices, settlements, and PDF download receipts</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold text-[10px] tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Invoice #</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Due Date</th>
                            <th class="px-6 py-3">Total Amount</th>
                            <th class="px-6 py-3">Balance Due</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($invoices as $inv)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-6 py-4 font-mono font-bold text-[#1E3A5F]">{{ $inv->invoice_number }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $inv->issue_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $inv->due_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 font-bold">${{ number_format((float)$inv->total_amount, 2) }}</td>
                                <td class="px-6 py-4 font-bold {{ $inv->balance_due > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    ${{ number_format((float)$inv->balance_due, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                        {{ $inv->status->value === 'paid' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                        {{ $inv->status->value === 'issued' ? 'bg-amber-50 text-amber-700' : '' }}
                                        {{ $inv->status->value === 'past_due' ? 'bg-rose-50 text-rose-700' : '' }}
                                    ">
                                        {{ strtoupper($inv->status->value) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="/portal/billing/invoices/{{ $inv->id }}" target="_blank" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded font-semibold text-xs transition">
                                        Receipt
                                    </a>
                                    @if($inv->balance_due > 0)
                                        <form method="POST" action="/portal/billing/invoices/{{ $inv->id }}/pay" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded font-semibold text-xs shadow-sm transition">
                                                Pay Now
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400">No invoices issued for this tenant yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
