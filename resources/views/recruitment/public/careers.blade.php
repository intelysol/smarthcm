<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers & Open Positions — Flow HCM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col font-sans">
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Careers at Flow HCM</h1>
                    <p class="text-xs text-slate-500">Join our world-class enterprise team</p>
                </div>
            </div>
            <a href="/" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                &larr; Back to Platform
            </a>
        </div>
    </header>

    <main class="flex-grow max-w-6xl mx-auto w-full px-4 sm:px-6 py-10 space-y-8">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-3xl font-extrabold text-slate-900">Explore Open Opportunities</h2>
            <p class="text-sm text-slate-500 mt-2">
                Discover roles across engineering, design, operations, and leadership. Apply online in minutes.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($postings as $posting)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between text-xs font-medium text-slate-400 mb-2">
                            <span>{{ $posting->location_display ?? 'Remote / Hybrid' }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700">Full Time</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">{{ $posting->title }}</h3>
                        <p class="text-xs text-slate-600 mt-2 line-clamp-3">{{ $posting->summary ?? strip_tags($posting->description) }}</p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-xs text-slate-400">Closes {{ $posting->closes_at ? $posting->closes_at->format('M d, Y') : 'Open until filled' }}</span>
                        <a href="{{ route('careers.show', $posting->slug) }}" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            View & Apply &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12 bg-white rounded-xl border border-slate-200">
                    <i class="fa-solid fa-folder-open text-slate-300 text-4xl mb-3"></i>
                    <p class="text-sm font-semibold text-slate-700">No open positions currently listed.</p>
                    <p class="text-xs text-slate-400 mt-1">Please check back soon for upcoming opportunities.</p>
                </div>
            @endforelse
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} Flow HCM Enterprise Recruitment. Equal Opportunity Employer.
    </footer>
</body>
</html>
