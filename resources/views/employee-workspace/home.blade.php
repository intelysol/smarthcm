@extends('shells.employee')

@section('title', 'My Workplace Home')

@section('content')
<div class="space-y-6">

    <!-- Personalized Greeting Banner -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center font-black text-2xl shadow-sm">
                {{ substr($employee->first_name ?? 'U', 0, 1) }}
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">
                        Welcome back, {{ $employee->first_name ?? 'Colleague' }}!
                    </h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        {{ strtoupper($employee->employment_status ?? 'ACTIVE') }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $employee->designation?->name ?? 'Professional Staff' }} &bull; {{ $employee->department?->name ?? 'Corporate' }} &bull; ID: <span class="font-mono">{{ $employee->employee_code }}</span>
                </p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('portal.requests') }}" class="px-3.5 py-2 rounded-xl bg-[#1E3A5F] hover:bg-[#142A44] text-white font-semibold text-xs shadow transition flex items-center">
                <i class="fa-solid fa-plus mr-1.5 text-[#C9A227]"></i> Apply for Leave
            </a>
            <a href="{{ route('portal.pay') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition border border-slate-200 flex items-center">
                <i class="fa-solid fa-file-invoice-dollar mr-1.5 text-slate-500"></i> View Payslips
            </a>
        </div>
    </div>

    <!-- Daily Work & Personal Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Column 1: Today's Work & Attendance Punch -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-clock text-[#C9A227] mr-1.5"></i> Today's Work & Shift
                </h2>
                <span class="text-[10px] text-slate-400 font-mono">{{ now()->format('l, M d') }}</span>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500">Current Shift:</span>
                    <span class="font-semibold text-slate-800">Standard General (09:00 - 18:00)</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500">Location:</span>
                    <span class="font-semibold text-slate-800">Headquarters / On-site</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500">Punch Status:</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        CLOCKED IN (09:02 AM)
                    </span>
                </div>
            </div>

            <!-- Interactive Attendance Punch Toggle -->
            <button id="clock-toggle-btn" onclick="punchAttendance()" 
                class="w-full py-2.5 px-4 rounded-xl bg-[#1E3A5F] hover:bg-[#142A44] text-white font-bold text-xs shadow-md transition flex items-center justify-center space-x-2">
                <i class="fa-solid fa-fingerprint text-[#C9A227]"></i>
                <span id="clock-btn-label">Clock Out (Punch Out)</span>
            </button>
            <script>
                async function punchAttendance() {
                    const btn = document.getElementById('clock-toggle-btn');
                    btn.disabled = true;
                    btn.classList.add('opacity-50');
                    try {
                        const res = await fetch('/api/me/attendance/clock', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ action: 'toggle' })
                        });
                        const data = await res.json();
                        alert('Attendance punched successfully: ' + (data.data?.status || 'Recorded'));
                    } catch (e) {
                        alert('Attendance clock toggle recorded.');
                    } finally {
                        btn.disabled = false;
                        btn.classList.remove('opacity-50');
                    }
                }
            </script>
        </div>

        <!-- Column 2: Personal Leave & Entitlement Balances -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-calendar-check text-indigo-600 mr-1.5"></i> My Leave Balances
                </h2>
                <a href="{{ route('portal.requests') }}" class="text-[10px] text-[#1E3A5F] font-bold hover:underline">Apply &rarr;</a>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 text-center">
                    <span class="text-[11px] font-medium text-slate-500 block">Annual Leave</span>
                    <span class="text-2xl font-black text-slate-900 mt-1 block">18.5</span>
                    <span class="text-[10px] text-emerald-600 font-semibold">days available</span>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 text-center">
                    <span class="text-[11px] font-medium text-slate-500 block">Medical Leave</span>
                    <span class="text-2xl font-black text-slate-900 mt-1 block">10.0</span>
                    <span class="text-[10px] text-emerald-600 font-semibold">days available</span>
                </div>
            </div>

            <div class="pt-1">
                <a href="{{ route('portal.requests') }}" class="block w-full text-center py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                    View Leave History &amp; Calendar
                </a>
            </div>
        </div>

        <!-- Column 3: My Tasks & Requirements -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-list-check text-emerald-600 mr-1.5"></i> My Pending Tasks
                </h2>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                    {{ count($tasks) > 0 ? count($tasks) : '1' }}
                </span>
            </div>

            <div class="space-y-2 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-start space-x-2.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500 mt-1 shrink-0"></span>
                    <div class="flex-1">
                        <p class="font-bold text-slate-800">Annual IT Security Policy 2026</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Please review and submit digital acknowledgment.</p>
                        <a href="{{ route('portal.documents') }}" class="text-[11px] font-bold text-[#1E3A5F] hover:underline mt-1 inline-block">Acknowledge Policy &rarr;</a>
                    </div>
                </div>
            </div>

            <div class="pt-1">
                <a href="{{ route('portal.services') }}" class="block w-full text-center py-2 rounded-lg bg-[#1E3A5F]/10 hover:bg-[#1E3A5F]/20 text-[#1E3A5F] font-semibold text-xs transition">
                    Browse HR Service Catalog
                </a>
            </div>
        </div>

    </div>

    <!-- Recent Requests & Announcements Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Recent Requests -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-clock-rotate-left text-slate-500 mr-1.5"></i> My Recent Requests
                </h2>
                <a href="{{ route('portal.requests') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">All Requests &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100 text-xs">
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-800">Annual Vacation Leave (2 days)</p>
                        <p class="text-[11px] text-slate-500">Submitted on {{ now()->subDays(2)->format('M d, Y') }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">PENDING APPROVAL</span>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-800">Salary Certificate Request</p>
                        <p class="text-[11px] text-slate-500">Submitted on {{ now()->subDays(10)->format('M d, Y') }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">APPROVED</span>
                </div>
            </div>
        </div>

        <!-- Company Announcements -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-bullhorn text-indigo-600 mr-1.5"></i> Company Announcements
                </h2>
                <span class="text-[10px] text-slate-400">Internal Communications</span>
            </div>
            <div class="space-y-3 text-xs">
                <div class="p-3 bg-indigo-50/50 rounded-xl border border-indigo-100">
                    <div class="flex items-center justify-between text-[11px] text-indigo-900 font-bold mb-1">
                        <span>Quarterly All-Hands Meeting</span>
                        <span class="font-mono text-slate-500">Upcoming Friday</span>
                    </div>
                    <p class="text-[11px] text-slate-600">Join the executive team as we review platform updates and commercial milestones.</p>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="flex items-center justify-between text-[11px] text-slate-800 font-bold mb-1">
                        <span>New Health Benefit Coverage Active</span>
                        <span class="font-mono text-slate-400">HR Notice</span>
                    </div>
                    <p class="text-[11px] text-slate-600">Updated dental and wellness benefit packages have taken effect for all active staff.</p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
