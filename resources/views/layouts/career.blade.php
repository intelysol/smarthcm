<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Flow HCM Career, Skills & Talent') - Flow HCM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col font-sans">
    <header class="bg-slate-800/90 border-b border-slate-700 backdrop-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                        <i class="fa-solid fa-chart-line text-white text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xl font-bold bg-gradient-to-r from-emerald-400 to-teal-200 bg-clip-text text-transparent">Flow HCM</span>
                        <span class="text-xs text-slate-400 block font-medium">Talent & Succession Platform</span>
                    </div>
                </div>

                <nav class="hidden md:flex space-x-1 text-sm font-medium">
                    <!-- ESS -->
                    <a href="{{ route('career.employee.skills') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">My Skills</a>
                    <a href="{{ route('career.employee.dashboard') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Career Hub</a>
                    <a href="{{ route('career.employee.path') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Career Path</a>
                    <a href="{{ route('career.employee.plans') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Development Plans</a>

                    <!-- MSS -->
                    <a href="{{ route('career.manager.dashboard') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Team Talent</a>

                    <!-- Admin -->
                    <a href="{{ route('career.admin.dashboard') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Talent Exec</a>
                    <a href="{{ route('career.admin.nine_box') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">9-Box Grid</a>
                    <a href="{{ route('career.admin.succession') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Succession</a>
                    <a href="{{ route('career.admin.reports') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/50 transition">Analytics</a>
                </nav>

                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                        Active Tenant
                    </span>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <footer class="bg-slate-800/60 border-t border-slate-700/50 py-6 mt-12 text-center text-xs text-slate-400">
        <p>&copy; {{ date('Y') }} Flow HCM Enterprise — Career, Skills, Talent & Succession Management</p>
    </footer>
</body>
</html>
