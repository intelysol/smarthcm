<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Self-Service Portal') - Flow HCM Enterprise</title>
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
    <!-- Top Navigation -->
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-lg bg-teal-600 flex items-center justify-center text-white font-bold shadow-lg shadow-teal-500/20">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold bg-gradient-to-r from-teal-400 to-emerald-400 bg-clip-text text-transparent">Flow HCM</span>
                        <span class="text-xs text-slate-400 ml-2 font-mono">HR SERVICE DELIVERY 2.22</span>
                    </div>
                </div>

                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('self-service.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-house-chimney mr-1 text-slate-400"></i> My ESS Home
                    </a>
                    <a href="{{ route('self-service.catalog.index') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-layer-group mr-1 text-slate-400"></i> Service Catalog
                    </a>
                    <a href="{{ route('self-service.requests.index') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-ticket mr-1 text-slate-400"></i> My Requests
                    </a>
                    <a href="{{ route('self-service.knowledge.index') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-book-open mr-1 text-slate-400"></i> Knowledge Base
                    </a>
                    <a href="{{ route('self-service.announcements.index') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-bullhorn mr-1 text-slate-400"></i> Announcements
                    </a>
                    <a href="{{ route('self-service.manager.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800 text-teal-400 hover:text-teal-300 transition">
                        <i class="fa-solid fa-users-gear mr-1"></i> Manager MSS
                    </a>
                    <a href="{{ route('self-service.agent.workspace') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800 text-amber-400 hover:text-amber-300 transition">
                        <i class="fa-solid fa-clipboard-user mr-1"></i> HR Agent Desk
                    </a>
                </nav>

                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-900/60 text-teal-300 border border-teal-700/50">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400 mr-1.5 animate-pulse"></span> SLA Active
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 py-4 text-center text-xs text-slate-500">
        Flow HCM Enterprise &bull; Epic 2.22 Employee Self-Service (ESS), Manager Self-Service (MSS) &amp; HR Helpdesk &bull; Orchestration Layer
    </footer>
</body>
</html>
