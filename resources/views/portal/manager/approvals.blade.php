@extends('portal.layout')

@section('title', 'Manager Approval Inbox')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-xl font-bold text-white tracking-wide">Manager Approval Inbox</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300">
                    {{ count($approvals) }} Pending
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Review, approve, or reject employee requests across Leave, Expenses, and Services within your management scope.</p>
        </div>
    </div>

    <!-- Approvals List -->
    <div class="space-y-4">
        @forelse($approvals as $item)
            <div id="approval-card-{{ $item['id'] }}" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono tracking-wider bg-slate-800 text-slate-300 border border-slate-700">
                                {{ $item['type'] }}
                            </span>
                            <h3 class="text-sm font-bold text-white">{{ $item['title'] }}</h3>
                        </div>
                        <p class="text-xs text-slate-400">
                            Requested by <span class="font-semibold text-slate-200">{{ $item['employee']['name'] }}</span> ({{ $item['employee']['code'] ?? '' }}) &bull; {{ date('M d, Y \a\t H:i', strtotime($item['submitted_at'])) }}
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-2">
                        <button onclick="handleApprovalAction('{{ $item['type'] }}', '{{ $item['id'] }}', 'reject')" class="px-3.5 py-1.5 bg-slate-800 hover:bg-rose-500/20 hover:text-rose-400 hover:border-rose-500/30 border border-slate-700 rounded-xl text-xs font-semibold text-slate-300 transition">
                            <i class="fa-solid fa-xmark mr-1"></i> Reject
                        </button>
                        <button onclick="handleApprovalAction('{{ $item['type'] }}', '{{ $item['id'] }}', 'approve')" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow transition flex items-center space-x-1.5">
                            <i class="fa-solid fa-check mr-1"></i> Approve
                        </button>
                    </div>
                </div>

                <!-- Request Details Box -->
                <div class="bg-slate-950/60 p-3.5 rounded-xl border border-slate-800/80 text-xs text-slate-300 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @if($item['type'] === 'LEAVE')
                        <div>
                            <span class="text-slate-500 text-[10px] uppercase font-bold block">Dates</span>
                            <span class="font-medium text-slate-200">{{ $item['details']['dates'] ?? 'Requested Dates' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 text-[10px] uppercase font-bold block">Duration</span>
                            <span class="font-medium text-slate-200">{{ $item['details']['duration'] ?? 1 }} Days</span>
                        </div>
                        <div>
                            <span class="text-slate-500 text-[10px] uppercase font-bold block">Reason</span>
                            <span class="font-medium text-slate-200">{{ $item['details']['reason'] ?? 'None provided' }}</span>
                        </div>
                    @elseif($item['type'] === 'EXPENSE')
                        <div>
                            <span class="text-slate-500 text-[10px] uppercase font-bold block">Claim Amount</span>
                            <span class="font-bold text-emerald-400 font-mono">{{ $item['details']['currency'] }} {{ number_format($item['details']['amount'], 2) }}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-slate-500 text-[10px] uppercase font-bold block">Description</span>
                            <span class="font-medium text-slate-200">{{ $item['details']['description'] }}</span>
                        </div>
                    @else
                        <div class="sm:col-span-3">
                            <span class="text-slate-500 text-[10px] uppercase font-bold block">Description</span>
                            <span class="font-medium text-slate-200">{{ $item['details']['description'] ?? 'Standard service request' }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
                <i class="fa-solid fa-stamp text-3xl text-slate-600 mb-3 block"></i>
                <h3 class="text-sm font-bold text-slate-200">Zero pending approvals</h3>
                <p class="text-xs text-slate-500 mt-1">All requests from your team have been processed and resolved.</p>
            </div>
        @endforelse
    </div>
</div>

<script>
    async function handleApprovalAction(type, id, action) {
        const comment = action === 'reject' ? prompt('Enter reason for rejection:') : null;
        if (action === 'reject' && comment === null) return;

        try {
            const res = await fetch(`/api/manager/approvals/${type}/${id}/action`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Tenant-ID': '{{ $manager->tenant_id }}',
                    'X-Employee-ID': '{{ $manager->id }}'
                },
                body: JSON.stringify({ action, comments: comment })
            });
            const data = await res.json();
            if (res.ok && data.success !== false) {
                window.showNotification('success', `Request has been ${action}d successfully.`);
                const card = document.getElementById(`approval-card-${id}`);
                if (card) {
                    card.classList.add('opacity-50', 'pointer-events-none');
                    setTimeout(() => card.remove(), 400);
                }
            } else {
                window.showNotification('error', data.error?.message || data.message || 'Unable to complete approval action.', null, data.request_id);
            }
        } catch (err) {
            window.showNotification('error', 'Approval action failed. Please verify your connection.');
        }
    }
</script>
@endsection
