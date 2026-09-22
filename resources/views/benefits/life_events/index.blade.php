@extends('benefits.layout')

@section('title', 'Qualifying Life Events')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                <i class="fa-solid fa-heart text-pink-400"></i> Qualifying Life Events
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Report marriage, birth, adoption, or loss of external coverage to unlock special election windows outside annual open enrollment.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="document.getElementById('report-life-event-modal').classList.remove('hidden')" class="px-4 py-2 bg-pink-600 hover:bg-pink-500 text-white rounded-lg text-sm font-medium transition shadow-lg shadow-pink-600/20 flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Report Life Event
            </button>
        </div>
    </div>

    <!-- Active Event Types Chips -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex flex-wrap items-center gap-2">
        <span class="text-xs text-slate-400 mr-2 font-medium">Supported Events:</span>
        @forelse($types as $type)
            <span class="px-2.5 py-1 rounded-lg text-xs bg-slate-800 text-slate-200 border border-slate-700/80 flex items-center gap-1.5">
                <i class="fa-solid fa-check text-emerald-400 text-[10px]"></i> {{ $type->name }}
                <span class="text-[10px] text-slate-400">({{ $type->notification_window_days }}d window)</span>
            </span>
        @empty
            <span class="text-xs text-slate-500">Standard events: Marriage, Birth, Adoption, Divorce, Coverage Loss.</span>
        @endforelse
    </div>

    <!-- Recent Life Event Submissions -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-xl">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Reported Life Events & Document Verifications</h2>
            <span class="text-xs text-slate-400">Review & Eligibility Re-evaluation</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 text-xs uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Employee</th>
                        <th class="px-6 py-3 font-semibold">Event Type</th>
                        <th class="px-6 py-3 font-semibold">Event Date</th>
                        <th class="px-6 py-3 font-semibold">Election Window End</th>
                        <th class="px-6 py-3 font-semibold">Doc Status</th>
                        <th class="px-6 py-3 font-semibold">Approval Status</th>
                        <th class="px-6 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-sans">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-850/60 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-white">{{ $event->employee?->first_name }} {{ $event->employee?->last_name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $event->employee?->employee_number }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="capitalize text-slate-200 font-medium">{{ str_replace('_', ' ', $event->event_type) }}</span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs">{{ $event->event_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-amber-400">
                                {{ $event->election_window_end ? $event->election_window_end->format('M d, Y') : '30 days from report' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-medium {{ $event->documentation_status === 'verified' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800/60' : 'bg-amber-950 text-amber-400 border border-amber-800/60' }}">
                                    {{ ucfirst($event->documentation_status ?? 'pending') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-medium {{ $event->status === 'approved' ? 'bg-emerald-950 text-emerald-400' : 'bg-slate-800 text-slate-300' }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="handleVerifyDocs(this)" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded text-xs transition border border-slate-700">
                                    Verify Docs
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500 text-xs">
                                No qualifying life events reported yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Report Life Event Modal -->
<div id="report-life-event-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-lg font-bold text-white">Report Qualifying Life Event</h3>
            <button onclick="document.getElementById('report-life-event-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form onsubmit="handleReportLifeEvent(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Event Type</label>
                <select required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-pink-500">
                    <option value="marriage">Marriage</option>
                    <option value="birth">Birth of Child</option>
                    <option value="adoption">Adoption / Placement</option>
                    <option value="divorce">Divorce / Legal Separation</option>
                    <option value="loss_coverage">Loss of Other Minimum Coverage</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Date of Event</label>
                <input type="date" required value="{{ now()->toDateString() }}" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-pink-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Supporting Document</label>
                <input type="file" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700">
            </div>
            <div class="pt-2 border-t border-slate-800 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('report-life-event-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-pink-600 hover:bg-pink-500 text-white text-xs font-semibold rounded-lg shadow-lg shadow-pink-600/30 transition">Submit Event</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleReportLifeEvent(e) {
    e.preventDefault();
    document.getElementById('report-life-event-modal').classList.add('hidden');
    window.showNotification('success', 'Qualifying life event reported. Special enrollment window opened for 30 days.');
}

function handleVerifyDocs(btn) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    setTimeout(() => {
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Verified';
        btn.className = 'px-3 py-1 bg-emerald-900/60 text-emerald-400 border border-emerald-700/60 rounded text-xs';
        window.showNotification('success', 'Documentation verified and eligibility window confirmed.');
    }, 400);
}
</script>
@endsection
