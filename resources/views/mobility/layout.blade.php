<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Global Mobility & Expatriate Management') - Flow HCM Enterprise</title>
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
                    <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-500/20">
                        <i class="fa-solid fa-earth-americas"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold bg-gradient-to-r from-indigo-400 to-cyan-400 bg-clip-text text-transparent">Flow HCM</span>
                        <span class="text-xs text-slate-400 ml-2 font-mono">GLOBAL MOBILITY 2.39</span>
                    </div>
                </div>

                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('mobility.dashboard') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-chart-pie mr-1 text-slate-400"></i> Dashboard
                    </a>
                    <a href="{{ route('mobility.programs.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-layer-group mr-1 text-slate-400"></i> Programs
                    </a>
                    <a href="{{ route('mobility.requests.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-file-signature mr-1 text-indigo-400"></i> Requests
                    </a>
                    <a href="{{ route('mobility.assignments.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-passport mr-1 text-emerald-400"></i> Assignments
                    </a>
                    <a href="{{ route('mobility.relocation.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-truck-ramp-box mr-1 text-amber-400"></i> Relocation
                    </a>
                    <a href="{{ route('mobility.travelers.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-plane-departure mr-1 text-cyan-400"></i> Business Travelers
                    </a>
                </nav>

                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                        Cross-Border Sync
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
        <p>Flow Enterprise HCM &copy; {{ date('Y') }} &middot; Global Mobility, International Relocation & Expatriate Orchestration Platform</p>
    </footer>
</body>
</html>
