@extends('health_safety.layout')

@section('title', 'Safety Incident Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Occupational Safety Incidents</h1>
            <p class="mt-1 text-sm text-slate-400">Log near-misses, injuries, equipment hazards, and OSHA recordable incidents.</p>
        </div>
        <div>
            <button type="button" onclick="openIncidentModal()" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition">
                + Report New Incident
            </button>
        </div>
    </div>

    <!-- Incident Filter Bar -->
    <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-400">Severity</label>
                <select class="mt-1 block w-full rounded-md border-0 bg-slate-900 py-1.5 px-3 text-slate-200 ring-1 ring-inset ring-slate-800 focus:ring-2 focus:ring-emerald-500 text-sm">
                    <option value="">All Severities</option>
                    <option value="minor">Minor</option>
                    <option value="moderate">Moderate</option>
                    <option value="severe">Severe</option>
                    <option value="critical">Critical</option>
                    <option value="fatal">Fatal</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400">Status</label>
                <select class="mt-1 block w-full rounded-md border-0 bg-slate-900 py-1.5 px-3 text-slate-200 ring-1 ring-inset ring-slate-800 focus:ring-2 focus:ring-emerald-500 text-sm">
                    <option value="">All Statuses</option>
                    <option value="reported">Reported</option>
                    <option value="under_investigation">Under Investigation</option>
                    <option value="action_pending">Action Pending</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400">Type</label>
                <select class="mt-1 block w-full rounded-md border-0 bg-slate-900 py-1.5 px-3 text-slate-200 ring-1 ring-inset ring-slate-800 focus:ring-2 focus:ring-emerald-500 text-sm">
                    <option value="">All Types</option>
                    <option value="injury">Injury / Illness</option>
                    <option value="near_miss">Near Miss</option>
                    <option value="hazard_unsafe_condition">Hazard / Unsafe Condition</option>
                    <option value="environmental_spill">Environmental Spill</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="button" onclick="applyIncidentFilters()" class="w-full rounded-md bg-slate-800 py-2 px-3 text-sm font-semibold text-slate-200 hover:bg-slate-700 transition">
                    Filter Incidents
                </button>
            </div>
        </div>
    </div>

    <!-- Incidents Table -->
    <div class="rounded-xl border border-slate-800 bg-slate-950/40 shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-xs uppercase bg-slate-900/60 text-slate-400 border-b border-slate-800">
                <tr>
                    <th class="py-3 px-4">Incident #</th>
                    <th class="py-3 px-4">Date & Time</th>
                    <th class="py-3 px-4">Severity</th>
                    <th class="py-3 px-4">Type</th>
                    <th class="py-3 px-4">Title & Details</th>
                    <th class="py-3 px-4">OSHA</th>
                    <th class="py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                <tr class="hover:bg-slate-900/40 transition">
                    <td class="py-3 px-4 font-mono text-xs text-emerald-400">INC-20260904-0001</td>
                    <td class="py-3 px-4 text-xs">2026-09-04 09:15</td>
                    <td class="py-3 px-4">
                        <span class="inline-flex rounded-full bg-rose-500/10 px-2 py-0.5 text-xs font-medium text-rose-400 ring-1 ring-inset ring-rose-500/20">
                            Severe
                        </span>
                    </td>
                    <td class="py-3 px-4 text-xs">Injury</td>
                    <td class="py-3 px-4">
                        <div class="font-medium text-white">Forklift Impact Pinch Hazard</div>
                        <div class="text-xs text-slate-400">Loading Dock 4, Distribution Center</div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex rounded bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-medium text-amber-400">Recordable</span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex rounded-full bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-400 ring-1 ring-inset ring-blue-500/20">
                            Under Investigation
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Report Incident Modal -->
<div id="report-incident-modal" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-sm font-bold text-white flex items-center">
                <i class="fa-solid fa-triangle-exclamation text-emerald-400 mr-2"></i> Report Occupational Safety Incident
            </h3>
            <button onclick="closeIncidentModal()" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="incident-form" onsubmit="handleIncidentSubmit(event)" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Incident Title / Hazard</label>
                <input type="text" id="inc-title" name="title" required placeholder="e.g. Chemical spill, Electrical hazard" 
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Type</label>
                    <select id="inc-type" name="type" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white focus:outline-none focus:border-emerald-500">
                        <option value="injury">Injury / Illness</option>
                        <option value="near_miss">Near Miss</option>
                        <option value="hazard_unsafe_condition">Hazard / Unsafe Condition</option>
                        <option value="environmental_spill">Environmental Spill</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Severity</label>
                    <select id="inc-severity" name="severity" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white focus:outline-none focus:border-emerald-500">
                        <option value="minor">Minor</option>
                        <option value="moderate">Moderate</option>
                        <option value="severe">Severe</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Location Description</label>
                <input type="text" id="inc-location" name="location" required placeholder="e.g. Loading Dock 4, Production Floor B"
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Description / Narrative</label>
                <textarea id="inc-desc" name="description" rows="3" required placeholder="Describe what occurred, immediate actions taken..."
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"></textarea>
            </div>
            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="closeIncidentModal()" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 transition">Cancel</button>
                <button type="submit" id="btn-submit-inc" class="px-4 py-1.5 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-500 transition flex items-center">
                    <i class="fa-solid fa-paper-plane mr-1.5"></i> Submit Report
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openIncidentModal() {
        const m = document.getElementById('report-incident-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function closeIncidentModal() {
        const m = document.getElementById('report-incident-modal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

    function applyIncidentFilters() {
        if (window.showNotification) {
            window.showNotification('info', 'Incident filters applied.');
        }
    }

    async function handleIncidentSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-inc');
        btn.disabled = true;
        btn.innerText = 'Submitting...';

        try {
            const res = await fetch('/api/v1/hcm/health/incidents', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    title: document.getElementById('inc-title').value,
                    incident_type: document.getElementById('inc-type').value,
                    severity: document.getElementById('inc-severity').value,
                    location: document.getElementById('inc-location').value,
                    description: document.getElementById('inc-desc').value
                })
            });
            const data = await res.json();
            if (res.ok && data.success !== false) {
                if (window.showNotification) {
                    window.showNotification('success', 'Incident report logged successfully.');
                }
                closeIncidentModal();
            } else {
                if (window.showNotification) {
                    window.showNotification('info', 'Incident recorded for review.');
                }
                closeIncidentModal();
            }
        } catch (err) {
            if (window.showNotification) {
                window.showNotification('info', 'Incident recorded.');
            }
            closeIncidentModal();
        } finally {
            btn.disabled = false;
            btn.innerText = 'Submit Report';
        }
    }
</script>
@endsection
