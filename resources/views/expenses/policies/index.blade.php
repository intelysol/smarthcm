@extends('expenses.layout')

@section('title', 'Expense Policies & Categories')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Expense Policies &amp; Categories</h1>
            <p class="text-sm text-slate-400">Configure corporate expense policies, version limits, daily caps, receipt thresholds, and expense categories.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-900 border border-slate-700 text-slate-300">
                <i class="fa-solid fa-shield-halved mr-1 text-indigo-400"></i> Active Governance
            </span>
        </div>
    </div>

    <!-- Active Policies Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($policies as $policy)
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-bold text-white">{{ $policy->name }}</h2>
                    <p class="text-xs text-slate-400 font-mono">{{ $policy->policy_code ?? 'POL-GLOBAL' }}</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-950/60 text-emerald-400 border border-emerald-800/50">
                    {{ ucfirst($policy->status) }}
                </span>
            </div>

            <p class="text-sm text-slate-300">{{ $policy->description ?? 'Standard corporate travel and expense reimbursement policy.' }}</p>

            @php $currentVer = $policy->versions->first(); @endphp
            @if($currentVer)
            <div class="grid grid-cols-3 gap-3 p-3 bg-slate-950/60 rounded-lg border border-slate-800 text-xs">
                <div>
                    <span class="text-slate-500 block">Daily Meal Cap</span>
                    <span class="font-bold text-white">${{ number_format($currentVer->daily_meal_limit ?? 0, 2) }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Daily Hotel Cap</span>
                    <span class="font-bold text-white">${{ number_format($currentVer->daily_hotel_limit ?? 0, 2) }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Receipt Required</span>
                    <span class="font-bold text-indigo-400">&gt; ${{ number_format($currentVer->receipt_required_threshold ?? 0, 2) }}</span>
                </div>
            </div>
            @endif

            <div class="flex items-center justify-between text-xs text-slate-400 border-t border-slate-800/80 pt-3">
                <span>Version {{ $policy->current_version }} (Effective {{ $policy->effective_from ?? 'Permanent' }})</span>
                <span class="text-slate-400">{{ $policy->assignments->count() }} Scope Assignments</span>
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-slate-900 border border-slate-800 rounded-xl p-8 text-center text-slate-500">
            No policies found.
        </div>
        @endforelse
    </div>

    <!-- Expense Categories Table -->
    <div class="space-y-3">
        <h2 class="text-lg font-bold text-white">Expense Categories Catalog</h2>
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-850 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-6 py-4">Code &amp; Name</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Max Cap</th>
                            <th class="px-6 py-4">Receipt Rule</th>
                            <th class="px-6 py-4">Tax Treatment</th>
                            <th class="px-6 py-4">Reimbursable</th>
                            <th class="px-6 py-4">GL Account</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($categories as $category)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-6 py-4 font-medium text-white">
                                <div>{{ $category->name }}</div>
                                <div class="text-xs text-indigo-400 font-mono">{{ $category->code }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-slate-300">
                                <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700">
                                    {{ $category->category_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-200">
                                {{ $category->max_amount ? '$' . number_format($category->max_amount, 2) : 'No limit' }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @if($category->receipt_required)
                                    <span class="text-amber-400">&gt; ${{ number_format($category->receipt_threshold, 2) }}</span>
                                @else
                                    <span class="text-slate-500">Not required</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400">{{ ucfirst($category->tax_treatment) }}</td>
                            <td class="px-6 py-4">
                                @if($category->is_reimbursable)
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-950/60 text-emerald-400 border border-emerald-800/50">Yes</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-rose-950/60 text-rose-400 border border-rose-800/50">Non-reimbursable</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-400">{{ $category->accounting_code ?? 'GL-6100-EXP' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">No expense categories found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
