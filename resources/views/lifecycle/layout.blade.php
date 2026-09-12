<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Flow HCM — Employee Lifecycle & Personnel Actions')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col font-sans">

    <!-- Top Navigation Bar -->
    <header class="bg-slate-900 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl shadow">
                        <i class="fa-solid fa-arrows-split-up-and-left text-lg"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight text-white">Flow HCM</span>
                        <span class="text-xs text-indigo-300 block font-medium">Employee Lifecycle & Personnel Actions</span>
                    </div>
                </div>

                <nav class="hidden md:flex space-x-1 text-sm font-medium">
                    <a href="{{ route('lifecycle.dashboard') }}" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-gauge-high mr-1.5 text-indigo-400"></i>Dashboard
                    </a>
                    <a href="#pending" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-clock mr-1.5 text-amber-400"></i>Pending Approvals
                    </a>
                    <a href="#scheduled" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-calendar-check mr-1.5 text-blue-400"></i>Scheduled Future Actions
                    </a>
                    <a href="{{ route('lifecycle.employee.actions') }}" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-user-tag mr-1.5 text-teal-400"></i>My Personnel Actions
                    </a>
                </nav>

                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-900 text-indigo-300 border border-indigo-700">
                        <i class="fa-solid fa-shield-halved mr-1 text-xs"></i>Epic 2.28 Active
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            Flow HCM Enterprise Platform — Employee Lifecycle, Internal Mobility & Change Control Architecture.
        </div>
    </footer>
</body>
</html>
