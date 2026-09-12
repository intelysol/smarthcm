<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Workforce Administration & HR Operations') - SmartHCM Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#151e2e',
                            900: '#0f172a',
                            950: '#090d16',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans">
    <!-- Top Navigation Bar -->
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-lg bg-cyan-600 flex items-center justify-center text-white font-bold shadow-lg shadow-cyan-500/20">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">SmartHCM</span>
                        <span class="text-xs text-slate-400 ml-2 font-mono">OPERATIONS 2.40</span>
                    </div>
                </div>

                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('workforce_admin.dashboard') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-chart-pie mr-1 text-slate-400"></i> Cockpit
                    </a>
                    <a href="{{ route('workforce_admin.queues.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-list-check mr-1 text-cyan-400"></i> Queues
                    </a>
                    <a href="{{ route('workforce_admin.exceptions.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-triangle-exclamation mr-1 text-rose-400"></i> Exceptions
                    </a>
                    <a href="{{ route('workforce_admin.changes.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-clock-rotate-left mr-1 text-blue-400"></i> Changes
                    </a>
                    <a href="{{ route('workforce_admin.bulk.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-bolt mr-1 text-amber-400"></i> Bulk Ops
                    </a>
                    <a href="{{ route('workforce_admin.data_quality.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-shield-check mr-1 text-emerald-400"></i> Data Quality
                    </a>
                    <a href="{{ route('workforce_admin.checklists.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-square-check mr-1 text-teal-400"></i> Checklists
                    </a>
                    <a href="{{ route('workforce_admin.calendar.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-calendar-days mr-1 text-indigo-400"></i> Calendar
                    </a>
                </nav>

                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 mr-1.5 animate-pulse"></span>
                        Governance Control Plane
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Body -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 py-4 mt-auto text-center text-xs text-slate-500">
        <p>SmartHCM Enterprise &copy; {{ date('Y') }} &middot; Workforce Administration, Global HR Operations & Governance Control</p>
    </footer>
</body>
</html>
