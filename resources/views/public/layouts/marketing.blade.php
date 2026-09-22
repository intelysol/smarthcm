<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SmartHCM &bull; Modern Enterprise Human Capital Management' }}</title>
    <meta name="description" content="{{ $meta_description ?? 'Enterprise Human Capital Management platform for workforce operations, attendance, payroll, and people intelligence.' }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    
    <!-- Open Graph Metadata -->
    <meta property="og:site_name" content="SmartHCM">
    <meta property="og:title" content="{{ $title ?? 'SmartHCM &bull; Modern Enterprise Human Capital Management' }}">
    <meta property="og:description" content="{{ $meta_description ?? 'Enterprise Human Capital Management platform for workforce operations, attendance, payroll, and people intelligence.' }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:type" content="{{ $og_type ?? 'website' }}">
    <meta property="og:image" content="{{ asset('images/og-smarthcm.png') }}">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'SmartHCM Enterprise HCM' }}">
    <meta name="twitter:description" content="{{ $meta_description ?? 'Enterprise Human Capital Management platform.' }}">
    <meta name="twitter:image" content="{{ asset('images/og-smarthcm.png') }}">

    <!-- Corporate Styles & Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --brand-navy: #1E3A5F;
            --brand-dark-navy: #142A44;
            --brand-gold: #C9A227;
            --brand-gold-light: #F4E7B2;
            --brand-bg: #F7F9FC;
            --brand-surface: #FFFFFF;
            --brand-text: #1F2937;
            --brand-muted: #6B7280;
            --brand-border: #E5E7EB;
        }
        .bg-navy { background-color: var(--brand-navy); }
        .bg-dark-navy { background-color: var(--brand-dark-navy); }
        .text-navy { color: var(--brand-navy); }
        .text-gold { color: var(--brand-gold); }
        .bg-gold { background-color: var(--brand-gold); }
        .hover\:bg-gold-dark:hover { background-color: #B08E20; }
        .border-gold { border-color: var(--brand-gold); }
        .skip-to-content:focus {
            top: 1rem;
            left: 1rem;
            z-index: 9999;
            background: #C9A227;
            color: #1E3A5F;
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            border-radius: 0.5rem;
        }
    </style>

    <!-- Schema.org Organization Structured Data -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "SmartHCM",
        "legalName": "Intelysol SmartHCM Enterprise Solutions",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('images/logo.png') }}",
        "description": "Enterprise Human Capital Management platform for workforce operations, HR administration, and workforce intelligence.",
        "contactPoint": {
            "@@type": "ContactPoint",
            "telephone": "+1-800-555-4261",
            "contactType": "sales",
            "email": "sales@smarthcm.com",
            "availableLanguage": ["en"]
        },
        "sameAs": [
            "https://www.linkedin.com/company/smarthcm",
            "https://github.com/intelysol/smarthcm"
        ]
    }
    </script>

    <!-- Schema.org WebSite Structured Data -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "SmartHCM",
        "url": "{{ url('/') }}",
        "potentialAction": {
            "@@type": "SearchAction",
            "target": "{{ url('/faq') }}?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <!-- Additional Page-Specific Schemas -->
    @yield('schema')
</head>
<body class="bg-[#F7F9FC] text-[#1F2937] font-sans antialiased min-h-screen flex flex-col selection:bg-[#C9A227]/30 selection:text-[#1E3A5F]">

    <!-- Accessible Skip Link -->
    <a href="#main-content" class="sr-only focus:not-sr-only skip-to-content fixed top-[-100px] transition shadow-lg">
        Skip to main content
    </a>

    <!-- Enterprise Header / Navbar -->
    <header class="sticky top-0 z-50 bg-[#1E3A5F] text-white border-b border-[#142A44] shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand Identity -->
                <div class="flex items-center space-x-3">
                    <a href="{{ url('/') }}" class="flex items-center space-x-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-[#C9A227] text-[#1E3A5F] font-black text-xl flex items-center justify-center shadow-md transition group-hover:scale-105">
                            S
                        </div>
                        <div>
                            <span class="text-xl font-bold tracking-tight text-white block leading-tight">Smart<span class="text-[#C9A227]">HCM</span></span>
                            <span class="text-[10px] uppercase tracking-wider text-slate-300 font-semibold block">Enterprise Platform</span>
                        </div>
                    </a>
                </div>

                <!-- Primary Desktop Navigation -->
                <nav class="hidden lg:flex items-center space-x-1 font-medium text-sm text-slate-200" aria-label="Main Navigation">
                    <a href="{{ url('/platform') }}" class="px-3 py-2 rounded-lg hover:text-white hover:bg-white/10 transition {{ request()->is('platform*') ? 'text-white bg-white/10 font-bold' : '' }}">
                        Platform
                    </a>
                    <a href="{{ url('/solutions') }}" class="px-3 py-2 rounded-lg hover:text-white hover:bg-white/10 transition {{ request()->is('solutions*') ? 'text-white bg-white/10 font-bold' : '' }}">
                        Solutions
                    </a>
                    
                    <!-- Features Dropdown Trigger -->
                    <div class="relative group">
                        <button class="px-3 py-2 rounded-lg hover:text-white hover:bg-white/10 transition flex items-center space-x-1">
                            <span>Features</span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-300 transition group-hover:rotate-180"></i>
                        </button>
                        <div class="absolute left-0 mt-1 w-64 rounded-xl bg-white text-[#1F2937] shadow-xl border border-slate-200 py-2 hidden group-hover:block transition z-50">
                            <a href="{{ url('/core-hr') }}" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-100 flex items-center">
                                <i class="fa-solid fa-users text-[#1E3A5F] w-5"></i> Core HR
                            </a>
                            <a href="{{ url('/mobile-attendance') }}" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-100 flex items-center text-[#1E3A5F]">
                                <i class="fa-solid fa-location-dot text-[#C9A227] w-5"></i> Mobile GPS Attendance
                            </a>
                            <a href="{{ url('/attendance') }}" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-100 flex items-center">
                                <i class="fa-solid fa-clock text-[#1E3A5F] w-5"></i> Attendance &amp; Shifts
                            </a>
                            <a href="{{ url('/payroll') }}" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-100 flex items-center">
                                <i class="fa-solid fa-money-check-dollar text-[#1E3A5F] w-5"></i> Payroll Operations
                            </a>
                            <a href="{{ url('/leave-management') }}" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-100 flex items-center">
                                <i class="fa-solid fa-calendar-check text-[#1E3A5F] w-5"></i> Leave Management
                            </a>
                            <a href="{{ url('/recruitment') }}" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-100 flex items-center">
                                <i class="fa-solid fa-user-plus text-[#1E3A5F] w-5"></i> Recruitment &amp; ATS
                            </a>
                            <a href="{{ url('/performance-management') }}" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-100 flex items-center">
                                <i class="fa-solid fa-chart-line text-[#1E3A5F] w-5"></i> Performance &amp; OKRs
                            </a>
                            <a href="{{ url('/employee-self-service') }}" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-100 flex items-center">
                                <i class="fa-solid fa-mobile-screen text-[#1E3A5F] w-5"></i> Employee Self-Service
                            </a>
                            <a href="{{ url('/workforce-analytics') }}" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-100 flex items-center">
                                <i class="fa-solid fa-chart-pie text-[#1E3A5F] w-5"></i> Workforce Analytics
                            </a>
                        </div>
                    </div>

                    <a href="{{ url('/industries') }}" class="px-3 py-2 rounded-lg hover:text-white hover:bg-white/10 transition {{ request()->is('industries*') ? 'text-white bg-white/10 font-bold' : '' }}">
                        Industries
                    </a>
                    <a href="{{ url('/resources') }}" class="px-3 py-2 rounded-lg hover:text-white hover:bg-white/10 transition {{ request()->is('resources*') || request()->is('docs*') || request()->is('blog*') ? 'text-white bg-white/10 font-bold' : '' }}">
                        Resources
                    </a>
                    <a href="{{ url('/pricing') }}" class="px-3 py-2 rounded-lg hover:text-white hover:bg-white/10 transition {{ request()->is('pricing*') ? 'text-white bg-white/10 font-bold' : '' }}">
                        Pricing
                    </a>
                    <a href="{{ url('/about') }}" class="px-3 py-2 rounded-lg hover:text-white hover:bg-white/10 transition {{ request()->is('about*') ? 'text-white bg-white/10 font-bold' : '' }}">
                        Company
                    </a>
                </nav>

                <!-- Navigation CTAs -->
                <div class="hidden md:flex items-center space-x-3">
                    <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-bold text-white hover:text-[#F4E7B2] transition">
                        <i class="fa-solid fa-lock mr-1.5 text-xs text-[#C9A227]"></i> Sign In
                    </a>
                    <a href="{{ url('/demo') }}" class="px-4 py-2 rounded-lg bg-[#C9A227] hover:bg-gold-dark text-[#1E3A5F] text-xs font-bold transition shadow-sm flex items-center">
                        <span>Request a Demo</span>
                        <i class="fa-solid fa-arrow-right ml-1.5 text-xs"></i>
                    </a>
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex lg:hidden">
                    <button id="mobile-menu-btn" type="button" class="p-2 rounded-md text-slate-200 hover:text-white hover:bg-white/10 focus:outline-none" aria-label="Toggle Mobile Menu">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div id="mobile-menu" class="hidden lg:hidden bg-[#142A44] border-t border-slate-700 px-4 pt-3 pb-6 space-y-2">
            <a href="{{ url('/') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-white/10">Home</a>
            <a href="{{ url('/platform') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-white/10">Platform Overview</a>
            <a href="{{ url('/mobile-attendance') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-[#C9A227] hover:bg-white/10">Mobile GPS Attendance</a>
            <a href="{{ url('/solutions') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-white/10">Solutions</a>
            <a href="{{ url('/industries') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-white/10">Industries</a>
            <a href="{{ url('/pricing') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-white/10">Pricing</a>
            <a href="{{ url('/resources') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-white/10">Resources &amp; Docs</a>
            <a href="{{ url('/contact') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-white/10">Contact</a>
            <div class="pt-4 border-t border-slate-700 flex flex-col space-y-2">
                <a href="{{ route('login') }}" class="w-full text-center px-4 py-2.5 rounded-lg border border-slate-500 text-white text-xs font-bold hover:bg-white/5">
                    Sign In to SmartHCM
                </a>
                <a href="{{ url('/demo') }}" class="w-full text-center px-4 py-2.5 rounded-lg bg-[#C9A227] text-[#1E3A5F] text-xs font-bold hover:bg-gold-dark shadow">
                    Request a Demo
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main id="main-content" class="flex-grow">
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-sm flex items-center shadow-sm">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg mr-3"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Global Enterprise Footer -->
    <footer class="bg-[#142A44] text-slate-300 border-t border-slate-800 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
                <!-- Column 1: Brand & Identity -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#C9A227] text-[#1E3A5F] font-black text-lg flex items-center justify-center">S</div>
                        <span class="text-xl font-bold tracking-tight text-white">Smart<span class="text-[#C9A227]">HCM</span></span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        SmartHCM is an enterprise Human Capital Management platform engineered for workforce operations, attendance, multi-jurisdiction payroll, and workforce intelligence.
                    </p>
                    <div class="text-xs text-slate-400 space-y-1">
                        <div><i class="fa-solid fa-shield-halved text-[#C9A227] mr-1.5"></i> Multi-Tenant Cryptographic Isolation</div>
                        <div><i class="fa-solid fa-lock text-[#C9A227] mr-1.5"></i> AES-256 Encryption at Rest &amp; TLS 1.3</div>
                        <div><i class="fa-solid fa-location-crosshairs text-[#C9A227] mr-1.5"></i> Privacy-by-Design GPS Event Validation</div>
                    </div>
                </div>

                <!-- Column 2: Platform & Features -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white">Platform Modules</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ url('/core-hr') }}" class="hover:text-white transition">Core HR Records</a></li>
                        <li><a href="{{ url('/mobile-attendance') }}" class="hover:text-white transition text-[#C9A227] font-semibold">Mobile GPS Attendance</a></li>
                        <li><a href="{{ url('/attendance') }}" class="hover:text-white transition">Attendance &amp; Shifts</a></li>
                        <li><a href="{{ url('/payroll') }}" class="hover:text-white transition">Payroll Operations</a></li>
                        <li><a href="{{ url('/leave-management') }}" class="hover:text-white transition">Leave &amp; Absences</a></li>
                        <li><a href="{{ url('/performance-management') }}" class="hover:text-white transition">Performance &amp; OKRs</a></li>
                        <li><a href="{{ url('/learning-management') }}" class="hover:text-white transition">Learning &amp; LMS</a></li>
                        <li><a href="{{ url('/workforce-analytics') }}" class="hover:text-white transition">Workforce Intelligence</a></li>
                    </ul>
                </div>

                <!-- Column 3: Solutions & Industries -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white">Solutions</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ url('/solutions/hr-digitization') }}" class="hover:text-white transition">HR Digitization</a></li>
                        <li><a href="{{ url('/solutions/workforce-management') }}" class="hover:text-white transition">Workforce Scheduling</a></li>
                        <li><a href="{{ url('/solutions/employee-self-service') }}" class="hover:text-white transition">Employee Self-Service</a></li>
                        <li><a href="{{ url('/solutions/multi-tenant-hr-saas') }}" class="hover:text-white transition">Multi-Tenant SaaS</a></li>
                        <li><a href="{{ url('/industries/manufacturing') }}" class="hover:text-white transition">Manufacturing</a></li>
                        <li><a href="{{ url('/industries/healthcare') }}" class="hover:text-white transition">Healthcare</a></li>
                        <li><a href="{{ url('/industries/logistics') }}" class="hover:text-white transition">Logistics &amp; Fleet</a></li>
                        <li><a href="{{ url('/industries/retail') }}" class="hover:text-white transition">Retail &amp; Branches</a></li>
                    </ul>
                </div>

                <!-- Column 4: Resources & Trust -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white">Resources &amp; Trust</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ url('/docs') }}" class="hover:text-white transition">Product Documentation</a></li>
                        <li><a href="{{ url('/glossary') }}" class="hover:text-white transition">HCM Glossary</a></li>
                        <li><a href="{{ url('/faq') }}" class="hover:text-white transition">Knowledge Base &amp; FAQ</a></li>
                        <li><a href="{{ url('/blog') }}" class="hover:text-white transition">Workforce Blog</a></li>
                        <li><a href="{{ url('/security') }}" class="hover:text-white transition">Security &amp; Compliance</a></li>
                        <li><a href="{{ url('/privacy') }}" class="hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="{{ url('/terms') }}" class="hover:text-white transition">Terms of Service</a></li>
                        <li><a href="{{ url('/contact') }}" class="hover:text-white transition">Contact Us</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright & Portal Link -->
            <div class="mt-12 pt-8 border-t border-slate-800 flex flex-col md:flex-row items-center justify-between text-xs text-slate-500 space-y-4 md:space-y-0">
                <div>
                    &copy; {{ date('Y') }} SmartHCM. All rights reserved. Intelysol Enterprise Solutions.
                </div>
                <div class="flex items-center space-x-6">
                    <a href="{{ url('/sitemap.xml') }}" class="hover:text-slate-300 transition">XML Sitemap</a>
                    <a href="{{ url('/robots.txt') }}" class="hover:text-slate-300 transition">Robots.txt</a>
                    <a href="{{ route('login') }}" class="text-[#C9A227] hover:underline font-semibold flex items-center">
                        <i class="fa-solid fa-arrow-right-to-bracket mr-1.5"></i> Authenticated Application Portal
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Privacy-Preserving Analytics Dispatcher -->
    <script>
        // Non-PII Event Trigger for Enterprise Telemetry & Search Engines
        window.trackHcmEvent = function(eventName, payload = {}) {
            console.info('[SmartHCM Telemetry]', eventName, payload);
            if (window.dataLayer && Array.isArray(window.dataLayer)) {
                window.dataLayer.push({ event: eventName, ...payload });
            }
        };

        // Toggle Mobile Menu
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        });
    </script>
</body>
</html>
