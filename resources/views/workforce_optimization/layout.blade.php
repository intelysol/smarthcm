<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SmartHCM - Workforce Optimization')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        <header class="bg-indigo-900 text-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl font-black tracking-wider text-indigo-300">SmartHCM</span>
                    <span class="text-sm uppercase tracking-widest text-indigo-200 border-l border-indigo-700 pl-3">Optimization & Decision Intelligence</span>
                </div>
                <nav class="flex space-x-4 text-sm font-medium">
                    <a href="{{ route('workforce_optimization.dashboard') }}" class="hover:text-indigo-200">Dashboard</a>
                    <a href="{{ route('workforce_optimization.opportunities') }}" class="hover:text-indigo-200">Opportunities</a>
                    <a href="{{ route('workforce_optimization.recommendations') }}" class="hover:text-indigo-200">Recommendations</a>
                    <a href="{{ route('workforce_optimization.scenarios') }}" class="hover:text-indigo-200">Scenarios</a>
                    <a href="{{ route('workforce_optimization.outcomes') }}" class="hover:text-indigo-200">Outcomes</a>
                </nav>
            </div>
        </header>

        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @yield('content')
        </main>

        <footer class="bg-white border-t border-gray-200 py-4 text-center text-xs text-gray-500">
            &copy; 2026 SmartHCM Enterprise. Human-in-the-Loop Workforce Optimization & Decision Intelligence.
        </footer>
    </div>
</body>
</html>
