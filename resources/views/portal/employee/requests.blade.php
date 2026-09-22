@extends('portal.layout')

@section('title', 'My Requests')

@section('content')
<div class="space-y-6">
    <!-- Top Header & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-wide">My Requests Center</h1>
            <p class="text-xs text-slate-400 mt-1">Unified view across all your leave, expense, document, and HR service requests.</p>
        </div>
        <button onclick="openLeaveModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-500/20 transition flex items-center space-x-2">
            <i class="fa-solid fa-plus"></i>
            <span>Submit Request</span>
        </button>
    </div>

    <!-- Request Status Filter Tabs -->
    <div class="flex space-x-2 border-b border-slate-800 pb-2 text-xs">
        <a href="{{ route('portal.requests') }}" class="px-3 py-1.5 rounded-lg {{ empty(request('status')) ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:bg-slate-850' }} transition">
            All ({{ count($requests) }})
        </a>
        <a href="{{ route('portal.requests') }}?status=pending" class="px-3 py-1.5 rounded-lg {{ request('status') === 'pending' ? 'bg-amber-600 text-white font-bold' : 'text-slate-400 hover:bg-slate-850' }} transition">
            Pending
        </a>
        <a href="{{ route('portal.requests') }}?status=approved" class="px-3 py-1.5 rounded-lg {{ request('status') === 'approved' ? 'bg-emerald-600 text-white font-bold' : 'text-slate-400 hover:bg-slate-850' }} transition">
            Approved
        </a>
        <a href="{{ route('portal.requests') }}?status=rejected" class="px-3 py-1.5 rounded-lg {{ request('status') === 'rejected' ? 'bg-rose-600 text-white font-bold' : 'text-slate-400 hover:bg-slate-850' }} transition">
            Rejected
        </a>
    </div>

    <!-- Unified Requests List -->
    <div class="space-y-4">
        @forelse($requests as $req)
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm hover:border-slate-700 transition space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono tracking-wider bg-slate-800 text-slate-300 border border-slate-700">
                                {{ $req['type'] }}
                            </span>
                            <h3 class="text-sm font-bold text-white">{{ $req['title'] }}</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                {{ $req['status'] === 'APPROVED' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}
                                {{ in_array($req['status'], ['PENDING', 'SUBMITTED']) ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                                {{ $req['status'] === 'REJECTED' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : '' }}">
                                {{ $req['status'] }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400">
                            Submitted on {{ date('M d, Y \a\t H:i', strtotime($req['submitted_at'])) }} &bull; Current Approver: <span class="text-slate-200 font-medium">{{ $req['approver'] }}</span>
                        </p>
                    </div>

                    <div class="text-right text-xs">
                        <div class="text-slate-400 text-[10px] uppercase">Next Step</div>
                        <div class="text-indigo-400 font-semibold">{{ $req['next_action'] }}</div>
                    </div>
                </div>

                <!-- Timeline Visualizer -->
                <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/80">
                    <div class="flex items-center space-x-2 sm:space-x-4 text-xs">
                        @foreach($req['timeline'] as $idx => $step)
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold 
                                    {{ $step['completed'] ? 'bg-emerald-600 text-white' : ($step['current'] ? 'bg-amber-600 text-white animate-pulse' : 'bg-slate-800 text-slate-500') }}">
                                    @if($step['completed'])
                                        <i class="fa-solid fa-check"></i>
                                    @else
                                        {{ $idx + 1 }}
                                    @endif
                                </div>
                                <span class="{{ $step['completed'] || $step['current'] ? 'text-slate-200 font-medium' : 'text-slate-500' }}">
                                    {{ $step['step'] }}
                                </span>
                            </div>
                            @if(!$loop->last)
                                <div class="flex-1 h-0.5 bg-slate-800"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
                <i class="fa-solid fa-inbox text-3xl text-slate-600 mb-3 block"></i>
                <h3 class="text-sm font-bold text-slate-200">No requests found</h3>
                <p class="text-xs text-slate-500 mt-1">Submit a new leave, expense, or document request anytime.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Leave Application Modal -->
<div id="leave-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-bold text-white flex items-center">
                <i class="fa-solid fa-plane-departure text-teal-400 mr-2"></i> Submit Leave Request
            </h3>
            <button onclick="closeLeaveModal()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>

        <form id="leave-form" onsubmit="handleLeaveSubmit(event)" class="space-y-4 text-xs">
            <div>
                <label class="block text-slate-300 font-medium mb-1">Leave Type</label>
                <select id="leave_type_id" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-200 focus:outline-none focus:border-indigo-500">
                    @foreach($leaveTypes as $lt)
                        <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                    @endforeach
                    @if(empty($leaveTypes))
                        <option value="default_annual">Annual Leave</option>
                        <option value="default_sick">Sick Leave</option>
                        <option value="default_casual">Casual Leave</option>
                    @endif
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-300 font-medium mb-1">Start Date</label>
                    <input type="date" id="start_date" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-200 focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-medium mb-1">End Date</label>
                    <input type="date" id="end_date" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-200 focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Duration (Days)</label>
                <input type="number" id="duration" step="0.5" min="0.5" value="1.0" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Reason / Notes</label>
                <textarea id="reason" rows="3" placeholder="Explain the reason for this leave request..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-200 focus:outline-none focus:border-indigo-500"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-end space-x-2">
                <button type="button" onclick="closeLeaveModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg font-medium transition">Cancel</button>
                <button type="submit" id="submit-leave-btn" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-bold transition shadow">Submit Application</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openLeaveModal() {
        document.getElementById('leave-modal').classList.remove('hidden');
    }
    function closeLeaveModal() {
        document.getElementById('leave-modal').classList.add('hidden');
    }

    // Auto-open modal if URL contains ?modal=leave
    if (new URLSearchParams(window.location.search).get('modal') === 'leave') {
        openLeaveModal();
    }

    async function handleLeaveSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('submit-leave-btn');
        btn.disabled = true;
        btn.innerText = 'Submitting...';

        const payload = {
            leave_type_id: document.getElementById('leave_type_id').value,
            start_date: document.getElementById('start_date').value,
            end_date: document.getElementById('end_date').value,
            duration: parseFloat(document.getElementById('duration').value),
            reason: document.getElementById('reason').value,
        };

        try {
            const res = await fetch('/api/me/leave/apply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Tenant-ID': '{{ $employee->tenant_id }}',
                    'X-Employee-ID': '{{ $employee->id }}'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (res.ok && (data.success !== false)) {
                window.showNotification('success', 'Leave application submitted successfully.');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showNotification('error', data.error?.message || data.message || 'Error submitting leave request.', null, data.request_id);
            }
        } catch (err) {
            window.showNotification('error', 'Submission error. Please check your network connection.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Submit Application';
        }
    }
</script>
@endsection
