@extends('benefits.layout')

@section('title', 'Benefit Plans Catalog')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Benefit Plans Catalog</h1>
            <p class="text-sm text-slate-400">Manage health, life, dental, vision, transport, and retirement plan offerings.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-850 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Plan Code &amp; Name</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Coverage Level</th>
                        <th class="px-6 py-4">Employee Cost</th>
                        <th class="px-6 py-4">Employer Cost</th>
                        <th class="px-6 py-4">Annual Limit</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($plans as $plan)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-medium text-white">
                            <div>{{ $plan->name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $plan->code }} (v{{ $plan->version }})</div>
                        </td>
                        <td class="px-6 py-4 text-slate-300">{{ $plan->category?->name ?? ucfirst($plan->benefit_type) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 text-xs rounded bg-slate-800 text-slate-300 border border-slate-700">
                                {{ ucwords(str_replace('_', ' ', $plan->coverage_level)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-200">${{ number_format((float)$plan->employee_cost, 2) }}</td>
                        <td class="px-6 py-4 text-emerald-400 font-medium">${{ number_format((float)$plan->employer_cost, 2) }}</td>
                        <td class="px-6 py-4 text-slate-300">{{ $plan->annual_limit ? ('$' . number_format((float)$plan->annual_limit, 2)) : 'Unlimited' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-300 border border-emerald-700/50">
                                {{ ucfirst($plan->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-500">No benefit plans found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($plans, 'links'))
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/60">
            {{ $plans->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
