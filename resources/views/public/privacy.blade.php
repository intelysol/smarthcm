@extends('public.layouts.marketing')

@section('content')

<section class="bg-[#1E3A5F] text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-4xl mx-auto space-y-4">
        <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white">
            SmartHCM Privacy Policy
        </h1>
        <p class="text-xs sm:text-sm text-slate-300">
            Last Updated: September 2026 &bull; Scope: SmartHCM Public Marketing Website &amp; Enterprise Application
        </p>
    </div>
</section>

<section class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
    <div class="max-w-4xl mx-auto space-y-8 text-slate-700 text-xs sm:text-sm leading-relaxed">
        <div class="space-y-3">
            <h2 class="text-base font-bold text-[#1E3A5F]">1. Overview &amp; Data Protection Principles</h2>
            <p>
                Intelysol SmartHCM Enterprise Solutions ("SmartHCM", "we", "us") values the confidentiality and trust of our clients, prospective customers, and end-user employees. This policy outlines how information is collected, processed, and safeguarded across our public website and enterprise software platform.
            </p>
        </div>

        <div class="space-y-3">
            <h2 class="text-base font-bold text-[#1E3A5F]">2. Data Categories Collected</h2>
            <ul class="list-disc pl-5 space-y-1 text-slate-600">
                <li><strong>Public Website Inquiries:</strong> Contact information provided voluntarily through demonstration requests or sales inquiry forms (name, work email, phone number, company name, organization size, requirements).</li>
                <li><strong>Enterprise Workforce Data:</strong> When utilizing the SmartHCM application, tenant organizations store employee profile records, job architecture, attendance punch logs, and compensation structures under tenant administration.</li>
                <li><strong>Mobile Attendance Location Data:</strong> Geographic coordinates (latitude/longitude) and optional camera photos are captured <em>strictly at the exact instant an employee clicks "Check In" or "Check Out"</em>. SmartHCM does <strong>not</strong> track background location or continuous employee movements.</li>
            </ul>
        </div>

        <div class="space-y-3">
            <h2 class="text-base font-bold text-[#1E3A5F]">3. Purpose of Processing</h2>
            <p>
                We process information solely to:
            </p>
            <ul class="list-disc pl-5 space-y-1 text-slate-600">
                <li>Respond to enterprise product inquiries and schedule product demonstrations.</li>
                <li>Provide human capital management software services requested by enterprise tenant administrators.</li>
                <li>Validate attendance punches against configured job-site geofences without invasive surveillance.</li>
                <li>Maintain system performance, diagnostic logging, and platform security.</li>
            </ul>
        </div>

        <div class="space-y-3">
            <h2 class="text-base font-bold text-[#1E3A5F]">4. Security &amp; Encryption Standards</h2>
            <p>
                All data is encrypted in transit using TLS 1.3 and at rest using AES-256. Database records are protected by cryptographic tenant isolation scopes, ensuring one organization’s workforce records are never accessible to another.
            </p>
        </div>

        <div class="space-y-3">
            <h2 class="text-base font-bold text-[#1E3A5F]">5. Data Retention &amp; User Rights</h2>
            <p>
                Public inquiry data is retained only as long as necessary to fulfill sales communications or comply with statutory recordkeeping. Enterprise clients control their own data retention cycles and employee data deletion policies through the Tenant Administration console. Employees maintain rights under applicable data protection laws (including GDPR and CCPA) to access, review, and request correction of their personal data via their organization’s HR administrator.
            </p>
        </div>

        <div class="space-y-3">
            <h2 class="text-base font-bold text-[#1E3A5F]">6. Contact Mechanism</h2>
            <p>
                For privacy questions or data inquiries, contact our Data Governance Officer at <a href="mailto:privacy@smarthcm.com" class="text-[#1E3A5F] underline font-semibold">privacy@smarthcm.com</a>.
            </p>
        </div>
    </div>
</section>

@endsection
