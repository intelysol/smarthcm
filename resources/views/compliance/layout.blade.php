<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Workforce Compliance Management') — Flow HCM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col antialiased">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-4">
                    <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-lg">
                        C
                    </div>
                    <div>
                        <h1 class="font-bold text-lg leading-none text-slate-800">Workforce Compliance</h1>
                        <span class="text-xs text-slate-500 font-medium">Regulatory Eligibility, Permits, Licenses & Exemption Governance</span>
                    </div>
                </div>
                <nav class="flex items-center space-x-6 text-sm font-medium">
                    <a href="{{ route('compliance.dashboard') }}" class="text-slate-600 hover:text-indigo-600 transition-colors">Overview</a>
                    <a href="{{ route('compliance.expirations') }}" class="text-slate-600 hover:text-indigo-600 transition-colors">Expirations</a>
                    <a href="{{ route('compliance.exemptions') }}" class="text-slate-600 hover:text-indigo-600 transition-colors">Exemptions</a>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        Active Tenant
                    </span>
                </nav>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-slate-200 mt-auto py-4 text-center text-xs text-slate-500">
        Flow Enterprise HCM &bull; Epic 2.33 Compliance, Permits, Visas & Regulatory Governance &bull; Multi-Tenant Architecture
    </footer>
</body>
</html>
