<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Expense & Travel') - Flow HCM Enterprise</title>
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
                    <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-500/20">
                        <i class="fa-solid fa-plane-departure"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold bg-gradient-to-r from-indigo-400 to-sky-400 bg-clip-text text-transparent">Flow HCM</span>
                        <span class="text-xs text-slate-400 ml-2 font-mono">EXPENSES &amp; TRAVEL 2.38</span>
                    </div>
                </div>

                <nav class="hidden lg:flex items-center space-x-1">
                    <a href="{{ route('expenses.dashboard') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-chart-pie mr-1 text-slate-400"></i> Dashboard
                    </a>
                    <a href="{{ route('expenses.travel.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-suitcase-rolling mr-1 text-slate-400"></i> Travel
                    </a>
                    <a href="{{ route('expenses.advances.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-money-bill-transfer mr-1 text-slate-400"></i> Advances
                    </a>
                    <a href="{{ route('expenses.claims.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-file-invoice-dollar mr-1 text-slate-400"></i> Claims
                    </a>
                    <a href="{{ route('expenses.reimbursements.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-hand-holding-dollar mr-1 text-slate-400"></i> Payables
                    </a>
                    <a href="{{ route('expenses.cards.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-credit-card mr-1 text-slate-400"></i> Cards
                    </a>
                    <a href="{{ route('expenses.policies.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-shield-halved mr-1 text-slate-400"></i> Policies
                    </a>
                    <a href="{{ route('expenses.accounting.index') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium hover:bg-slate-800 text-slate-300 hover:text-white transition">
                        <i class="fa-solid fa-book-journal-whills mr-1 text-slate-400"></i> GL Export
                    </a>
                    <a href="{{ route('expenses.ai.advisor') }}" class="px-2.5 py-1.5 rounded-md text-xs font-medium bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 hover:text-indigo-200 border border-indigo-500/30 transition">
                        <i class="fa-solid fa-brain mr-1 text-indigo-400"></i> AI Advisor
                    </a>
                </nav>

                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-900/60 text-indigo-300 border border-indigo-700/50">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span> HCM 2.38 Active
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
        Flow HCM Enterprise &bull; Epic 2.38 Employee Expense, Travel, Advances &amp; Reimbursement Platform &bull; Multi-Tenant &amp; Audited
    </footer>
</body>
</html>
