<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Benefits & Financial Wellness') - Flow HCM Enterprise</title>
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
                    <div class="w-9 h-9 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold shadow-lg shadow-emerald-500/20">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">Flow HCM</span>
                        <span class="text-xs text-slate-400 ml-2 font-mono">BENEFITS & LOANS 2.20</span>
                    </div>
                </div>

                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('benefits.dashboard') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-chart-pie mr-1 text-slate-400"></i> Dashboard
                    </a>
                    <a href="{{ route('benefits.programs.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-layer-group mr-1 text-slate-400"></i> Programs
                    </a>
                    <a href="{{ route('benefits.plans.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-shield-halved mr-1 text-slate-400"></i> Plans
                    </a>
                    <a href="{{ route('benefits.open_enrollment.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-calendar-check mr-1 text-emerald-400"></i> Open Enrollment
                    </a>
                    <a href="{{ route('benefits.self_service.wizard') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-indigo-300 hover:text-white transition">
                        <i class="fa-solid fa-wand-magic-sparkles mr-1 text-indigo-400"></i> Self-Service
                    </a>
                    <a href="{{ route('benefits.enrollments.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-user-check mr-1 text-slate-400"></i> Enrollments
                    </a>
                    <a href="{{ route('benefits.life_events.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-heart mr-1 text-pink-400"></i> Life Events
                    </a>
                    <a href="{{ route('benefits.reconciliation.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-scale-balanced mr-1 text-amber-400"></i> Reconciliation
                    </a>
                    <a href="{{ route('benefits.claims.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-receipt mr-1 text-slate-400"></i> Claims
                    </a>
                    <a href="{{ route('benefits.loans.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-hand-holding-dollar mr-1 text-slate-400"></i> Loans
                    </a>
                </nav>

                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-900/60 text-emerald-300 border border-emerald-700/50">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span> Production Ready
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
        Flow HCM Enterprise &bull; Epic 2.20 Benefits, Insurance, Retirement, Loans & Financial Wellness &bull; Multi-Tenant &amp; Immutable Financial Audit
    </footer>

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
        const icon = type === 'success' ? 'fa-circle-check text-emerald-400' : type === 'error' ? 'fa-circle-xmark text-rose-400' : 'fa-circle-exclamation text-amber-400';
        toast.innerHTML = `
            <i class="fa-solid ${icon} text-lg mt-0.5"></i>
            <div class="flex-1">
                ${title ? `<div class="font-bold text-xs uppercase tracking-wider text-white mb-0.5">${title}</div>` : ''}
                <div class="text-xs text-slate-200">${message}</div>
                ${refId ? `<div class="text-[10px] text-slate-400 mt-1 font-mono">Ref: ${refId}</div>` : ''}
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white p-1 text-xs"><i class="fa-solid fa-xmark"></i></button>
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
