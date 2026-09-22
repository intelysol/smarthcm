@extends('public.layouts.marketing')

@section('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "AboutPage",
    "name": "About SmartHCM",
    "description": "SmartHCM enterprise workforce software company overview, mission, and leadership principles.",
    "url": "{{ url('/about') }}"
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
            "name": "About Us",
            "item": "{{ url('/about') }}"
        }
    ]
}
</script>
@endsection

@section('content')

<section class="bg-[#1E3A5F] text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-4xl mx-auto space-y-4">
        <nav class="text-xs text-slate-300 flex items-center space-x-2" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-white">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-[#F4E7B2] font-semibold">About Us</span>
        </nav>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            Engineering the Enterprise Workforce Operating System
        </h1>
        <p class="text-sm sm:text-base text-slate-200 leading-relaxed">
            SmartHCM was founded to bridge the painful divide between administrative Human Resources record-keeping and dynamic front-line operational workforce execution.
        </p>
    </div>
</section>

<section class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
    <div class="max-w-4xl mx-auto space-y-12 text-slate-700">
        <div class="space-y-4">
            <h2 class="text-2xl font-black text-[#1E3A5F]">Our Enterprise Mission</h2>
            <p class="text-xs sm:text-sm leading-relaxed">
                Modern enterprises cannot thrive on disconnected point solutions. Plant managers require real-time shift coverage; payroll controllers demand zero-error gross-to-net calculations; and workers deserve intuitive, mobile self-service. SmartHCM brings these disparate worlds together under a single, high-concurrency cloud architecture.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-2">
                <div class="text-[#C9A227] text-xl font-bold"><i class="fa-solid fa-cube"></i></div>
                <h3 class="text-sm font-bold text-[#1E3A5F]">Modular Domain Architecture</h3>
                <p class="text-xs text-slate-600 leading-relaxed">Built on clean domain-driven boundaries (DDD) ensuring stability and high extensibility.</p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-2">
                <div class="text-[#C9A227] text-xl font-bold"><i class="fa-solid fa-user-shield"></i></div>
                <h3 class="text-sm font-bold text-[#1E3A5F]">Zero-Trust Privacy</h3>
                <p class="text-xs text-slate-600 leading-relaxed">Strict cryptographic tenant isolation and zero continuous background employee tracking.</p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-2">
                <div class="text-[#C9A227] text-xl font-bold"><i class="fa-solid fa-microchip"></i></div>
                <h3 class="text-sm font-bold text-[#1E3A5F]">Responsible Intelligence</h3>
                <p class="text-xs text-slate-600 leading-relaxed">Deterministic workforce analytics without black-box bias or ungrounded generative claims.</p>
            </div>
        </div>

        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
            <h3 class="text-sm font-bold text-[#1E3A5F]">Corporate Information</h3>
            <ul class="text-xs space-y-2 text-slate-600">
                <li><strong>Entity:</strong> Intelysol SmartHCM Enterprise Solutions</li>
                <li><strong>Headquarters:</strong> Austin, Texas, United States</li>
                <li><strong>Primary Repository:</strong> <a href="https://github.com/intelysol/smarthcm" class="text-[#1E3A5F] underline font-semibold">github.com/intelysol/smarthcm</a></li>
                <li><strong>Support SLA:</strong> 24/7 Enterprise Tier Coverage Available</li>
            </ul>
        </div>
    </div>
</section>

@endsection
