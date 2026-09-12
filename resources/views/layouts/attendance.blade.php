<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Attendance & Workforce Scheduling') — Flow HCM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">
    <!-- Navigation -->
    <header class="bg-slate-800 border-b border-slate-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <i class="fa-solid fa-clock text-white text-lg"></i>
                    </span>
                    <div>
                        <span class="font-bold text-lg text-white tracking-tight">Flow HCM</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 ml-2 border border-indigo-500/30">Time & Scheduling</span>
                    </div>
                </div>

                <nav class="hidden md:flex space-x-1 text-sm font-medium">
                    <a href="{{ route('hcm.attendance.dashboard') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('hcm.attendance.dashboard') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700/50' }}">Dashboard</a>
                    <a href="{{ route('hcm.attendance.roster_board') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('hcm.attendance.roster_board') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700/50' }}">Roster Board</a>
                    <a href="{{ route('hcm.attendance.shifts') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('hcm.attendance.shifts') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700/50' }}">Shifts</a>
                    <a href="{{ route('hcm.attendance.calendars') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('hcm.attendance.calendars') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700/50' }}">Calendars</a>
                    <a href="{{ route('hcm.attendance.devices') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('hcm.attendance.devices') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700/50' }}">Devices</a>
                    <a href="{{ route('hcm.attendance.exceptions') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('hcm.attendance.exceptions') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700/50' }}">Exceptions</a>
                    <a href="{{ route('hcm.attendance.timesheets') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('hcm.attendance.timesheets') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700/50' }}">Timesheets</a>
                    <a href="{{ route('hcm.attendance.periods') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('hcm.attendance.periods') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700/50' }}">Periods</a>
                </nav>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('hcm.attendance.me') }}" class="px-3 py-1.5 rounded-lg bg-indigo-600/30 text-indigo-300 hover:bg-indigo-600/50 text-xs font-semibold border border-indigo-500/30 flex items-center gap-1.5">
                        <i class="fa-solid fa-user"></i> My Attendance
                    </a>
                    <a href="{{ route('hcm.attendance.manager') }}" class="px-3 py-1.5 rounded-lg bg-emerald-600/30 text-emerald-300 hover:bg-emerald-600/50 text-xs font-semibold border border-emerald-500/30 flex items-center gap-1.5">
                        <i class="fa-solid fa-users"></i> Team
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>
</body>
</html>
