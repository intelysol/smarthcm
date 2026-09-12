<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SmartHCM — Workforce Productivity & ROI Intelligence')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col font-sans">

    <!-- Top Navigation Bar -->
    <header class="bg-slate-900 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-white font-bold text-xl shadow">
                        <i class="fa-solid fa-chart-line text-lg"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight text-white">SmartHCM</span>
                        <span class="text-xs text-emerald-300 block font-medium">Workforce Productivity & ROI Intelligence</span>
                    </div>
                </div>

                <nav class="hidden md:flex space-x-1 text-sm font-medium">
                    <a href="/hcm/productivity/executive" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-crown mr-1.5 text-amber-400"></i>Executive
                    </a>
                    <a href="/hcm/productivity/manager" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-user-group mr-1.5 text-blue-400"></i>Manager
                    </a>
                    <a href="/hcm/productivity/hr" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-people-roof mr-1.5 text-purple-400"></i>HR Intelligence
                    </a>
                    <a href="/hcm/productivity/finance" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-scale-balanced mr-1.5 text-emerald-400"></i>Finance & Unit Cost
                    </a>
                    <a href="/hcm/productivity/explorer" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-compass mr-1.5 text-cyan-400"></i>Explorer
                    </a>
                    <div class="border-l border-slate-700 mx-2 h-6 self-center"></div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-950 text-emerald-300 border border-emerald-800">
                        <i class="fa-solid fa-shield-check mr-1"></i>Non-Surveillance
                    </span>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-xs text-slate-500 text-center">
        SmartHCM Enterprise &copy; 2026 — Workforce Productivity, Labor Efficiency & ROI Intelligence Layer (Epic 2.50)
    </footer>

</body>
</html>
