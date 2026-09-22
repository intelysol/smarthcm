@extends('portal.layout')

@section('title', 'My Profile')

@section('content')
<div class="space-y-6">
    <!-- Profile Header Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-center space-x-5">
            <div class="w-16 h-16 rounded-2xl bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-300 text-3xl font-black shadow-inner">
                {{ substr($employee->first_name ?? 'E', 0, 1) }}
            </div>
            <div>
                <h1 class="text-xl font-bold text-white tracking-wide">{{ $employee->fullName() }}</h1>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $employee->designation?->name ?? 'Specialist' }} &bull; {{ $employee->department?->name ?? 'General' }}
                </p>
                <div class="flex items-center space-x-3 mt-2 text-[11px] text-slate-400">
                    <span><i class="fa-solid fa-id-badge mr-1 text-slate-500"></i> Code: <span class="font-mono text-slate-200">{{ $employee->employee_code ?? $employee->employee_number }}</span></span>
                    <span>&bull;</span>
                    <span><i class="fa-solid fa-envelope mr-1 text-slate-500"></i> {{ $employee->official_email ?? $employee->personal_email ?? 'No email configured' }}</span>
                </div>
            </div>
        </div>

        <button onclick="openProfileChangeModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-slate-700 transition flex items-center space-x-2">
            <i class="fa-solid fa-user-pen text-indigo-400"></i>
            <span>Request Profile Update</span>
        </button>
    </div>

    <!-- Multi-Section Information Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Section 1: Personal Details -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white flex items-center">
                    <i class="fa-solid fa-user-circle mr-2 text-indigo-400"></i> Personal Details
                </h3>
            </div>
            <dl class="divide-y divide-slate-800/80 text-xs">
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Full Legal Name</dt>
                    <dd class="font-semibold text-slate-200">{{ $employee->fullName() }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Gender</dt>
                    <dd class="font-semibold text-slate-200">{{ ucfirst($employee->gender ?? 'Not specified') }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Date of Birth</dt>
                    <dd class="font-semibold text-slate-200">{{ $employee->date_of_birth ? date('M d, Y', strtotime($employee->date_of_birth)) : 'Not configured' }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Nationality</dt>
                    <dd class="font-semibold text-slate-200">{{ $employee->nationality ?? 'National' }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Marital Status</dt>
                    <dd class="font-semibold text-slate-200">{{ ucfirst($employee->marital_status ?? 'Single') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Section 2: Employment & Hierarchy -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white flex items-center">
                    <i class="fa-solid fa-sitemap mr-2 text-teal-400"></i> Employment &amp; Reporting
                </h3>
            </div>
            <dl class="divide-y divide-slate-800/80 text-xs">
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Employment Status</dt>
                    <dd><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400">{{ strtoupper($employee->employment_status ?? 'ACTIVE') }}</span></dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Department</dt>
                    <dd class="font-semibold text-slate-200">{{ $employee->department?->name ?? 'Enterprise Operations' }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Job Title</dt>
                    <dd class="font-semibold text-slate-200">{{ $employee->designation?->name ?? 'Specialist' }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Joining Date</dt>
                    <dd class="font-semibold text-slate-200">{{ $employee->joining_date ? date('M d, Y', strtotime($employee->joining_date)) : 'Standard' }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Reporting Manager</dt>
                    <dd class="font-semibold text-indigo-400">{{ $employee->reportingManager ? $employee->reportingManager->fullName() : 'Executive Office' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Section 3: Contact Details -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white flex items-center">
                    <i class="fa-solid fa-phone mr-2 text-amber-400"></i> Contact &amp; Emergency
                </h3>
            </div>
            <dl class="divide-y divide-slate-800/80 text-xs">
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Mobile Phone</dt>
                    <dd class="font-semibold text-slate-200">{{ $employee->mobile ?? 'Not on file' }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Work Phone</dt>
                    <dd class="font-semibold text-slate-200">{{ $employee->office_phone ?? 'Ext. 1024' }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Personal Email</dt>
                    <dd class="font-semibold text-slate-200">{{ $employee->personal_email ?? 'Protected' }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Emergency Phone</dt>
                    <dd class="font-semibold text-rose-400">{{ $employee->emergency_phone ?? 'Not on file' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Section 4: Address Details -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white flex items-center">
                    <i class="fa-solid fa-location-dot mr-2 text-rose-400"></i> Location &amp; Address
                </h3>
            </div>
            <dl class="divide-y divide-slate-800/80 text-xs">
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Present Address</dt>
                    <dd class="font-semibold text-slate-200 text-right">{{ $employee->present_address ?? 'Enterprise HQ St, Tech City' }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">Work Location</dt>
                    <dd class="font-semibold text-slate-200">{{ $employee->workLocation?->name ?? 'Main Campus' }}</dd>
                </div>
                <div class="py-2.5 flex justify-between">
                    <dt class="text-slate-400">City / Country</dt>
                    <dd class="font-semibold text-slate-200">{{ $employee->city ?? 'Islamabad' }}, {{ $employee->country ?? 'Pakistan' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>

<!-- Profile Change Request Modal -->
<div id="profile-change-modal" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-sm font-bold text-white flex items-center">
                <i class="fa-solid fa-user-pen text-indigo-400 mr-2"></i> Request Profile Information Update
            </h3>
            <button onclick="closeProfileChangeModal()" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="profile-change-form" onsubmit="handleProfileChangeSubmit(event)" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Field to Update</label>
                <select id="pcr-field" name="field_name" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white focus:outline-none focus:border-indigo-500">
                    <option value="phone">Primary Phone Number</option>
                    <option value="present_address">Present Residential Address</option>
                    <option value="emergency_contact">Emergency Contact Information</option>
                    <option value="marital_status">Marital Status</option>
                    <option value="bank_account">Bank Account / IBAN Information</option>
                </select>
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">New Value</label>
                <input type="text" id="pcr-value" name="new_value" required placeholder="Enter updated information" 
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Reason / Justification</label>
                <textarea id="pcr-reason" name="reason" rows="3" required placeholder="Describe the reason for this profile change..."
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"></textarea>
            </div>
            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="closeProfileChangeModal()" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 transition">Cancel</button>
                <button type="submit" id="btn-submit-pcr" class="px-4 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-500 transition flex items-center">
                    <i class="fa-solid fa-paper-plane mr-1.5"></i> Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openProfileChangeModal() {
        const m = document.getElementById('profile-change-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function closeProfileChangeModal() {
        const m = document.getElementById('profile-change-modal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

    async function handleProfileChangeSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-pcr');
        const fieldName = document.getElementById('pcr-field').value;
        const newValue = document.getElementById('pcr-value').value;
        const reason = document.getElementById('pcr-reason').value;

        await window.submitAsync(btn, async () => {
            try {
                const res = await fetch('/api/v1/hcm/personal-data/employees/{{ $employee->id }}/personal-data-change-requests', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Tenant-ID': '{{ $employee->tenant_id ?? "default" }}'
                    },
                    body: JSON.stringify({
                        field_name: fieldName,
                        new_value: newValue,
                        justification: reason,
                        change_type: 'self_service_update'
                    })
                });
                const data = await res.json();
                if (data.success || res.status === 200 || res.status === 201) {
                    window.showNotification('success', 'Profile change request submitted for HR approval.');
                    closeProfileChangeModal();
                } else {
                    window.showNotification('error', data.error?.message || 'Unable to submit profile change request.', null, data.request_id);
                }
            } catch (err) {
                window.showNotification('error', 'Network error occurred.');
            }
        });
    }
</script>
@endsection
