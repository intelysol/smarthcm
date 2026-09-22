@extends('health_safety.layout')

@section('title', 'Employee Health & Occupational Profile')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-5">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-white">Occupational Health Profile</h1>
                <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-400 ring-1 ring-inset ring-emerald-500/20">
                    FIT FOR DUTY
                </span>
            </div>
            <p class="mt-1 text-sm text-slate-400">Employee ID: <span class="font-mono text-slate-300">{{ $employeeId }}</span></p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="document.getElementById('schedule-assessment-modal').classList.remove('hidden')" class="inline-flex items-center justify-center rounded-lg bg-slate-800 px-3.5 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-700 transition">
                Schedule Assessment
            </button>
            <button type="button" onclick="handleIssueClearance(this)" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition">
                Issue Clearance
            </button>
        </div>
    </div>

    <!-- Active Operational Restrictions Banner (Manager Safe View) -->
    <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-4">
        <div class="flex items-start gap-3">
            <span class="text-amber-400 text-lg">⚠️</span>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-amber-300">Active Operational Work Restriction</h3>
                <p class="mt-1 text-xs text-amber-200/90 leading-relaxed">
                    Lifting limit: Maximum 25 lbs (11.3 kg). No heavy manual material handling. Valid through 2026-11-15.
                </p>
                <div class="mt-2 text-[11px] text-amber-300/70">
                    Confidential medical diagnosis masked per HIPAA & GDPR privacy boundaries.
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Sections Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Mandatory Health Requirements -->
        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-6 shadow-sm">
            <h2 class="text-base font-semibold text-white mb-4">Mandatory Occupational Surveillance</h2>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-900/60 border border-slate-800">
                    <div>
                        <div class="text-sm font-medium text-white">Annual Audiometric Hearing Test</div>
                        <div class="text-xs text-slate-400">Mandated by Noise Exposure Hazard Profile</div>
                    </div>
                    <span class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-400 ring-1 ring-inset ring-emerald-500/20">
                        Compliant
                    </span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-900/60 border border-slate-800">
                    <div>
                        <div class="text-sm font-medium text-white">Respiratory Protection Medical Clearance</div>
                        <div class="text-xs text-slate-400">Tight-fitting negative pressure respirator</div>
                    </div>
                    <span class="inline-flex rounded-full bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-400 ring-1 ring-inset ring-amber-500/20">
                        Expiring in 28 Days
                    </span>
                </div>
            </div>
        </div>

        <!-- Clearance & Fitness Certifications -->
        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-6 shadow-sm">
            <h2 class="text-base font-semibold text-white mb-4">Fitness Certificate & Medical Provider</h2>
            <div class="space-y-3">
                <div class="p-3 rounded-lg bg-slate-900/60 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono text-emerald-400">CERT-2026-7841</span>
                        <span class="text-xs text-slate-400">Valid until: 2027-01-15</span>
                    </div>
                    <div class="text-sm font-medium text-white">Dr. Sarah Jenkins, MD, MPH (Occupational Medicine)</div>
                    <div class="text-xs text-slate-400">Certified by Metro Occupational Health Specialists</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Assessment Modal -->
<div id="schedule-assessment-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-lg font-bold text-white">Schedule Medical Assessment</h3>
            <button onclick="document.getElementById('schedule-assessment-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
                &times;
            </button>
        </div>
        <form onsubmit="handleScheduleAssessment(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Assessment Type</label>
                <select class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
                    <option value="audiometric">Audiometric Surveillance</option>
                    <option value="respiratory">Respiratory Fitness Exam</option>
                    <option value="ergonomic">Ergonomic Evaluation</option>
                    <option value="general">Routine Occupational Medical Examination</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Appointment Date</label>
                <input type="date" required value="{{ now()->addDays(7)->toDateString() }}" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Clinical Provider</label>
                <input type="text" required value="Metro Occupational Health Specialists" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-emerald-500">
            </div>
            <div class="pt-2 border-t border-slate-800 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('schedule-assessment-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg shadow-lg shadow-emerald-600/30 transition">Schedule Now</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleScheduleAssessment(e) {
    e.preventDefault();
    document.getElementById('schedule-assessment-modal').classList.add('hidden');
    window.showNotification('success', 'Occupational health assessment scheduled successfully.');
}

function handleIssueClearance(btn) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Issuing...';
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        window.showNotification('success', 'Occupational medical clearance issued. Fit-For-Duty record updated.');
    }, 400);
}
</script>
@endsection
