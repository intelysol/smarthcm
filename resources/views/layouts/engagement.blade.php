<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Flow HCM Engagement & Culture') - Flow HCM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col font-sans">
    <header class="bg-slate-800/90 border-b border-slate-700 backdrop-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-pink-600 to-rose-400 flex items-center justify-center shadow-lg shadow-pink-500/20">
                        <i class="fa-solid fa-heart-pulse text-white text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xl font-bold bg-gradient-to-r from-pink-400 to-rose-200 bg-clip-text text-transparent">Flow HCM</span>
                        <span class="text-xs text-slate-400 block font-medium">Engagement & Culture Platform</span>
                    </div>
                </div>

                <nav class="hidden md:flex space-x-1 text-sm font-medium">
                    <!-- ESS -->
                    <a href="{{ route('engagement.employee.dashboard') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">My Engagement</a>
                    <a href="{{ route('engagement.employee.pulse') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Pulse Check</a>
                    <a href="{{ route('engagement.employee.recognition') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Recognition Wall</a>
                    <a href="{{ route('engagement.employee.suggestions') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Ideas & Suggestions</a>

                    <!-- MSS -->
                    <a href="{{ route('engagement.manager.dashboard') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Team Engagement</a>

                    <!-- Admin -->
                    <a href="{{ route('engagement.admin.dashboard') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Surveys Admin</a>
                    <a href="{{ route('engagement.admin.action_plans') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Action Plans</a>
                    <a href="{{ route('engagement.admin.culture') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Culture Initiatives</a>
                    <a href="{{ route('engagement.admin.executive') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Executive Hub</a>
                </nav>

                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-500/10 text-pink-400 border border-pink-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-pink-400 mr-1.5 animate-pulse"></span>
                        Confidential & Anonymous Protected
                    </span>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <footer class="bg-slate-800/60 border-t border-slate-700/50 py-6 mt-12 text-center text-xs text-slate-400">
        <p>&copy; {{ date('Y') }} Flow HCM Enterprise — Employee Engagement, Surveys, Pulse & Organizational Culture</p>
    </footer>
</body>
</html>
