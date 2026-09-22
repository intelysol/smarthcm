@extends('public.layouts.marketing')

@section('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "SoftwareApplication",
    "name": "SmartHCM",
    "applicationCategory": "BusinessApplication",
    "operatingSystem": "Web, iOS, Android",
    "description": "Enterprise Human Capital Management platform managing core HR, attendance tracking, shift scheduling, payroll compliance, and workforce intelligence.",
    "url": "{{ url('/') }}",
    "brand": {
        "@@type": "Brand",
        "name": "SmartHCM"
    },
    "offers": {
        "@@type": "AggregateOffer",
        "priceCurrency": "USD",
        "lowPrice": "0",
        "offerCount": "3"
    },
    "featureList": [
        "Core Human Resources and Employee Dossiers",
        "Location-Aware Mobile GPS Attendance and Geofencing",
        "Shift Rostering and Workforce Scheduling",
        "Multi-Jurisdiction Gross-to-Net Payroll Operations",
        "Absence, Leave Accruals and Approvals",
        "Applicant Tracking System and Digital Onboarding",
        "Continuous Performance Management and OKRs",
        "Workforce and People Analytics"
    ]
}
</script>
@endsection

@section('content')

<!-- 1. Hero Section -->
<section class="relative bg-gradient-to-b from-[#1E3A5F] via-[#1E3A5F] to-[#142A44] text-white pt-16 pb-24 px-4 sm:px-6 lg:px-8 overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Hero Copy -->
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold bg-[#C9A227]/20 text-[#F4E7B2] border border-[#C9A227]/40 shadow-sm">
                    <i class="fa-solid fa-layer-group mr-2 text-[#C9A227]"></i> Enterprise Human Capital Management
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight text-white">
                    Modern Human Capital Management for the Complete Employee Lifecycle
                </h1>

                <p class="text-base sm:text-lg text-slate-200 leading-relaxed max-w-2xl">
                    SmartHCM provides an integrated software platform for enterprises and multi-subsidiary organizations. Manage core employee records, location-aware mobile GPS attendance, operational workforce scheduling, multi-country payroll compliance, and workforce intelligence from a single secure cloud environment.
                </p>

                <!-- Concrete Action CTAs -->
                <div class="pt-4 flex flex-col sm:flex-row items-stretch sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                    <a href="{{ url('/demo') }}" class="px-7 py-3.5 rounded-xl bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F] font-bold text-sm text-center shadow-lg transition transform hover:-translate-y-0.5 flex items-center justify-center">
                        <span>Request a Demo</span>
                        <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                    </a>
                    <a href="{{ url('/platform') }}" class="px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-sm text-center transition flex items-center justify-center">
                        <span>Explore Platform Architecture</span>
                    </a>
                </div>

                <!-- Verified Security & Trust Indicators -->
                <div class="pt-6 border-t border-slate-700/60 grid grid-cols-3 gap-4 text-xs text-slate-300">
                    <div>
                        <div class="font-bold text-white flex items-center"><i class="fa-solid fa-shield text-[#C9A227] mr-1.5"></i> Multi-Tenant</div>
                        <div class="text-[11px] text-slate-400">Strict Data Isolation</div>
                    </div>
                    <div>
                        <div class="font-bold text-white flex items-center"><i class="fa-solid fa-lock text-[#C9A227] mr-1.5"></i> AES-256</div>
                        <div class="text-[11px] text-slate-400">Encrypted at Rest</div>
                    </div>
                    <div>
                        <div class="font-bold text-white flex items-center"><i class="fa-solid fa-mobile-button text-[#C9A227] mr-1.5"></i> Privacy First</div>
                        <div class="text-[11px] text-slate-400">No Continuous Tracking</div>
                    </div>
                </div>
            </div>

            <!-- Hero Product Visualization Card -->
            <div class="lg:col-span-5">
                <div class="rounded-2xl bg-white/5 border border-white/15 p-6 backdrop-blur-sm shadow-2xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-white/10">
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 rounded-full bg-red-400"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                            <span class="text-xs text-slate-400 font-mono ml-2">smarthcm.app/dashboard</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">System Ready</span>
                    </div>

                    <!-- Visual Workspace Summary Mockup -->
                    <div class="space-y-3">
                        <div class="p-3.5 rounded-xl bg-white/10 border border-white/10 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-[#C9A227] text-[#1E3A5F] flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-white">Mobile GPS Attendance</div>
                                    <div class="text-[11px] text-slate-300">Geofence Verified Check-in</div>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-[#F4E7B2]">100% In-Bounds</span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-white/10 border border-white/10 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-500 text-white flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-money-check-dollar"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-white">Payroll Cycle Execution</div>
                                    <div class="text-[11px] text-slate-300">Pre-Run Audit Passed</div>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-emerald-400">0 Discrepancies</span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-white/10 border border-white/10 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-purple-500 text-white flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-white">Workforce Roster Coverage</div>
                                    <div class="text-[11px] text-slate-300">3 Plant Shifts Assigned</div>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-white">48/48 Filled</span>
                        </div>
                    </div>

                    <!-- Role Access Selector Banner -->
                    <div class="pt-3 border-t border-white/10 text-[11px] text-slate-300 flex items-center justify-between">
                        <span>5 Persona Workspaces Included</span>
                        <a href="{{ route('login') }}" class="text-[#C9A227] font-bold hover:underline">Access Login &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Trust & Credibility Section -->
<section class="bg-white border-b border-slate-200 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <p class="text-center text-xs font-bold uppercase tracking-widest text-slate-400 mb-8">
            Engineered for Enterprise Operational Demands
        </p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-2xl sm:text-3xl font-black text-[#1E3A5F]">99.99%</div>
                <div class="text-xs text-slate-600 mt-1 font-medium">Platform Availability Target</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-2xl sm:text-3xl font-black text-[#1E3A5F]">AES-256</div>
                <div class="text-xs text-slate-600 mt-1 font-medium">Cryptographic Data Encryption</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-2xl sm:text-3xl font-black text-[#1E3A5F]">&lt; 100ms</div>
                <div class="text-xs text-slate-600 mt-1 font-medium">Core API Response Velocity</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-2xl sm:text-3xl font-black text-[#1E3A5F]">Zero</div>
                <div class="text-xs text-slate-600 mt-1 font-medium">Continuous Tracking Privacy Guard</div>
            </div>
        </div>
    </div>
</section>

<!-- 3. HCM Platform Overview -->
<section class="py-20 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-7xl mx-auto space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#1E3A5F]/10 text-[#1E3A5F] border border-[#1E3A5F]/20">
                Platform Architecture
            </span>
            <h2 class="text-3xl sm:text-4xl font-black tracking-tight text-[#1E3A5F]">
                An Integrated Human Capital Operating System
            </h2>
            <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                SmartHCM eliminates the friction of managing fragmented point solutions. By uniting Core HR records, attendance, shift scheduling, payroll, and people analytics under a single multi-tenant data model, organizations gain absolute visibility and automated compliance.
            </p>
        </div>

        <!-- Architecture Diagram / Tier Visualization -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-8 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4 hover:border-[#1E3A5F] transition">
                <div class="w-12 h-12 rounded-xl bg-[#1E3A5F]/10 text-[#1E3A5F] flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-database"></i>
                </div>
                <h3 class="text-lg font-bold text-[#1E3A5F]">1. Foundational Core HR</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Single source of truth for organizational hierarchies, positions, employee dossiers, compliance documents, and job architecture across all legal entities.
                </p>
                <ul class="text-xs space-y-2 text-slate-700 pt-2 border-t border-slate-100">
                    <li><i class="fa-solid fa-check text-[#16805C] mr-2"></i> Multi-entity parent/subsidiary modeling</li>
                    <li><i class="fa-solid fa-check text-[#16805C] mr-2"></i> Field-level audit trails and PII masking</li>
                </ul>
            </div>

            <div class="p-8 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4 hover:border-[#C9A227] transition">
                <div class="w-12 h-12 rounded-xl bg-[#C9A227]/20 text-[#B08E20] flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <h3 class="text-lg font-bold text-[#1E3A5F]">2. Operational Workforce</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Real-time labor management connecting location-aware mobile GPS attendance, physical biometric hardware, shift rosters, and leave approvals.
                </p>
                <ul class="text-xs space-y-2 text-slate-700 pt-2 border-t border-slate-100">
                    <li><i class="fa-solid fa-check text-[#16805C] mr-2"></i> Geofenced mobile check-in with offline sync</li>
                    <li><i class="fa-solid fa-check text-[#16805C] mr-2"></i> Automated timesheet generation for payroll</li>
                </ul>
            </div>

            <div class="p-8 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4 hover:border-[#1E3A5F] transition">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <h3 class="text-lg font-bold text-[#1E3A5F]">3. Economics &amp; Intelligence</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Deterministic gross-to-net payroll execution, statutory tax compliance, benefits administration, and executive people analytics.
                </p>
                <ul class="text-xs space-y-2 text-slate-700 pt-2 border-t border-slate-100">
                    <li><i class="fa-solid fa-check text-[#16805C] mr-2"></i> Automated bank file generation (ACH, SEPA)</li>
                    <li><i class="fa-solid fa-check text-[#16805C] mr-2"></i> Real-time overtime leakage and cost analytics</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- 4. Core Modules Grid -->
<section class="py-20 px-4 sm:px-6 lg:px-8 bg-white border-y border-slate-200">
    <div class="max-w-7xl mx-auto space-y-12">
        <div class="flex flex-col md:flex-row md:items-end justify-between">
            <div class="max-w-2xl space-y-3">
                <span class="text-xs font-bold uppercase tracking-wider text-[#C9A227]">Enterprise Modules</span>
                <h2 class="text-3xl font-black tracking-tight text-[#1E3A5F]">
                    Explore the SmartHCM Functional Capability Matrix
                </h2>
                <p class="text-sm text-slate-600">
                    Every module is engineered for enterprise high availability, verifiable audit compliance, and multi-tenant security.
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ url('/platform') }}" class="text-xs font-bold text-[#1E3A5F] hover:text-[#C9A227] flex items-center">
                    <span>View all module roadmap statuses</span>
                    <i class="fa-solid fa-arrow-right ml-1.5 text-xs"></i>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach(array_slice($modules, 0, 9) as $mod)
                <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 hover:shadow-md transition flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-[#1E3A5F] flex items-center justify-center text-lg shadow-sm">
                                <i class="{{ $mod['icon'] }}"></i>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $mod['status'] === 'Available' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $mod['status'] }}
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-[#1E3A5F]">{{ $mod['name'] }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $mod['summary'] }}</p>
                    </div>
                    <div class="pt-4 mt-4 border-t border-slate-200 flex items-center justify-between">
                        <a href="{{ url('/' . $mod['slug']) }}" class="text-xs font-bold text-[#1E3A5F] hover:text-[#C9A227] flex items-center">
                            <span>Explore Capability</span>
                            <i class="fa-solid fa-chevron-right ml-1.5 text-[10px]"></i>
                        </a>
                        <span class="text-[10px] font-medium text-slate-400">{{ $mod['category'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 5. Featured Highlight: Mobile GPS Attendance -->
<section class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-[#1E3A5F] to-[#142A44] text-white">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#C9A227]/20 text-[#F4E7B2] border border-[#C9A227]/30">
                    <i class="fa-solid fa-location-crosshairs mr-1.5 text-[#C9A227]"></i> Specialized Workforce Capability
                </div>

                <h2 class="text-3xl sm:text-4xl font-black tracking-tight text-white leading-tight">
                    Location-Aware Mobile GPS Attendance for Distributed Workforces
                </h2>

                <p class="text-sm sm:text-base text-slate-200 leading-relaxed">
                    Designed for field technicians, multi-branch retail teams, healthcare clinics, and construction job sites. SmartHCM Mobile Attendance binds employee check-in events to verified geographic coordinates without invading worker privacy through continuous background tracking.
                </p>

                <div class="space-y-3 pt-2">
                    <div class="flex items-start space-x-3">
                        <i class="fa-solid fa-circle-check text-[#C9A227] text-sm mt-0.5"></i>
                        <span class="text-xs text-slate-200"><strong>Configurable Geofences:</strong> Circular and polygon boundary perimeters with adjustable meters radius.</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fa-solid fa-circle-check text-[#C9A227] text-sm mt-0.5"></i>
                        <span class="text-xs text-slate-200"><strong>Anti-Spoofing Security:</strong> Hardware-level detection flags mock locations and rooted device manipulation.</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fa-solid fa-circle-check text-[#C9A227] text-sm mt-0.5"></i>
                        <span class="text-xs text-slate-200"><strong>Offline Attendance Caching:</strong> Cryptographic timestamp signatures record punches when offline and sync upon reconnection.</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fa-solid fa-circle-check text-[#C9A227] text-sm mt-0.5"></i>
                        <span class="text-xs text-slate-200"><strong>Zero Continuous Tracking:</strong> Strictly captures coordinates at the single instant of check-in and check-out.</span>
                    </div>
                </div>

                <div class="pt-4 flex items-center space-x-4">
                    <a href="{{ url('/mobile-attendance') }}" class="px-6 py-3 rounded-xl bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F] font-bold text-xs shadow-md transition">
                        Explore Mobile Attendance
                    </a>
                    <a href="{{ url('/demo') }}" class="px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-xs transition">
                        Schedule Live Demo
                    </a>
                </div>
            </div>

            <div class="lg:col-span-6">
                <!-- Mobile Mockup Graphic -->
                <div class="max-w-md mx-auto rounded-3xl bg-slate-900 border-4 border-slate-700 p-6 shadow-2xl space-y-6 text-slate-100">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3 text-xs">
                        <span class="font-bold text-[#C9A227]"><i class="fa-solid fa-mobile mr-1"></i> SmartHCM Mobile</span>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono">GPS LOCK: ±4m</span>
                    </div>

                    <div class="text-center space-y-1">
                        <div class="text-xs text-slate-400">Current Assigned Worksite</div>
                        <div class="text-lg font-bold text-white">Central Operations Hub &bull; Site #104</div>
                        <div class="text-xs text-emerald-400 font-semibold"><i class="fa-solid fa-circle-check mr-1"></i> Inside Geofence (38m from center)</div>
                    </div>

                    <!-- Visual Radar Map Simulation -->
                    <div class="h-44 rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center justify-center relative overflow-hidden">
                        <div class="w-32 h-32 rounded-full border border-dashed border-[#C9A227]/40 animate-ping absolute"></div>
                        <div class="w-24 h-24 rounded-full bg-[#1E3A5F]/50 border border-[#C9A227] flex items-center justify-center">
                            <i class="fa-solid fa-location-dot text-[#C9A227] text-2xl"></i>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-800 border border-slate-700 text-xs flex items-center justify-between">
                        <div>
                            <span class="text-slate-400 block text-[10px]">ACTION</span>
                            <span class="font-bold text-white">Morning Shift Check-In</span>
                        </div>
                        <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-bold text-xs shadow hover:bg-emerald-500">
                            Confirm Punch
                        </button>
                    </div>

                    <p class="text-[10px] text-center text-slate-400">
                        Event verified. Coordinates and optional photo recorded for HR review.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. Industry Solutions Grid -->
<section class="py-20 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-7xl mx-auto space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-[#C9A227]">Vertical Specialization</span>
            <h2 class="text-3xl font-black tracking-tight text-[#1E3A5F]">
                Engineered for High-Compliance Industry Workforces
            </h2>
            <p class="text-sm text-slate-600">
                Tailored workflows addressing the unique operational constraints of 24/7 plants, hospital shifts, retail chains, and construction sites.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach(array_slice($industries, 0, 8) as $ind)
                <a href="{{ url('/industries/' . $ind['slug']) }}" class="group p-6 rounded-2xl bg-white border border-slate-200 hover:border-[#1E3A5F] hover:shadow-md transition flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-[#1E3A5F]/10 text-[#1E3A5F] group-hover:bg-[#1E3A5F] group-hover:text-[#C9A227] transition flex items-center justify-center text-lg">
                            <i class="{{ $ind['icon'] }}"></i>
                        </div>
                        <h3 class="text-base font-bold text-[#1E3A5F] group-hover:text-[#C9A227] transition">{{ $ind['name'] }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $ind['tagline'] }}</p>
                    </div>
                    <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-[#1E3A5F]">
                        <span>View Industry Model</span>
                        <i class="fa-solid fa-arrow-right text-xs transition group-hover:translate-x-1"></i>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

<!-- 7. Security, Privacy & Multi-Tenancy Architecture -->
<section class="py-20 px-4 sm:px-6 lg:px-8 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-5 space-y-6">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#16805C]/10 text-[#16805C] border border-[#16805C]/30">
                    <i class="fa-solid fa-shield-halved mr-1.5"></i> Enterprise Trust &amp; Governance
                </span>
                <h2 class="text-3xl font-black tracking-tight text-[#1E3A5F]">
                    Zero-Trust Architecture &amp; Complete Tenant Isolation
                </h2>
                <p class="text-sm text-slate-600 leading-relaxed">
                    SmartHCM is built for organizations where data integrity and privacy are non-negotiable. Every request is verified, scoped, and audited.
                </p>

                <div class="space-y-4 text-xs">
                    <div class="p-4 rounded-xl bg-[#F7F9FC] border border-slate-200">
                        <div class="font-bold text-[#1E3A5F] mb-1"><i class="fa-solid fa-database text-[#16805C] mr-2"></i> Tenant Scoping Guard</div>
                        <p class="text-slate-600">Database queries automatically inject tenant constraints at the model layer, ensuring zero data co-mingling.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-[#F7F9FC] border border-slate-200">
                        <div class="font-bold text-[#1E3A5F] mb-1"><i class="fa-solid fa-key text-[#16805C] mr-2"></i> Granular Field-Level RBAC</div>
                        <p class="text-slate-600">Sensitive attributes (salary, tax ID, medical notes) are masked based on specific role permissions.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-[#F7F9FC] border border-slate-200">
                        <div class="font-bold text-[#1E3A5F] mb-1"><i class="fa-solid fa-clock-rotate-left text-[#16805C] mr-2"></i> Immutable Audit Logs</div>
                        <p class="text-slate-600">All data mutations, logins, and exports are recorded with actor IDs, IP timestamps, and before/after state.</p>
                    </div>
                </div>

                <a href="{{ url('/security') }}" class="inline-flex items-center text-xs font-bold text-[#1E3A5F] hover:text-[#C9A227]">
                    <span>Read complete enterprise security specification</span>
                    <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>

            <div class="lg:col-span-7">
                <div class="rounded-2xl bg-slate-900 text-slate-200 p-6 sm:p-8 font-mono text-xs shadow-xl space-y-4 border border-slate-800">
                    <div class="flex items-center justify-between text-slate-400 pb-3 border-b border-slate-800 text-[11px]">
                        <span>SECURITY CONTRACT VERIFICATION</span>
                        <span class="text-emerald-400 font-bold">100% PASS</span>
                    </div>
                    <div class="space-y-2 text-[11px]">
                        <div class="text-slate-400"># Verification of Database Global Scopes</div>
                        <div class="text-emerald-300">SELECT * FROM `employees` WHERE `tenant_id` = '9a7e8b2c-...' [ENFORCED]</div>
                        <div class="text-slate-400 pt-2"># Encryption at Rest Standard</div>
                        <div class="text-emerald-300">CIPHER: AES-256-GCM | KEY_ROTATION: Automated 90-day</div>
                        <div class="text-slate-400 pt-2"># API Security &amp; Zero Trust Context</div>
                        <div class="text-emerald-300">HEADER: Authorization: Bearer &lt;scoped_token&gt; | TLS: 1.3 Strict</div>
                        <div class="text-slate-400 pt-2"># Event-Driven Audit Pipeline</div>
                        <div class="text-emerald-300">EVENT: EmployeeSalaryUpdated | ACTOR: user_hr_lead | AUDIT_SAVED: true</div>
                    </div>
                    <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-[10px] text-slate-400">
                        <span>Zero Trust Certification Verified</span>
                        <span class="text-[#C9A227]">Production Compliant</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 8. Frequently Asked Questions (Structured for AIO & GEO) -->
<section class="py-20 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-4xl mx-auto space-y-10">
        <div class="text-center space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-[#C9A227]">Factual Product Clarifications</span>
            <h2 class="text-3xl font-black tracking-tight text-[#1E3A5F]">Frequently Asked Questions</h2>
            <p class="text-sm text-slate-600">Authoritative answers regarding platform architecture, mobile attendance, and data separation.</p>
        </div>

        <div class="space-y-4">
            @foreach($faqs['general']['questions'] as $faq)
                <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-sm space-y-2">
                    <h3 class="text-sm font-bold text-[#1E3A5F] flex items-center">
                        <i class="fa-solid fa-circle-question text-[#C9A227] mr-2.5"></i>
                        {{ $faq['q'] }}
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed pl-6">{{ $faq['a'] }}</p>
                </div>
            @endforeach

            @foreach($faqs['attendance']['questions'] as $faq)
                <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-sm space-y-2">
                    <h3 class="text-sm font-bold text-[#1E3A5F] flex items-center">
                        <i class="fa-solid fa-circle-question text-[#C9A227] mr-2.5"></i>
                        {{ $faq['q'] }}
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed pl-6">{{ $faq['a'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="text-center pt-4">
            <a href="{{ url('/faq') }}" class="text-xs font-bold text-[#1E3A5F] hover:text-[#C9A227] inline-flex items-center">
                <span>View all frequently asked questions across HCM categories</span>
                <i class="fa-solid fa-arrow-right ml-1.5 text-xs"></i>
            </a>
        </div>
    </div>
</section>

<!-- 9. Final Enterprise Call to Action -->
<section class="bg-gradient-to-b from-[#142A44] to-[#1E3A5F] text-white py-20 px-4 sm:px-6 lg:px-8 text-center">
    <div class="max-w-3xl mx-auto space-y-6">
        <h2 class="text-3xl sm:text-4xl font-black tracking-tight text-white">
            Ready to Modernize Your Enterprise Human Capital Operations?
        </h2>
        <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
            Schedule a confidential consultation and walkthrough with our enterprise HCM solutions architects. See real-time mobile GPS attendance, payroll processing, and workforce intelligence in action.
        </p>
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="{{ url('/demo') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F] font-bold text-sm shadow-xl transition transform hover:-translate-y-0.5">
                Request an Enterprise Demo
            </a>
            <a href="{{ url('/contact') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-sm transition">
                Contact HCM Specialist
            </a>
        </div>
        <div class="pt-6 text-xs text-slate-400">
            Already an authorized user? <a href="{{ route('login') }}" class="text-[#F4E7B2] underline font-semibold">Sign in to your workspace</a>
        </div>
    </div>
</section>

@endsection
