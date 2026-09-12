<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Payroll & Compensation Engine') - SmartHCM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="h-full flex">
    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-slate-950 border-r border-slate-800 flex flex-col justify-between">
        <div>
            <div class="p-6 border-b border-slate-800 flex items-center space-x-3">
                <div class="w-8 h-8 rounded bg-emerald-600 flex items-center justify-center font-bold text-white shadow-lg shadow-emerald-500/30">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <h1 class="text-base font-bold tracking-tight text-white">SmartHCM Payroll</h1>
                    <span class="text-xs text-emerald-400 font-mono">Enterprise Engine</span>
                </div>
            </div>
            <nav class="p-4 space-y-1">
                <a href="{{ route('payroll.dashboard') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                    <i class="fa-solid fa-gauge w-5 text-emerald-400"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('payroll.periods.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                    <i class="fa-regular fa-calendar-check w-5 text-blue-400"></i>
                    <span>Payroll Periods</span>
                </a>
                <a href="{{ route('payroll.runs.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                    <i class="fa-solid fa-calculator w-5 text-amber-400"></i>
                    <span>Payroll Runs</span>
                </a>
                <a href="{{ route('payroll.structures.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                    <i class="fa-solid fa-layer-group w-5 text-indigo-400"></i>
                    <span>Salary Structures</span>
                </a>
                <a href="{{ route('payroll.adjustments.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                    <i class="fa-solid fa-sliders w-5 text-purple-400"></i>
                    <span>Adjustments & Arrears</span>
                </a>
                <a href="{{ route('payroll.payslips.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                    <i class="fa-solid fa-file-invoice-dollar w-5 text-teal-400"></i>
                    <span>Payslips</span>
                </a>
                <a href="{{ route('payroll.payments.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                    <i class="fa-solid fa-building-columns w-5 text-green-400"></i>
                    <span>Bank Payments</span>
                </a>
                <a href="{{ route('payroll.reports.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                    <i class="fa-solid fa-chart-line w-5 text-rose-400"></i>
                    <span>Analytics & GL Export</span>
                </a>
                <a href="{{ route('payroll.settings.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                    <i class="fa-solid fa-gears w-5 text-slate-400"></i>
                    <span>Rules & Policies</span>
                </a>
            </nav>
        </div>
        <div class="p-4 border-t border-slate-800 text-xs text-slate-400">
            <p>SmartHCM Enterprise Engine</p>
            <p class="font-mono text-[10px] text-slate-400">Precision: Decimal(19,4)</p>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <header class="h-16 border-b border-slate-800 px-8 flex items-center justify-between bg-slate-950/50 backdrop-blur">
            <h2 class="text-lg font-semibold text-white">@yield('page_title', 'Payroll Overview')</h2>
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
                    Calculation Engine Active
                </span>
            </div>
        </header>

        <div class="p-8 flex-1">
            @yield('content')
        </div>
    </main>
</body>
</html>
