@extends('public.layouts.marketing')

@section('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebPage",
    "name": "SmartHCM Enterprise Security Architecture",
    "description": "Factual overview of SmartHCM security controls: tenant isolation, AES-256 encryption, RBAC, audit logging, and automated backups.",
    "url": "{{ url('/security') }}"
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
            "name": "Security",
            "item": "{{ url('/security') }}"
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
            <span class="text-[#F4E7B2] font-semibold">Security</span>
        </nav>
        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#16805C]/20 text-emerald-300 border border-[#16805C]/30">
            <i class="fa-solid fa-shield-halved mr-1.5 text-emerald-400"></i> Enterprise Trust Center
        </div>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            Security, Governance &amp; Data Protection
        </h1>
        <p class="text-sm sm:text-base text-slate-200 leading-relaxed">
            Factual documentation of the technical and operational safeguards protecting workforce records, authentication pipelines, and tenant isolation in SmartHCM.
        </p>
    </div>
</section>

<section class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
    <div class="max-w-4xl mx-auto space-y-12 text-slate-700">
        <!-- 9 Core Controls (Prompt Section 52) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h3 class="text-sm font-bold text-[#1E3A5F]">1. Authentication</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    SAML 2.0 and OAuth2 enterprise single sign-on (SSO), enforced multi-factor authentication (MFA), secure bcrypt password hashing, and configurable session timeout thresholds.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-user-lock"></i>
                </div>
                <h3 class="text-sm font-bold text-[#1E3A5F]">2. Role-Based Authorization</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Zero-trust RBAC with strict workspace boundary checks (`workspace:platform`, `workspace:hr`, `workspace:manager`, `workspace:employee`). No unauthorized cross-role escalation.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-database"></i>
                </div>
                <h3 class="text-sm font-bold text-[#1E3A5F]">3. Tenant Isolation</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Eloquent global query scopes enforce `WHERE tenant_id = ?` across all aggregates. Client-provided tenant keys are never trusted over server session context.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <h3 class="text-sm font-bold text-[#1E3A5F]">4. Audit Logging</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Automated event auditing capturing actor ID, timestamp, IP address, user agent, and before/after mutation diffs. Sensitive fields are redacted prior to write.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <h3 class="text-sm font-bold text-[#1E3A5F]">5. Data Protection</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    AES-256 encryption at rest for sensitive attributes (tax identifiers, banking details). TLS 1.3 in transit with strict HSTS security headers.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-eye-slash"></i>
                </div>
                <h3 class="text-sm font-bold text-[#1E3A5F]">6. Field-Level Access Control</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Granular attribute masking ensuring people managers only view approved team records, while general employees see only their personal profile.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <h3 class="text-sm font-bold text-[#1E3A5F]">7. API Security</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    OAuth2 Bearer token authentication, strict per-IP rate limiting, payload schema validation, and complete absence of hardcoded API secrets.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-hard-drive"></i>
                </div>
                <h3 class="text-sm font-bold text-[#1E3A5F]">8. Backup &amp; Recovery</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Automated daily database snapshots, multi-region point-in-time recovery (PITR) journals, and periodic recovery drills verifying data integrity.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#F7F9FC] border border-slate-200 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A5F] text-[#C9A227] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-gauge-high"></i>
                </div>
                <h3 class="text-sm font-bold text-[#1E3A5F]">9. Health Monitoring</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Active synthetic health endpoints (`/health/live`, `/health/ready`), queue queue-lag sensors via Laravel Horizon, and storage capacity alarms.
                </p>
            </div>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900 text-slate-300 font-mono text-xs space-y-2">
            <div class="text-emerald-400 font-bold"># PRODUCTION SAFETY SAFEGUARDS</div>
            <div>[GUARD 1] Seeding Guard: Demo seeder halts in production unless explicitly authorized.</div>
            <div>[GUARD 2] Reset Guard: Demo reset commands are permanently locked in production.</div>
            <div>[GUARD 3] Privacy Guard: Mobile GPS attendance never tracks continuous background location.</div>
        </div>
    </div>
</section>

@endsection
