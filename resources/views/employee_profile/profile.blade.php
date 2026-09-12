@extends('employee_profile.layout')

@section('title', $employee->fullName() . ' — Employee Profile — Flow HCM')

@section('content')
<div class="space-y-8">

    <!-- Profile Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center space-x-5">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white font-bold text-3xl shadow-md">
                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-extrabold text-slate-900">{{ $employee->fullName() }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                        @if($employee->employment_status === 'active') bg-emerald-50 text-emerald-700
                        @else bg-slate-100 text-slate-700 @endif">
                        {{ $employee->employment_status }}
                    </span>
                </div>
                <p class="text-sm font-semibold text-indigo-600 mt-0.5">{{ $employee->designation?->name ?? 'Staff Member' }}</p>
                <div class="text-xs text-slate-500 mt-2 flex flex-wrap items-center gap-3">
                    <span><i class="fa-solid fa-id-badge mr-1 text-slate-400"></i>{{ $employee->employee_code ?? $employee->employee_number }}</span>
                    <span>&bull;</span>
                    <span><i class="fa-solid fa-building mr-1 text-slate-400"></i>{{ $employee->department?->name ?? 'General' }}</span>
                    <span>&bull;</span>
                    <span><i class="fa-solid fa-location-dot mr-1 text-slate-400"></i>{{ $employee->branch?->name ?? 'Main Branch' }}</span>
                    @if($employee->reportingManager)
                        <span>&bull;</span>
                        <span><i class="fa-solid fa-user-tie mr-1 text-slate-400"></i>Manager: <strong>{{ $employee->reportingManager->fullName() }}</strong></span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('employee_profile.org_chart') }}" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <i class="fa-solid fa-sitemap mr-1.5 text-indigo-600"></i>Focus in Org Chart
            </a>
            <button class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                <i class="fa-solid fa-pen-to-square mr-1.5"></i>Request Profile Update
            </button>
        </div>
    </div>

    <!-- Section Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left Column: Employment & Subsystem Summaries -->
        <div class="space-y-6 lg:col-span-2">

            <!-- Employment Details -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center space-x-2">
                    <i class="fa-solid fa-briefcase text-indigo-600"></i>
                    <span>Employment Information</span>
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 uppercase font-medium">Employment Type</span>
                        <p class="font-bold text-slate-900 mt-0.5">{{ $employee->employmentType?->name ?? 'Permanent Full-Time' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500 uppercase font-medium">Original Hire Date</span>
                        <p class="font-bold text-slate-900 mt-0.5">{{ $employee->joining_date ? $employee->joining_date->format('M d, Y') : 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500 uppercase font-medium">Confirmation Date</span>
                        <p class="font-bold text-slate-900 mt-0.5">{{ $employee->confirmation_date ? $employee->confirmation_date->format('M d, Y') : 'Probationary' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500 uppercase font-medium">Notice Period</span>
                        <p class="font-bold text-slate-900 mt-0.5">{{ $employee->notice_period_days ?? 30 }} Days</p>
                    </div>
                </div>
            </div>

            <!-- Resilient Subsystem Summaries (Documents, Learning, Payroll) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Documents Card -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
                        <span>DOCUMENTS</span>
                        <i class="fa-solid fa-folder-open text-sky-500"></i>
                    </div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">
                        {{ $summary['sections']['documents']['data']['total_documents'] ?? 0 }}
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $summary['sections']['documents']['data']['verified_documents'] ?? 0 }} verified records
                    </p>
                </div>

                <!-- Learning & Skills Card -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
                        <span>LEARNING</span>
                        <i class="fa-solid fa-graduation-cap text-indigo-500"></i>
                    </div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">
                        {{ $summary['sections']['learning']['data']['certifications_count'] ?? 0 }}
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Certifications recorded</p>
                </div>

                <!-- Compensation / Payroll Card -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
                        <span>COMPENSATION</span>
                        <i class="fa-solid fa-vault text-emerald-500"></i>
                    </div>
                    @if(($summary['sections']['payroll']['status'] ?? '') === 'available')
                        <div class="mt-2 text-sm font-bold text-slate-900">
                            {{ $summary['sections']['payroll']['data']['salary_structure'] }}
                        </div>
                        <p class="text-xs text-emerald-600 mt-1">Authorized HR View</p>
                    @else
                        <div class="mt-2 text-xs font-semibold text-slate-400">
                            Restricted
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Field-level access policy</p>
                    @endif
                </div>
            </div>

            <!-- Unified Timeline Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center space-x-2">
                    <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i>
                    <span>Employee Career Timeline</span>
                </h2>
                <div class="divide-y divide-slate-100">
                    @forelse($summary['sections']['timeline']['data'] ?? [] as $evt)
                        <div class="py-3 flex items-start space-x-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 border border-indigo-200 flex items-center justify-center flex-shrink-0 text-indigo-600 text-xs">
                                <i class="fa-solid {{ $evt['icon'] ?? 'fa-circle' }}"></i>
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-bold text-slate-900">{{ $evt['title'] }}</span>
                                    <span class="text-[11px] text-slate-400 font-mono">{{ $evt['date'] }}</span>
                                </div>
                                <p class="text-xs text-slate-600 mt-0.5">{{ $evt['description'] }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-slate-400 text-xs py-6">
                            No milestone events recorded on this employee timeline.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Column: Contact & Team View -->
        <div class="space-y-6">

            <!-- Contact Information -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Contact Details</h2>
                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-500 uppercase font-medium">Work Email</span>
                        <p class="font-bold text-indigo-600 mt-0.5 truncate">{{ $employee->official_email ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-500 uppercase font-medium">Office Extension</span>
                        <p class="font-bold text-slate-900 mt-0.5">{{ $employee->office_phone ?? 'N/A' }}</p>
                    </div>
                    @if(!empty($summary['header']['personal_email']))
                        <div>
                            <span class="text-slate-500 uppercase font-medium">Personal Email</span>
                            <p class="font-semibold text-slate-900 mt-0.5 truncate">{{ $summary['header']['personal_email'] }}</p>
                        </div>
                    @endif
                    <div>
                        <span class="text-slate-500 uppercase font-medium">Work Location</span>
                        <p class="font-semibold text-slate-900 mt-0.5">{{ $employee->workLocation?->name ?? $employee->city ?? 'Headquarters' }}</p>
                    </div>
                </div>
            </div>

            <!-- Direct Reports / Team Members -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center justify-between">
                    <span>Direct Reports</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                        {{ $employee->directReports()->count() }}
                    </span>
                </h2>
                <div class="divide-y divide-slate-100">
                    @forelse($employee->directReports as $report)
                        <div class="py-2.5 flex items-center justify-between">
                            <div>
                                <a href="{{ route('employee_profile.profile', $report->id) }}" class="text-xs font-bold text-slate-900 hover:text-indigo-600">
                                    {{ $report->fullName() }}
                                </a>
                                <span class="block text-[11px] text-slate-500">{{ $report->designation?->name ?? 'Staff' }}</span>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700">
                                {{ $report->employment_status }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-slate-400 text-xs py-4">
                            No direct reports assigned.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
