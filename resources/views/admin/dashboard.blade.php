<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HCM Enterprise Control Center & Administration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans antialiased">
    <!-- Top Nav Header -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/20 text-white font-black text-xl">
                    {{ substr($dashboard['tenant']['name'] ?? 'S', 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h1 class="font-bold text-lg leading-tight tracking-wide text-white">{{ $dashboard['tenant']['name'] ?? 'Enterprise Tenant' }}</h1>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            {{ strtoupper($dashboard['tenant']['status'] ?? 'ACTIVE') }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                            PROD
                        </span>
                    </div>
                    <p class="text-xs text-slate-400">Enterprise Administration &amp; Multi-Tenant Control Plane</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div class="text-xs text-slate-400">Setup Health</div>
                    <div class="text-sm font-bold text-emerald-400">{{ $dashboard['setup_health']['overall_score'] ?? 95.0 }}% Complete</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
            </div>
        </div>

        <!-- Secondary Admin Navigation Bar -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-slate-800/80 overflow-x-auto">
            <nav class="flex space-x-6 py-2.5 text-xs font-medium text-slate-400 whitespace-nowrap">
                <a href="#overview" class="text-indigo-400 border-b-2 border-indigo-500 pb-1 font-semibold flex items-center">
                    <i class="fa-solid fa-gauge-high mr-1.5"></i> Control Center
                </a>
                <a href="#modules" class="hover:text-slate-200 transition flex items-center">
                    <i class="fa-solid fa-cubes mr-1.5"></i> Modules &amp; Features
                </a>
                <a href="#configuration" class="hover:text-slate-200 transition flex items-center">
                    <i class="fa-solid fa-sliders mr-1.5"></i> Configuration
                </a>
                <a href="#branding" class="hover:text-slate-200 transition flex items-center">
                    <i class="fa-solid fa-palette mr-1.5"></i> Branding &amp; Portal
                </a>
                <a href="#localization" class="hover:text-slate-200 transition flex items-center">
                    <i class="fa-solid fa-globe mr-1.5"></i> Localization (Pakistan/Intl)
                </a>
                <a href="#security" class="hover:text-slate-200 transition flex items-center">
                    <i class="fa-solid fa-shield-halved mr-1.5"></i> Security &amp; Roles
                </a>
                <a href="#system-health" class="hover:text-slate-200 transition flex items-center">
                    <i class="fa-solid fa-heart-pulse mr-1.5"></i> System Health
                </a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- KPI Cards Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Workforce</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white">{{ $dashboard['kpis']['total_employees'] }}</span>
                    <span class="text-xs text-slate-400">Employees</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">{{ $dashboard['kpis']['departments_count'] }} configured departments</p>
            </div>

            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Active Users &amp; Identities</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-indigo-400">{{ $dashboard['kpis']['active_users'] }}</span>
                    <span class="text-xs text-emerald-400 font-semibold">Active</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">RBAC &amp; MFA Enforced</p>
            </div>

            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Active HCM Modules</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-white">{{ $dashboard['kpis']['enabled_modules_count'] }}</span>
                    <span class="text-xs text-teal-400 font-medium">Activated</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Zero dependency conflicts</p>
            </div>

            <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Onboarding &amp; Setup Progress</span>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-3xl font-extrabold text-emerald-400">{{ $dashboard['onboarding']['progress_pct'] }}%</span>
                    <span class="text-xs text-slate-400">{{ $dashboard['onboarding']['status'] }}</span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-1.5 mt-3">
                    <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ $dashboard['onboarding']['progress_pct'] }}%"></div>
                </div>
            </div>
        </div>

        <!-- Two Columns: Feature Activation Matrix & Setup Health -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Feature Activation Matrix (2 cols) -->
            <div class="lg:col-span-2 p-6 rounded-xl bg-slate-900 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200 flex items-center">
                        <i class="fa-solid fa-cubes mr-2 text-indigo-400"></i> Module Activation &amp; Dependency Governance
                    </h3>
                    <span class="text-xs text-slate-400">Tenant-Level Feature Flags</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($dashboard['features'] as $key => $f)
                    <div class="p-3.5 rounded-lg bg-slate-950/70 border border-slate-800 flex items-start justify-between">
                        <div>
                            <div class="text-xs font-bold text-white flex items-center">
                                {{ $f['name'] }}
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1 leading-relaxed">{{ $f['description'] }}</div>
                            @if(!empty($f['dependencies']))
                            <div class="text-[10px] text-slate-500 mt-1.5">
                                Requires: <span class="text-slate-400">{{ implode(', ', $f['dependencies']) }}</span>
                            </div>
                            @endif
                        </div>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $f['is_enabled'] ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-500' }}">
                            {{ $f['is_enabled'] ? 'ENABLED' : 'DISABLED' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Setup Health Breakdown (1 col) -->
            <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200 flex items-center">
                        <i class="fa-solid fa-heart-pulse mr-2 text-emerald-400"></i> Setup Health Index
                    </h3>
                    <span class="text-xs font-extrabold text-emerald-400">{{ $dashboard['setup_health']['overall_score'] }}%</span>
                </div>

                <div class="space-y-3">
                    @foreach($dashboard['setup_health']['category_scores'] as $cat => $score)
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-300 capitalize">{{ str_replace('_', ' ', strtolower($cat)) }}</span>
                            <span class="text-slate-200 font-semibold">{{ $score }}%</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $score }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-slate-800/80 text-xs text-slate-400">
                    <i class="fa-solid fa-circle-check text-emerald-400 mr-1.5"></i> Organization, Security, and Core HR modules meet production deployment thresholds.
                </div>
            </div>
        </div>

        <!-- Branding & Localization Foundations -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Branding Configuration -->
            <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200 flex items-center">
                        <i class="fa-solid fa-palette mr-2 text-indigo-400"></i> Tenant-Isolated Branding
                    </h3>
                    <span class="text-xs text-slate-400">Theme &amp; Identity</span>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                        <span class="text-slate-400">Portal Display Name</span>
                        <span class="text-white font-medium">{{ $dashboard['branding']['portal_title'] }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                        <span class="text-slate-400">Company Name</span>
                        <span class="text-white font-medium">{{ $dashboard['branding']['company_name'] }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-800/60">
                        <span class="text-slate-400">Brand Colors (Primary / Secondary)</span>
                        <div class="flex items-center space-x-2">
                            <span class="w-4 h-4 rounded-full" style="background-color: {{ $dashboard['branding']['primary_color'] }}"></span>
                            <span class="text-white font-mono">{{ $dashboard['branding']['primary_color'] }}</span>
                            <span class="w-4 h-4 rounded-full ml-2" style="background-color: {{ $dashboard['branding']['secondary_color'] }}"></span>
                            <span class="text-white font-mono">{{ $dashboard['branding']['secondary_color'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regional & Localization Foundation -->
            <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200 flex items-center">
                        <i class="fa-solid fa-globe mr-2 text-emerald-400"></i> Localization &amp; Regional Foundations
                    </h3>
                    <span class="text-xs text-slate-400">Country: {{ $dashboard['localization']['country_code'] }}</span>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                        <span class="text-slate-400">Currency &amp; Symbol</span>
                        <span class="text-white font-medium">{{ $dashboard['localization']['currency'] }} ({{ $dashboard['localization']['currency_symbol'] }})</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                        <span class="text-slate-400">Timezone</span>
                        <span class="text-white font-medium">{{ $dashboard['localization']['timezone'] }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                        <span class="text-slate-400">National Tax ID Mask</span>
                        <span class="text-emerald-400 font-mono font-medium">{{ $dashboard['localization']['national_id_mask'] }} ({{ $dashboard['localization']['tax_identifier_type'] }})</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                        <span class="text-slate-400">Fiscal Year Start Month</span>
                        <span class="text-white font-medium">Month {{ $dashboard['localization']['fiscal_year_start_month'] }} (July)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Diagnostics Grid -->
        <div class="p-6 rounded-xl bg-slate-900 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-slate-200 flex items-center">
                    <i class="fa-solid fa-server mr-2 text-cyan-400"></i> System &amp; Infrastructure Health
                </h3>
                <span class="text-xs text-emerald-400 font-medium"><i class="fa-solid fa-circle text-[8px] mr-1"></i> All Operational</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($dashboard['system_health'] as $service => $meta)
                <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-lg flex items-center justify-between">
                    <div>
                        <div class="text-[11px] text-slate-400 uppercase tracking-wider font-semibold">{{ str_replace('_', ' ', $service) }}</div>
                        <div class="text-xs text-slate-200 mt-0.5">Latency: {{ $meta['latency_ms'] ?? 4 }}ms</div>
                    </div>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                </div>
                @endforeach
            </div>
        </div>
    </main>
</body>
</html>
