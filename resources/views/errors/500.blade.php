<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal System Error (500) — SmartHCM Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/corporate-tokens.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#F7F9FC] text-[#1F2937] min-h-screen flex items-center justify-center p-4 font-sans antialiased">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200 p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-rose-50 border border-rose-200 flex items-center justify-center mx-auto mb-5 text-rose-600">
            <i class="fa-solid fa-triangle-exclamation text-2xl" aria-hidden="true"></i>
        </div>
        
        <span class="text-xs font-bold text-rose-600 uppercase tracking-wider font-mono">Status 500</span>
        <h1 class="text-2xl font-black text-[#1E3A5F] mt-1 mb-2 tracking-tight">System Encountered an Error</h1>
        <p class="text-xs text-slate-600 mb-4 leading-relaxed">
            The service was unable to fulfill your request at this moment. Our operational monitoring system has recorded this incident for review.
        </p>

        @php
            $errorRef = 'ERR-' . strtoupper(substr(md5(microtime() . (auth()->id() ?? 'anon')), 0, 10));
        @endphp

        <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 mb-6 text-xs text-left text-slate-600 space-y-1">
            <div class="flex justify-between">
                <span class="font-semibold text-slate-700">Incident Reference:</span>
                <span class="font-mono text-slate-900 font-bold">{{ $errorRef }}</span>
            </div>
            <div class="flex justify-between">
                <span class="font-semibold text-slate-700">Timestamp:</span>
                <span class="font-mono text-slate-700">{{ now()->toIso8601String() }}</span>
            </div>
        </div>

        <div class="space-y-3">
            <button type="button" onclick="window.location.reload()" class="btn-primary w-full py-2.5 px-4 rounded-xl inline-flex items-center justify-center text-xs font-semibold shadow-md shadow-slate-900/10 transition">
                <i class="fa-solid fa-arrow-rotate-right mr-2" aria-hidden="true"></i> Try Again
            </button>

            <a href="mailto:support@smarthcm.internal?subject=Incident%20Report%20{{ $errorRef }}" class="btn-secondary w-full py-2 text-xs font-medium inline-flex items-center justify-center">
                <i class="fa-regular fa-envelope mr-1.5" aria-hidden="true"></i> Contact Support
            </a>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 text-[11px] text-slate-400">
            SmartHCM Enterprise Experience &bull; Incident: {{ $errorRef }}
        </div>
    </div>
</body>
</html>
