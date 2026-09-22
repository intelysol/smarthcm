@extends('offboarding.layout')

@section('title', 'My Separation & Exit Portal — Flow HCM')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-rose-600 via-purple-600 to-indigo-600 rounded-2xl p-8 text-white shadow-lg">
        <span class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider bg-white/20 text-white backdrop-blur-sm">
            Employee Self-Service & Post-Exit Portal
        </span>
        <h1 class="text-3xl font-extrabold mt-3">Employee Separation & Clearance Center</h1>
        <p class="text-sm text-rose-100 mt-1 max-w-xl">
            Track your clearance progress across departments, complete handover items, review your notice timeline, and access authorized exit documents.
        </p>
    </div>

    @if($separation)
        <!-- Separation Overview Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 space-y-6">
            <div class="flex items-start justify-between border-b border-slate-100 pb-4">
                <div>
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-rose-50 text-rose-700">
                        {{ $separation->separationType?->name ?? 'Separation' }}
                    </span>
                    <h2 class="text-xl font-bold text-slate-900 mt-2 font-mono">{{ $separation->request_number }}</h2>
                    <div class="text-xs text-slate-500 mt-1">
                        Final Working Day: <strong>{{ $separation->approved_last_working_day ? $separation->approved_last_working_day->format('F d, Y') : ($separation->proposed_last_working_day ? $separation->proposed_last_working_day->format('F d, Y') : 'Pending Approval') }}</strong>
                    </div>
                </div>

                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                        @if($separation->status === 'exited') bg-emerald-100 text-emerald-800
                        @elseif($separation->status === 'notice_period') bg-blue-100 text-blue-800
                        @elseif($separation->status === 'clearance') bg-purple-100 text-purple-800
                        @elseif($separation->status === 'pending_approval') bg-amber-100 text-amber-800
                        @else bg-slate-100 text-slate-700 @endif">
                        {{ ucwords(str_replace('_', ' ', $separation->status)) }}
                    </span>
                </div>
            </div>

            <!-- Clearance Checklist Matrix -->
            <div>
                <h3 class="text-base font-bold text-slate-900 mb-3">Multi-Department Clearance Status</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($separation->clearances as $clearance)
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">{{ $clearance->department }} Clearance</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    @if($clearance->status === 'cleared') bg-emerald-100 text-emerald-800
                                    @elseif($clearance->status === 'waived') bg-purple-100 text-purple-800
                                    @else bg-amber-100 text-amber-800 @endif">
                                    {{ $clearance->status }}
                                </span>
                            </div>

                            <ul class="space-y-1 text-xs text-slate-600">
                                @foreach($clearance->items as $item)
                                    <li class="flex items-center space-x-2">
                                        @if($item->status === 'cleared' || $item->status === 'waived')
                                            <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                            <span class="line-through text-slate-400">{{ $item->title }}</span>
                                        @else
                                            <i class="fa-solid fa-circle text-amber-400 text-[8px]"></i>
                                            <span>{{ $item->title }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <div class="col-span-2 text-xs text-slate-400 py-4 text-center">No clearance items assigned.</div>
                    @endforelse
                </div>
            </div>

            <!-- Exit Documents Download -->
            <div class="border-t border-slate-100 pt-4">
                <h3 class="text-base font-bold text-slate-900 mb-2">Authorized Exit Documents</h3>
                <div class="space-y-2">
                    @forelse($separation->documents as $doc)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition text-xs">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-file-pdf text-rose-500 text-base"></i>
                                <span class="font-medium text-slate-900">{{ $doc->title }}</span>
                            </div>
                            <a href="/api/v1/documents/{{ $doc->id }}/download" class="px-3 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition flex items-center space-x-1">
                                <i class="fa-solid fa-download mr-1"></i> Download
                            </a>
                        </div>
                    @empty
                        <div class="text-xs text-slate-400 py-2">
                            Exit certificates and relieving letters will be generated upon final clearance and exit execution.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <!-- No Active Separation - Resignation Submission Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 space-y-6">
            <h2 class="text-xl font-bold text-slate-900">Initiate Voluntary Resignation</h2>
            <p class="text-sm text-slate-500">
                You currently have no active separation requests. If you wish to submit a voluntary resignation, please enter your proposed last working day and comments below.
            </p>

            <form action="/api/v1/me/separation/resign" method="POST" class="space-y-4 max-w-lg">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Proposed Last Working Day</label>
                    <input type="date" name="proposed_last_working_day" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Primary Reason</label>
                    <input type="text" name="reason" placeholder="e.g. Career growth, relocation, higher education" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Comments (Optional)</label>
                    <textarea name="comments" rows="3" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500" placeholder="Optional notes for your manager and HR..."></textarea>
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm shadow-sm transition">
                    Submit Resignation &rarr;
                </button>
            </form>
        </div>
    @endif

</div>
@endsection
