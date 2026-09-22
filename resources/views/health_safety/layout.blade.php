<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Occupational Health & Safety') - Flow HCM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex flex-col antialiased">
    <header class="border-b border-slate-800 bg-slate-950/60 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-emerald-500/10 text-emerald-400 ring-1 ring-emerald-500/20 font-bold text-lg">
                        +
                    </span>
                    <span class="text-lg font-semibold tracking-tight text-white">Flow Health & Workplace Safety</span>
                </div>
                <nav class="hidden md:flex items-center space-x-1 pl-6">
                    <a href="{{ route('health.dashboard') }}" class="px-3 py-1.5 text-sm font-medium rounded-md hover:bg-slate-800 text-slate-300 hover:text-white transition">Safety Dashboard</a>
                    <a href="{{ route('health.incidents') }}" class="px-3 py-1.5 text-sm font-medium rounded-md hover:bg-slate-800 text-slate-300 hover:text-white transition">Incident Management</a>
                    <a href="{{ route('health.return_to_work') }}" class="px-3 py-1.5 text-sm font-medium rounded-md hover:bg-slate-800 text-slate-300 hover:text-white transition">Return to Work</a>
                </nav>
            </div>
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center gap-x-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400 ring-1 ring-inset ring-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    Confidential Mode Active
                </span>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Global Toast Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none"></div>

    <script>
    window.showNotification = function(type, message, title = null, refId = null) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `pointer-events-auto p-4 rounded-xl shadow-2xl border text-sm max-w-sm flex items-start gap-3 transition-all duration-300 transform translate-x-5 opacity-0 ${
            type === 'success' ? 'bg-slate-900 border-emerald-500/40 text-emerald-300' :
            type === 'error' ? 'bg-slate-900 border-rose-500/40 text-rose-300' :
            'bg-slate-900 border-amber-500/40 text-amber-300'
        }`;
        toast.innerHTML = `
            <div class="flex-1">
                ${title ? `<div class="font-bold text-xs uppercase tracking-wider text-white mb-0.5">${title}</div>` : ''}
                <div class="text-xs text-slate-200">${message}</div>
                ${refId ? `<div class="text-[10px] text-slate-400 mt-1 font-mono">Ref: ${refId}</div>` : ''}
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white p-1 text-xs">&times;</button>
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
