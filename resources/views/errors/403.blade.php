<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Restricted — SmartHCM Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#F7F9FC] text-[#1F2937] min-h-screen flex items-center justify-center p-4 font-sans antialiased">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200 p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center mx-auto mb-5 text-amber-600">
            <i class="fa-solid fa-lock text-2xl"></i>
        </div>
        
        <h1 class="text-xl font-black text-[#1E3A5F] mb-2 tracking-tight">Access Restricted</h1>
        <p class="text-sm text-slate-600 mb-6 leading-relaxed">
            {{ $message ?? 'You do not have authorization to access this workspace or resource. Access attempts are logged and monitored under enterprise governance.' }}
        </p>

        <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 mb-6 text-xs text-left text-slate-600 space-y-1">
            <div class="flex justify-between">
                <span class="font-semibold text-slate-700">Account:</span>
                <span class="font-mono text-slate-900">{{ auth()->user()->email ?? 'Authenticated User' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold text-slate-700">Tenant:</span>
                <span class="font-mono text-slate-900">{{ auth()->user()->tenant?->name ?? 'Enterprise Environment' }}</span>
            </div>
        </div>

        <div class="space-y-3">
            @php
                $fallbackRoute = isset($fallbackWorkspace) ? route($fallbackWorkspace->dashboardRoute()) : (Route::has('portal.dashboard') ? route('portal.dashboard') : '/');
            @endphp
            <a href="{{ $fallbackRoute }}" class="block w-full py-2.5 px-4 rounded-xl bg-[#1E3A5F] hover:bg-[#142A44] text-white font-semibold text-xs shadow-md shadow-slate-900/10 transition">
                <i class="fa-solid fa-arrow-left mr-2"></i> Return to Authorized Workspace
            </a>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-2 text-xs text-slate-500 hover:text-slate-800 transition font-medium">
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</body>
</html>
