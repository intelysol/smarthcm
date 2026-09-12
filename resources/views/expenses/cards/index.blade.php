@extends('expenses.layout')

@section('title', 'Corporate Cards & Feeds')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Corporate Credit Cards &amp; Automated Feeds</h1>
            <p class="text-sm text-slate-400">Match credit card transactions directly to employee expense lines, detect personal charges, and reconcile card statements.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-950/60 text-indigo-400 border border-indigo-800/50">
                <i class="fa-solid fa-credit-card mr-1"></i> Card Reconciliation
            </span>
        </div>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Assigned Corporate Cards</span>
            <div class="text-2xl font-bold text-white mt-2">{{ $cards->count() }}</div>
            <span class="text-xs text-slate-500">Active employee card accounts</span>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Matched Transactions</span>
            <div class="text-2xl font-bold text-emerald-400 mt-2">{{ $matchedCount }}</div>
            <span class="text-xs text-emerald-500"><i class="fa-solid fa-check mr-1"></i> Reconciled to expense claim lines</span>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Unmatched Feed Items</span>
            <div class="text-2xl font-bold text-amber-400 mt-2">{{ $unmatchedCount }}</div>
            <span class="text-xs text-amber-500"><i class="fa-solid fa-clock mr-1"></i> Awaiting claim association</span>
        </div>
    </div>

    <!-- Cards List -->
    <div class="space-y-3">
        <h2 class="text-lg font-bold text-white">Active Corporate Cards</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($cards as $card)
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span class="font-bold text-indigo-400">{{ $card->card_provider }}</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-950/60 text-emerald-400 border border-emerald-800/50">{{ ucfirst($card->status) }}</span>
                </div>
                <div class="mt-4 font-mono text-lg tracking-widest text-white">{{ $card->card_masked_number }}</div>
                <div class="mt-3 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-slate-500 block">Cardholder</span>
                        <span class="text-slate-300 font-medium">{{ $card->card_holder_name }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-500 block">Assigned To</span>
                        <span class="text-slate-300 font-medium">{{ $card->employee?->first_name }} {{ $card->employee?->last_name }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 bg-slate-900 border border-slate-800 rounded-xl p-6 text-center text-slate-500">
                No corporate cards assigned.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Imported Transactions Feed -->
    <div class="space-y-3">
        <h2 class="text-lg font-bold text-white">Imported Transaction Feed</h2>
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-850 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-6 py-4">Transaction Ref &amp; Date</th>
                            <th class="px-6 py-4">Cardholder</th>
                            <th class="px-6 py-4">Merchant</th>
                            <th class="px-6 py-4">Amount</th>
                            <th class="px-6 py-4">Category Hint</th>
                            <th class="px-6 py-4">Match Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($transactions as $txn)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-6 py-4 font-medium text-white">
                                <div class="font-mono text-xs text-indigo-400">{{ $txn->transaction_reference }}</div>
                                <div class="text-xs text-slate-400">{{ $txn->transaction_date ? \Carbon\Carbon::parse($txn->transaction_date)->format('Y-m-d H:i') : 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-white">
                                {{ $txn->employee?->first_name }} {{ $txn->employee?->last_name }}
                            </td>
                            <td class="px-6 py-4 text-slate-300 font-medium">{{ $txn->merchant_name }}</td>
                            <td class="px-6 py-4 text-white font-bold font-mono">
                                ${{ number_format((float)$txn->amount, 2) }} {{ $txn->currency }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400 font-mono">{{ $txn->category_hint ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                @if($txn->is_matched)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-950/60 text-emerald-400 border border-emerald-800/50">
                                        <i class="fa-solid fa-check mr-1"></i> Matched
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-950/60 text-amber-400 border border-amber-800/50">
                                        Unmatched
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">No transactions found in feed.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
