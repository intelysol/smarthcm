@extends('public.layouts.marketing')

@section('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "SoftwareApplication",
    "name": "SmartHCM Mobile GPS Attendance System",
    "applicationCategory": "BusinessApplication",
    "operatingSystem": "iOS, Android, Web",
    "description": "Location-aware mobile employee attendance app with geofencing, work-location binding, offline punch caching, and photo verification for controlled check-in.",
    "url": "{{ url('/mobile-attendance') }}",
    "offers": {
        "@@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
    }
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "{{ url('/') }}"
        },
        {
            "@@type": "ListItem",
            "position": 2,
            "name": "Mobile GPS Attendance",
            "item": "{{ url('/mobile-attendance') }}"
        }
    ]
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        @foreach($feature['faqs'] as $index => $faq)
        {
            "@@type": "Question",
            "name": "{{ addslashes($faq['q']) }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ addslashes($faq['a']) }}"
            }
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endsection

@section('content')

<!-- Mobile Attendance Hero Banner -->
<section class="bg-[#1E3A5F] text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <div class="lg:col-span-7 space-y-6">
                <nav class="text-xs text-slate-300 flex items-center space-x-2" aria-label="Breadcrumb">
                    <a href="{{ url('/') }}" class="hover:text-white">Home</a>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                    <a href="{{ url('/platform') }}" class="hover:text-white">Platform</a>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                    <span class="text-[#F4E7B2] font-semibold">Mobile GPS Attendance</span>
                </nav>

                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#C9A227]/20 text-[#F4E7B2] border border-[#C9A227]/30">
                    <i class="fa-solid fa-location-dot mr-1.5 text-[#C9A227]"></i> Enterprise Mobile Workforce Attendance
                </div>

                <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white leading-tight">
                    Location-Aware Mobile GPS Attendance &amp; Geo-Fencing System
                </h1>

                <!-- Definition-First AIO Content -->
                <div class="p-4 rounded-xl bg-white/10 border border-white/20 text-xs sm:text-sm text-slate-100 leading-relaxed font-medium">
                    {{ $feature['definition'] }}
                </div>

                <p class="text-xs sm:text-sm text-slate-200 leading-relaxed">
                    SmartHCM Mobile Attendance eliminates the operational vulnerabilities of unverified remote hours and hardware clock installation costs. Staff clock in securely from client worksites, field routes, and construction yards while strict privacy boundaries protect workers by enforcing zero background tracking.
                </p>

                <div class="pt-4 flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4">
                    <a href="{{ url('/demo') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F] font-bold text-xs shadow-lg text-center transition">
                        Schedule Mobile Attendance Demo
                    </a>
                    <a href="{{ url('/attendance') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-xs text-center transition">
                        View Complete Attendance Module
                    </a>
                </div>
            </div>

            <!-- Visual Mobile Display -->
            <div class="lg:col-span-5">
                <div class="max-w-sm mx-auto rounded-3xl bg-slate-900 border-4 border-slate-700 p-6 shadow-2xl text-slate-100 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3 text-xs">
                        <span class="font-bold text-[#C9A227]"><i class="fa-solid fa-mobile-screen-button mr-1"></i> Mobile Geofence</span>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono">STATUS: VERIFIED</span>
                    </div>

                    <div class="space-y-2">
                        <div class="text-[11px] text-slate-400">ASSIGNED WORK LOCATION</div>
                        <div class="text-sm font-bold text-white">Austin Metro Regional Logistics Hub #4</div>
                        <div class="text-xs text-slate-300 font-mono">Lat: 30.2672° N &bull; Long: 97.7431° W</div>
                    </div>

                    <!-- Geofence Visualization -->
                    <div class="h-36 rounded-xl bg-slate-800 border border-slate-700 p-3 flex flex-col items-center justify-center relative">
                        <div class="w-24 h-24 rounded-full border-2 border-emerald-400/50 bg-emerald-500/10 flex items-center justify-center">
                            <div class="w-8 h-8 rounded-full bg-emerald-500 text-slate-900 flex items-center justify-center font-bold text-xs shadow">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                        </div>
                        <span class="text-[10px] text-emerald-300 font-bold mt-2">Geofence Radius: 100m (Worker Inside)</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-800 text-xs flex items-center justify-between">
                        <div>
                            <span class="text-slate-400 text-[10px] block">PHOTO VERIFICATION</span>
                            <span class="font-semibold text-white">Front Camera Capture Enabled</span>
                        </div>
                        <i class="fa-solid fa-camera text-[#C9A227]"></i>
                    </div>

                    <div class="text-[10px] text-center text-slate-400 pt-2 border-t border-slate-800">
                        Zero continuous location tracking. Data read only at punch event.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Pillars of the Mobile Attendance Architecture -->
<section class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
    <div class="max-w-7xl mx-auto space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-[#C9A227]">Architectural Specifications</span>
            <h2 class="text-3xl font-black text-[#1E3A5F]">
                Engineered for Verification, Compliance, and Trust
            </h2>
            <p class="text-xs sm:text-sm text-slate-600">
                Explore how SmartHCM enforces location integrity without sacrificing employee battery life, network resiliency, or personal privacy.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-draw-polygon"></i>
                </div>
                <h3 class="text-base font-bold text-[#1E3A5F]">Configurable Geo-Fencing</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Set circular or custom polygon virtual boundaries around client offices, construction sites, and remote stores with tailored meter tolerances.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-link"></i>
                </div>
                <h3 class="text-base font-bold text-[#1E3A5F]">Work-Location Binding</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Prevent workers from logging in outside their designated job location or branch assignment. Unscheduled off-site attempts are flagged instantly.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-camera"></i>
                </div>
                <h3 class="text-base font-bold text-[#1E3A5F]">Selfie Photo Verification</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Optional front-camera photo capture during check-in confirms physical user presence and completely eliminates peer buddy-punching.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <h3 class="text-base font-bold text-[#1E3A5F]">Offline Mode &amp; Sync</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Employees working in underground garages, basements, or remote field plots punch offline with cryptographic timestamps that sync when connected.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-shield-virus"></i>
                </div>
                <h3 class="text-base font-bold text-[#1E3A5F]">Anti-Spoofing Detection</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Native hardware checks detect rooted devices, fake GPS mock coordinates, and developer manipulation, rejecting fraudulent punch attempts.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <h3 class="text-base font-bold text-[#1E3A5F]">Zero Continuous Tracking</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    SmartHCM never tracks employee movement during the workday. Location coordinates are queried exclusively at the instant the check-in button is pressed.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <h3 class="text-base font-bold text-[#1E3A5F]">Manager &amp; HR Review</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Out-of-boundary punches, late regularizations, and supervisor override approvals flow through structured, audited managerial workbenches.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <h3 class="text-base font-bold text-[#1E3A5F]">Direct Payroll Ingestion</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Approved shift hours, verified overtime, and night differentials flow automatically into the gross-to-net payroll engine with zero manual export.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Operational Workflow: Step-by-Step Breakdown -->
<section class="py-20 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC] border-y border-slate-200">
    <div class="max-w-5xl mx-auto space-y-12">
        <div class="text-center space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-[#C9A227]">Operational Workflow</span>
            <h2 class="text-3xl font-black text-[#1E3A5F]">How Mobile GPS Attendance Operates</h2>
            <p class="text-xs sm:text-sm text-slate-600">A structured walkthrough of location capture, validation, and payroll synchronization.</p>
        </div>

        <div class="space-y-6">
            @foreach($feature['workflow'] as $step)
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-start space-x-4">
                    <div class="w-8 h-8 rounded-full bg-[#1E3A5F] text-[#C9A227] font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                        {{ $loop->iteration }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-[#1E3A5F]">{{ $step }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Frequently Asked Questions (Grounded & Citable for GEO/AIO) -->
<section class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
    <div class="max-w-4xl mx-auto space-y-10">
        <div class="text-center space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-[#C9A227]">Factual Product FAQ</span>
            <h2 class="text-3xl font-black text-[#1E3A5F]">Mobile GPS Attendance Questions</h2>
            <p class="text-xs sm:text-sm text-slate-600">Authoritative facts on data privacy, spoofing defenses, and offline capabilities.</p>
        </div>

        <div class="space-y-4">
            @foreach($feature['faqs'] as $faq)
                <div class="p-6 rounded-xl bg-[#F7F9FC] border border-slate-200 space-y-2">
                    <h3 class="text-sm font-bold text-[#1E3A5F] flex items-center">
                        <i class="fa-solid fa-circle-question text-[#C9A227] mr-2.5"></i>
                        {{ $faq['q'] }}
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed pl-6">{{ $faq['a'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-[#142A44] to-[#1E3A5F] text-white text-center">
    <div class="max-w-3xl mx-auto space-y-6">
        <h2 class="text-2xl sm:text-3xl font-black">Upgrade to Controlled Mobile GPS Attendance</h2>
        <p class="text-xs sm:text-sm text-slate-300">
            Request an interactive demonstration to see how geofencing, photo capture, and anti-spoofing work on real devices.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="{{ url('/demo') }}" class="px-8 py-3.5 rounded-xl bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F] font-bold text-xs shadow transition">
                Request Mobile Attendance Demo
            </a>
            <a href="{{ url('/contact') }}" class="px-8 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-xs transition">
                Talk to an Attendance Specialist
            </a>
        </div>
    </div>
</section>

@endsection
