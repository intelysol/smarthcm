@extends('shells.platform')

@section('title', 'Platform Products & Subscriptions')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Products &amp; Commercial Subscriptions</h1>
            <p class="text-xs text-slate-400">Global SaaS commercial packages, pricing tiers, and customer subscriptions</p>
        </div>
        <a href="{{ route('platform.control-center') }}" class="text-xs font-semibold text-[#C9A227] hover:underline">&larr; Back to Control Center</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs font-medium text-slate-400">Active Plans</span>
            <div class="mt-2 text-2xl font-black text-white">Enterprise Tier</div>
            <p class="text-xs text-emerald-400 mt-1">Unlimited HCM Modules</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs font-medium text-slate-400">Billing Cycle</span>
            <div class="mt-2 text-2xl font-black text-white">Monthly / Annual</div>
            <p class="text-xs text-slate-400 mt-1">Automated invoice generation</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
            <span class="text-xs font-medium text-slate-400">Payment Gateway</span>
            <div class="mt-2 text-2xl font-black text-emerald-400">Configured</div>
            <p class="text-xs text-slate-400 mt-1">Multi-currency enabled</p>
        </div>
    </div>
</div>
@endsection
