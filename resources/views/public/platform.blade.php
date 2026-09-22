@extends('public.layouts.marketing')

@section('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "SoftwareApplication",
    "name": "SmartHCM Platform",
    "applicationCategory": "EnterpriseApplication",
    "description": "Unified Human Capital Management platform architecture connecting Core HR, Workforce Management, Payroll, and Responsible AI.",
    "url": "{{ url('/platform') }}",
    "operatingSystem": "Web, iOS, Android"
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
            "name": "Platform",
            "item": "{{ url('/platform') }}"
        }
    ]
}
</script>
@endsection

@section('content')

<!-- Header Banner -->
<section class="bg-[#1E3A5F] text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-7xl mx-auto space-y-4">
        <nav class="text-xs text-slate-300 flex items-center space-x-2" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-white">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-[#F4E7B2] font-semibold">Platform Architecture</span>
        </nav>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            SmartHCM Enterprise Platform Architecture
        </h1>
        <p class="text-sm sm:text-base text-slate-200 max-w-3xl leading-relaxed">
            SmartHCM is an integrated, multi-tenant Human Capital Management platform. It delivers a unified domain architecture uniting Core HR records, real-time workforce operations, multi-country payroll compliance, and people intelligence under strict tenant isolation.
        </p>
    </div>
</section>

<!-- Architectural Principles & Lifecycle Status Legend -->
<section class="py-8 px-4 sm:px-6 lg:px-8 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
        <div class="flex items-center space-x-2 text-slate-600">
            <span class="font-bold text-[#1E3A5F]">Capability Lifecycle Legend:</span>
            <span>We maintain transparent product documentation. All capabilities below are explicitly categorized:</span>
        </div>
        <div class="flex items-center space-x-4 font-semibold">
            <span class="flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1.5"></span> Available (Production-Ready)</span>
            <span class="flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-1.5"></span> Coming Soon (Active Build)</span>
            <span class="flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-slate-400 mr-1.5"></span> Planned (Roadmap Specification)</span>
        </div>
    </div>
</section>

<!-- Modules Catalog Grouped by Category -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-7xl mx-auto space-y-16">

        @php
            $categories = [
                'Foundation' => ['title' => 'Foundational Core HR & Organizational Architecture', 'desc' => 'Centralized employee dossier, multi-company hierarchies, and position management.'],
                'Workforce Operations' => ['title' => 'Workforce Operations, Shifts & Attendance', 'desc' => 'Biometric clocks, geofenced mobile check-in, shift rostering, and leave tracking.'],
                'Workforce Economics' => ['title' => 'Workforce Economics, Payroll & Benefits', 'desc' => 'Gross-to-net calculation engines, tax compliance, benefits open enrollment, and expenses.'],
                'Talent' => ['title' => 'Talent Acquisition & Employee Development', 'desc' => 'Recruitment pipelines, paperless onboarding, OKR performance, and enterprise LMS.'],
                'Employee Experience' => ['title' => 'Employee Self-Service & Service Delivery', 'desc' => 'Mobile ESS, SLA-tracked HR ticketing, and workplace engagement surveys.'],
                'Workforce Intelligence' => ['title' => 'Workforce Planning & People Analytics', 'desc' => 'Executive dashboards, labor cost allocation, turnover modeling, and capacity forecasts.'],
                'Intelligence' => ['title' => 'Responsible Enterprise AI', 'desc' => 'Auditable, zero-retention HR intelligence with bias guardrails and determinism.'],
                'Ecosystem' => ['title' => 'Integrations & Enterprise Ecosystem', 'desc' => 'Pre-built ERP connectors, SAML 2.0 / SSO, and RESTful webhooks.'],
            ];
        @endphp

        @foreach($categories as $catKey => $catInfo)
            <div class="space-y-6">
                <div class="border-b border-slate-200 pb-3">
                    <h2 class="text-xl sm:text-2xl font-black text-[#1E3A5F]">{{ $catInfo['title'] }}</h2>
                    <p class="text-xs text-slate-600 mt-1">{{ $catInfo['desc'] }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($modules as $mod)
                        @if($mod['category'] === $catKey)
                            <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#1E3A5F] transition flex flex-col justify-between">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="w-10 h-10 rounded-xl bg-[#1E3A5F]/10 text-[#1E3A5F] flex items-center justify-center text-lg">
                                            <i class="{{ $mod['icon'] }}"></i>
                                        </div>
                                        @if($mod['status'] === 'Available')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Available
                                            </span>
                                        @elseif($mod['status'] === 'Coming Soon')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                Coming Soon
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                Planned
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-base font-bold text-[#1E3A5F]">{{ $mod['name'] }}</h3>
                                    <p class="text-xs font-semibold text-[#C9A227]">{{ $mod['tagline'] }}</p>
                                    <p class="text-xs text-slate-600 leading-relaxed">{{ $mod['summary'] }}</p>
                                </div>
                                <div class="pt-4 mt-4 border-t border-slate-100">
                                    @if($mod['status'] === 'Available')
                                        <a href="{{ url('/' . $mod['slug']) }}" class="text-xs font-bold text-[#1E3A5F] hover:text-[#C9A227] flex items-center justify-between">
                                            <span>Read Capability Spec</span>
                                            <i class="fa-solid fa-arrow-right text-xs"></i>
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Roadmap specification under development</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

    </div>
</section>

<!-- Call to Action -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-white border-t border-slate-200 text-center">
    <div class="max-w-3xl mx-auto space-y-6">
        <h2 class="text-2xl sm:text-3xl font-black text-[#1E3A5F]">Evaluate SmartHCM for Your Enterprise</h2>
        <p class="text-xs sm:text-sm text-slate-600">
            Learn how SmartHCM’s modular architecture can replace disparate HR point solutions while reducing total cost of ownership.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="{{ url('/demo') }}" class="px-8 py-3.5 rounded-xl bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F] font-bold text-xs shadow transition">
                Request Platform Walkthrough
            </a>
            <a href="{{ url('/pricing') }}" class="px-8 py-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-[#1E3A5F] font-semibold text-xs transition">
                View Editions &amp; Pricing
            </a>
        </div>
    </div>
</section>

@endsection
