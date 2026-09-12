@extends('personal_data.layout')

@section('title', 'Employee Personal Data Portal — ' . $employee->fullName())

@section('content')
<div class="space-y-6">
    <!-- Employee Header Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col md:flex-row md:items-center md:justify-between">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-2xl border-2 border-emerald-300">
                {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900">{{ $employee->fullName() }}</h1>
                <p class="text-sm text-slate-500">{{ $employee->designation?->designation_name ?? 'Employee' }} • {{ $employee->department?->department_name ?? 'Department' }}</p>
                <div class="flex items-center space-x-3 mt-1 text-xs text-slate-400">
                    <span>ID: {{ $employee->employee_code ?? $employee->employee_number }}</span>
                    <span>•</span>
                    <span>Status: <strong class="text-emerald-600 font-semibold uppercase">{{ $employee->employment_status ?? 'Active' }}</strong></span>
                </div>
            </div>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <a href="{{ route('personal-data.governance') }}" class="px-4 py-2 text-xs font-semibold rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left mr-1"></i>Back to Governance
            </a>
        </div>
    </div>

    <!-- Personal Data Tabs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Core Personal Details Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 flex items-center space-x-2">
                    <i class="fa-solid fa-user text-emerald-600"></i>
                    <span>Personal Info</span>
                </h3>
            </div>
            <dl class="space-y-3 text-xs">
                <div>
                    <dt class="text-slate-400 font-medium">Full Legal Name</dt>
                    <dd class="text-slate-800 font-semibold mt-0.5">{{ $employee->fullName() }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 font-medium">Preferred Name</dt>
                    <dd class="text-slate-800 font-semibold mt-0.5">{{ $employee->preferred_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 font-medium">Date of Birth</dt>
                    <dd class="text-slate-800 font-semibold mt-0.5">{{ $employee->date_of_birth ? $employee->date_of_birth->format('M d, Y') : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 font-medium">Gender / Marital Status</dt>
                    <dd class="text-slate-800 font-semibold mt-0.5">{{ ucfirst($employee->gender ?? '—') }} / {{ ucfirst($employee->marital_status ?? '—') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 font-medium">Nationality / Blood Group</dt>
                    <dd class="text-slate-800 font-semibold mt-0.5">{{ $employee->nationality ?? '—' }} / {{ $employee->blood_group ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 font-medium">Personal Contact</dt>
                    <dd class="text-slate-800 font-semibold mt-0.5">{{ $employee->personal_email ?? '—' }} | {{ $employee->mobile ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Addresses Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 flex items-center space-x-2">
                    <i class="fa-solid fa-location-dot text-sky-600"></i>
                    <span>Address & Residence</span>
                </h3>
            </div>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-sky-100 text-sky-800">Present Address</span>
                    <p class="text-slate-800 font-medium mt-1">{{ $employee->present_address ?? 'No present address recorded.' }}</p>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-indigo-100 text-indigo-800">Permanent Address</span>
                    <p class="text-slate-800 font-medium mt-1">{{ $employee->permanent_address ?? 'No permanent address recorded.' }}</p>
                </div>
            </div>
        </div>

        <!-- Emergency Contacts Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 flex items-center space-x-2">
                    <i class="fa-solid fa-phone-volume text-rose-600"></i>
                    <span>Emergency Contacts</span>
                </h3>
            </div>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 font-medium">Emergency Phone:</span>
                    <p class="text-slate-800 font-semibold text-sm mt-0.5">{{ $employee->emergency_phone ?? 'Not registered' }}</p>
                </div>
                <p class="text-slate-500 text-xs mt-2">
                    Emergency contacts are prioritized for rapid notification during health and safety incidents.
                </p>
            </div>
        </div>
    </div>

    <!-- Governance & Security Note -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start space-x-3">
        <i class="fa-solid fa-lock text-amber-600 mt-0.5 text-sm"></i>
        <div class="text-xs text-amber-800 space-y-1">
            <p class="font-bold">Enterprise Privacy & Audit Protection</p>
            <p>National Identification Numbers, Passport numbers, and Direct Deposit Bank Accounts are encrypted at rest with field-level role-based masking. All changes to personal data require workflow verification.</p>
        </div>
    </div>
</div>
@endsection
