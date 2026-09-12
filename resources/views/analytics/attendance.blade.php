@extends('analytics.layout')

@section('title', 'Attendance & Leave Analytics — Flow HCM')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center">
                <i class="fa-solid fa-business-time text-emerald-600 mr-3"></i>Attendance, Punctuality & Leave Analytics
            </h1>
            <p class="text-sm text-slate-500 mt-1">Operational metrics covering scheduled shifts, absenteeism, late arrivals, overtime, and leave utilization.</p>
        </div>
    </div>

    <!-- Attendance KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Attendance Rate</span>
            <div class="mt-3 text-3xl font-extrabold text-emerald-600">{{ $metrics['attendance_rate_percent'] ?? 100 }}%</div>
            <p class="text-xs text-slate-500 mt-1">{{ $metrics['present_days'] ?? 0 }} Present / {{ $metrics['total_scheduled_days'] ?? 0 }} Total Days</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Absenteeism Rate</span>
            <div class="mt-3 text-3xl font-extrabold text-rose-600">{{ $metrics['absenteeism_rate_percent'] ?? 0 }}%</div>
            <p class="text-xs text-slate-500 mt-1">{{ $metrics['absent_days'] ?? 0 }} Unplanned Absences</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Late Arrivals</span>
            <div class="mt-3 text-3xl font-extrabold text-amber-600">{{ $metrics['late_arrival_rate_percent'] ?? 0 }}%</div>
            <p class="text-xs text-slate-500 mt-1">{{ $metrics['late_days'] ?? 0 }} Punctuality Exceptions</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Total Overtime Logged</span>
            <div class="mt-3 text-3xl font-extrabold text-indigo-600">{{ $metrics['total_overtime_hours'] ?? 0 }} hrs</div>
            <p class="text-xs text-slate-500 mt-1">{{ $metrics['total_working_hours'] ?? 0 }} Regular Working Hours</p>
        </div>
    </div>

    <!-- Leave Utilization Section -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="font-bold text-slate-900 text-sm mb-4 flex items-center">
            <i class="fa-solid fa-plane-departure text-teal-600 mr-2"></i>Leave Utilization Trends
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 bg-slate-50 rounded-lg border border-slate-100">
                <span class="text-xs text-slate-500">Total Leave Days Taken</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ $leave['total_leave_days_taken'] ?? 0 }}</div>
            </div>
            <div class="p-4 bg-slate-50 rounded-lg border border-slate-100">
                <span class="text-xs text-slate-500">Avg Leave Per Employee</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ $leave['average_leave_per_employee_days'] ?? 0 }} days</div>
            </div>
            <div class="p-4 bg-slate-50 rounded-lg border border-slate-100">
                <span class="text-xs text-slate-500">Entitlement Utilization</span>
                <div class="text-2xl font-bold text-teal-600 mt-1">{{ $leave['leave_utilization_rate_percent'] ?? 0 }}%</div>
            </div>
        </div>
    </div>
</div>
@endsection
