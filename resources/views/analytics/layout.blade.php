<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Flow HCM — People Analytics & Reporting')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col font-sans">

    <!-- Top Navigation Bar -->
    <header class="bg-slate-900 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-tr from-indigo-500 to-cyan-400 flex items-center justify-center text-white font-bold text-xl shadow">
                        <i class="fa-solid fa-chart-pie text-lg"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight text-white">Flow HCM</span>
                        <span class="text-xs text-indigo-300 block font-medium">Workforce & People Analytics</span>
                    </div>
                </div>

                <nav class="hidden md:flex space-x-1 text-sm font-medium">
                    <a href="{{ route('hcm.analytics.chro') }}" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-gauge-high mr-1.5 text-indigo-400"></i>CHRO Dashboard
                    </a>
                    <a href="{{ route('hcm.analytics.workforce') }}" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-users mr-1.5 text-blue-400"></i>Workforce
                    </a>
                    <a href="{{ route('hcm.analytics.attendance') }}" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-business-time mr-1.5 text-emerald-400"></i>Attendance & Leave
                    </a>
                    <a href="{{ route('hcm.analytics.recruitment') }}" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-bullseye mr-1.5 text-amber-400"></i>Talent Acquisition
                    </a>
                    <a href="{{ route('hcm.analytics.payroll') }}" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-coins mr-1.5 text-purple-400"></i>Payroll
                    </a>
                    <a href="{{ route('hcm.analytics.reports.builder') }}" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-table mr-1.5 text-pink-400"></i>Report Builder
                    </a>
                    <a href="{{ route('hcm.analytics.quality.index') }}" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-shield-halved mr-1.5 text-teal-400"></i>Data Quality
                    </a>
                    <a href="{{ route('hcm.analytics.ai.index') }}" class="px-3 py-2 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-sm ml-2">
                        <i class="fa-solid fa-wand-magic-sparkles mr-1.5"></i>AI Assistant
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        Flow HCM Enterprise &copy; 2026 — Read-Oriented Analytical & Reporting Layer
    </footer>

</body>
</html>
