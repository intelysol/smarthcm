@extends('layouts.attendance')

@section('title', 'My Attendance & Schedule')

@section('content')
<div class="space-y-6">
    <!-- Header with Quick Punch -->
    <div class="bg-gradient-to-r from-indigo-900/60 to-slate-800 rounded-3xl p-6 border border-indigo-500/30 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <span class="px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-semibold border border-indigo-500/30">Employee Self-Service</span>
            <h1 class="text-2xl font-bold text-white mt-2">Personal Attendance & Duty Schedule</h1>
            <p class="text-sm text-slate-300 mt-1">View your shift roster, clock in/out with geolocation, and request regularizations.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="handleAttendancePunch(this, 'CHECK_IN')" class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 transition flex items-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i> Clock In
            </button>
            <button onclick="handleAttendancePunch(this, 'CHECK_OUT')" class="px-5 py-3 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm shadow-lg shadow-rose-600/30 transition flex items-center gap-2">
                <i class="fa-solid fa-right-from-bracket"></i> Clock Out
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-slate-800/80 rounded-2xl p-4 border border-slate-700/60">
            <span class="text-xs text-slate-400">Total Worked This Month</span>
            <div class="text-2xl font-bold text-white mt-1">168.5 hrs</div>
        </div>
        <div class="bg-slate-800/80 rounded-2xl p-4 border border-slate-700/60">
            <span class="text-xs text-slate-400">Punctuality Score</span>
            <div class="text-2xl font-bold text-emerald-400 mt-1">100%</div>
        </div>
        <div class="bg-slate-800/80 rounded-2xl p-4 border border-slate-700/60">
            <span class="text-xs text-slate-400">Overtime Recorded</span>
            <div class="text-2xl font-bold text-indigo-400 mt-1">8.5 hrs</div>
        </div>
        <div class="bg-slate-800/80 rounded-2xl p-4 border border-slate-700/60">
            <span class="text-xs text-slate-400">Pending Regularizations</span>
            <div class="text-2xl font-bold text-amber-400 mt-1">0</div>
        </div>
    </div>
</div>

<script>
async function handleAttendancePunch(btn, eventType) {
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    try {
        const res = await fetch('/api/me/attendance/clock', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ action: eventType === 'CHECK_IN' ? 'clock_in' : 'clock_out' })
        });
        const data = await res.json();
        if (data.success) {
            window.showNotification('success', (eventType === 'CHECK_IN' ? 'Clocked in' : 'Clocked out') + ' successfully.');
        } else {
            window.showNotification('error', data.error?.message || 'Failed to record attendance punch.');
        }
    } catch (err) {
        window.showNotification('error', 'Network error while recording punch.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
}
</script>
@endsection
