<!DOCTYPE html>
<html lang=en class=h-full bg-slate-950 text-slate-100>
<head>
    <meta charset=UTF-8>
    <meta name=viewport content=width=device-width, initial-scale=1.0>
    <title>@yield('title', 'Performance Management') - SmartHCM</title>
    <script src=https://cdn.tailwindcss.com></script>
</head>
<body class=min-h-full bg-slate-950 flex flex-col font-sans antialiased>
    <header class=border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-40>
        <div class=max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between>
            <div class=flex items-center gap-3>
                <span class=h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/30>🎯</span>
                <span class=text-lg font-semibold tracking-tight text-white>SmartHCM <span class=text-indigo-400 font-normal>Performance</span></span>
            </div>
            <nav class=flex items-center gap-4 text-sm font-medium>
                <a href={{ route('performance.dashboard') }} class=text-slate-300 hover:text-white transition>Dashboard</a>
                <a href={{ route('performance.goals') }} class=text-slate-300 hover:text-white transition>Goals & OKRs</a>
                <a href={{ route('performance.reviews') }} class=text-slate-300 hover:text-white transition>Reviews</a>
                <a href={{ route('performance.calibration') }} class=text-slate-300 hover:text-white transition>Calibration</a>
            </nav>
        </div>
    </header>

    <main class=flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8>
        @yield('content')
    </main>

    <footer class=border-t border-slate-900 py-4 text-center text-xs text-slate-600>
        Flow Enterprise Platform — Continuous Performance Lifecycle & Talent Alignment
    </footer>
</body>
</html>
