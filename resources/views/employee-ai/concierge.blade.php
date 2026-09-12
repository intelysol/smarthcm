<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI HR Concierge — Employee Self-Service Copilot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans antialiased flex flex-col">
    <!-- Header -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-500 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <i class="fa-solid fa-sparkles text-white text-base"></i>
                </div>
                <div>
                    <h1 class="font-bold text-base text-white leading-tight">AI HR Concierge</h1>
                    <p class="text-xs text-slate-400">Personalized Self-Service Copilot</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-950 text-emerald-400 border border-emerald-800">
                    <span class="w-1.5 h-1.5 mr-1 rounded-full bg-emerald-400"></span> Online
                </span>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 py-6 flex flex-col space-y-6">
        <!-- Quick Personalized Summary Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3 bg-slate-900 border border-slate-800 rounded-xl">
                <span class="text-[10px] uppercase font-bold text-slate-400">Annual Leave</span>
                <div class="mt-1 text-xl font-extrabold text-white">{{ $summary['annual_leave_balance'] ?? 14.5 }} <span class="text-xs font-normal text-slate-400">days</span></div>
            </div>
            <div class="p-3 bg-slate-900 border border-slate-800 rounded-xl">
                <span class="text-[10px] uppercase font-bold text-slate-400">Next Holiday</span>
                <div class="mt-1 text-sm font-bold text-indigo-400 truncate">{{ $summary['next_holiday']['name'] ?? 'Labor Day' }}</div>
            </div>
            <div class="p-3 bg-slate-900 border border-slate-800 rounded-xl">
                <span class="text-[10px] uppercase font-bold text-slate-400">Attendance</span>
                <div class="mt-1 text-xl font-extrabold text-emerald-400">{{ $summary['attendance_this_month']['on_time_days'] ?? 18 }} <span class="text-xs font-normal text-slate-400">on-time</span></div>
            </div>
            <div class="p-3 bg-slate-900 border border-slate-800 rounded-xl">
                <span class="text-[10px] uppercase font-bold text-slate-400">Recent Payslip</span>
                <div class="mt-1 text-xl font-extrabold text-white">${{ number_format($summary['recent_payslip']['net_salary'] ?? 4850, 0) }}</div>
            </div>
        </div>

        <!-- Chat Feed -->
        <div class="flex-1 bg-slate-900/60 border border-slate-800 rounded-2xl p-4 sm:p-6 space-y-4 overflow-y-auto min-h-[350px]">
            <!-- Welcome message from AI -->
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center flex-shrink-0 text-xs text-white">
                    <i class="fa-solid fa-sparkles"></i>
                </div>
                <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl rounded-tl-none p-4 max-w-[85%] text-sm text-slate-200 leading-relaxed shadow-sm">
                    <p>Good day! I am your <strong>AI HR Concierge</strong>. I can assist you with your leave entitlements, explain payslip deductions, guide you through HR policies, or prepare self-service requests for your review.</p>
                </div>
            </div>

            <!-- Proactive Suggestions Card -->
            <div class="ml-11 space-y-2">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Suggested for you</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($suggestions as $sug)
                    <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800 flex items-center justify-between">
                        <div class="pr-2">
                            <div class="text-xs font-bold text-slate-200">{{ $sug['title'] }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">{{ $sug['description'] }}</div>
                        </div>
                        <button class="px-2.5 py-1 text-[11px] font-semibold rounded bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 hover:bg-indigo-600/30 whitespace-nowrap">
                            {{ $sug['action_label'] }}
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Prompt Input Bar -->
        <div class="sticky bottom-4">
            <div class="relative flex items-center">
                <input type="text" placeholder="Ask anything about leave, attendance, payslips, or HR policies..." class="w-full bg-slate-900 border border-slate-700 rounded-2xl py-3.5 pl-4 pr-12 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-xl">
                <button class="absolute right-2.5 w-8 h-8 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-arrow-up text-xs"></i>
                </button>
            </div>
        </div>
    </main>
</body>
</html>
