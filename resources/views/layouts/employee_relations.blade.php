<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Employee Relations & HR Case Management') - Flow HCM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans">
    <header class="bg-slate-900/95 border-b border-slate-800 backdrop-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                        <i class="fa-solid fa-scale-balanced text-white text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xl font-bold bg-gradient-to-r from-indigo-400 to-violet-200 bg-clip-text text-transparent">Flow HCM</span>
                        <span class="text-xs text-slate-400 block font-medium">Employee Relations & HR Case Platform</span>
                    </div>
                </div>

                <nav class="hidden md:flex space-x-1 text-sm font-medium">
                    <!-- ESS -->
                    <a href="{{ route('er.employee.dashboard') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">My Cases</a>
                    <a href="{{ route('er.employee.report') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">Submit Report</a>

                    <!-- Admin -->
                    <a href="{{ route('er.admin.dashboard') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">ER Dashboard</a>
                    <a href="{{ route('er.admin.cases') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">Case Queue</a>
                    <a href="{{ route('er.admin.intake') }}" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition">New Intake</a>
                    <a href="{{ route('er.anonymous.intake') }}" class="px-3 py-2 rounded-lg text-amber-300 hover:text-white hover:bg-slate-800 transition">Anonymous Portal</a>
                </nav>

                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 mr-1.5 animate-pulse"></span>
                        Confidential & Role Protected
                    </span>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <footer class="bg-slate-900/60 border-t border-slate-800/50 py-6 mt-12 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Flow HCM Enterprise — Employee Relations, Grievances, Investigations & HR Case Management</p>
    </footer>
</body>
</html>
