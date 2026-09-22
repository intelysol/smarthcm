<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Flow HCM — Strategic Workforce Planning')</title>
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
                        <i class="fa-solid fa-sitemap text-lg"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight text-white">Flow HCM</span>
                        <span class="text-xs text-emerald-300 block font-medium">Strategic Workforce & Organizational Planning</span>
                    </div>
                </div>

                <nav class="hidden md:flex space-x-1 text-sm font-medium">
                    <a href="{{ route('workforce-planning.dashboard') }}" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-gauge-high mr-1.5 text-emerald-400"></i>Executive Dashboard
                    </a>
                    <a href="#plans" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-clipboard-list mr-1.5 text-blue-400"></i>Planning Cycles
                    </a>
                    <a href="#positions" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-id-badge mr-1.5 text-cyan-400"></i>Positions & Budget
                    </a>
                    <a href="#hiring" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-user-plus mr-1.5 text-amber-400"></i>Hiring Plan
                    </a>
                    <a href="#scenarios" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-code-compare mr-1.5 text-purple-400"></i>Scenarios
                    </a>
                    <a href="#actual-vs-plan" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200 hover:text-white transition">
                        <i class="fa-solid fa-chart-line mr-1.5 text-pink-400"></i>Actual vs Plan
                    </a>
                </nav>

                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-900 text-emerald-300 border border-emerald-700">
                        <i class="fa-solid fa-shield-halved mr-1 text-xs"></i>Epic 2.24 Active
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            Flow HCM Enterprise Platform — Strategic Workforce Planning, Budgeted Positions & Organizational Modeling.
        </div>
    </footer>

    <!-- Global Toast Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none"></div>

    <script>
    window.showNotification = function(type, message, title = null, refId = null) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `pointer-events-auto p-4 rounded-xl shadow-2xl border text-sm max-w-sm flex items-start gap-3 transition-all duration-300 transform translate-x-5 opacity-0 ${
            type === 'success' ? 'bg-white border-emerald-500/40 text-emerald-800' :
            type === 'error' ? 'bg-white border-rose-500/40 text-rose-800' :
            'bg-white border-amber-500/40 text-amber-800'
        }`;
        toast.innerHTML = `
            <div class="flex-1">
                ${title ? `<div class="font-bold text-xs uppercase tracking-wider text-slate-900 mb-0.5">${title}</div>` : ''}
                <div class="text-xs text-slate-700">${message}</div>
                ${refId ? `<div class="text-[10px] text-slate-400 mt-1 font-mono">Ref: ${refId}</div>` : ''}
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 p-1 text-xs">&times;</button>
        `;
        container.appendChild(toast);
        setTimeout(() => { toast.classList.remove('translate-x-5', 'opacity-0'); }, 10);
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-x-5');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    };
    </script>
</body>
</html>
