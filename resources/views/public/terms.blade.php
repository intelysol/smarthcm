@extends('public.layouts.marketing')

@section('content')

<section class="bg-[#1E3A5F] text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-4xl mx-auto space-y-4">
        <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white">
            SmartHCM Terms of Service
        </h1>
        <p class="text-xs sm:text-sm text-slate-300">
            Last Updated: September 2026 &bull; Scope: Public Website &amp; Enterprise Subscription Platform
        </p>
    </div>
</section>

<section class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
    <div class="max-w-4xl mx-auto space-y-8 text-slate-700 text-xs sm:text-sm leading-relaxed">
        <div class="space-y-3">
            <h2 class="text-base font-bold text-[#1E3A5F]">1. Acceptance of Terms</h2>
            <p>
                By accessing this website or utilizing SmartHCM enterprise services, you agree to be bound by these Terms of Service. Enterprise access to authenticated application workspaces is governed by the executed Master Services Agreement (MSA) and applicable Service Level Agreements (SLAs).
            </p>
        </div>

        <div class="space-y-3">
            <h2 class="text-base font-bold text-[#1E3A5F]">2. Use of Public Website</h2>
            <p>
                The public website is provided for informational, documentation, and evaluation purposes. You agree not to perform unauthorized penetration testing, denial of service attacks, or malicious scraping of public assets.
            </p>
        </div>

        <div class="space-y-3">
            <h2 class="text-base font-bold text-[#1E3A5F]">3. Enterprise Subscription &amp; Tenant Responsibilities</h2>
            <p>
                Authorized tenant organizations are responsible for maintaining the confidentiality of their administrator credentials, configuring role-based permissions appropriately, and ensuring lawful basis for capturing employee attendance and personal records.
            </p>
        </div>

        <div class="space-y-3">
            <h2 class="text-base font-bold text-[#1E3A5F]">4. Intellectual Property</h2>
            <p>
                SmartHCM, its proprietary logos, user interface designs, algorithms, documentation, and underlying codebases are the exclusive intellectual property of Intelysol SmartHCM Enterprise Solutions.
            </p>
        </div>

        <div class="space-y-3">
            <h2 class="text-base font-bold text-[#1E3A5F]">5. Limitation of Liability</h2>
            <p>
                SmartHCM provides software on an "as is" and "as available" basis. Specific uptime commitments and financial remedies are defined solely within enterprise Service Level Agreements.
            </p>
        </div>
    </div>
</section>

@endsection
