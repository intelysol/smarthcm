<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $category['title'] }} &bull; Help Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#F7F9FC] text-slate-800 min-h-screen flex flex-col">

    <!-- Top Header -->
    <header class="bg-[#1E3A5F] text-white border-b border-[#142A44] shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('help.index') }}" class="flex items-center space-x-2">
                <span class="w-9 h-9 rounded-lg bg-[#C9A227] text-[#1E3A5F] font-black text-lg flex items-center justify-center shadow">FEP</span>
                <span class="font-bold tracking-tight text-white text-sm">Help Center</span>
            </a>
            <a href="{{ route('help.index') }}" class="text-xs font-semibold text-slate-200 hover:text-white flex items-center">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> All Categories
            </a>
        </div>
    </header>

    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <!-- Breadcrumbs -->
        <nav class="flex items-center space-x-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('help.index') }}" class="hover:underline">Help Center</a>
            <span>&rsaquo;</span>
            <span class="text-slate-800 font-semibold">{{ $category['title'] }}</span>
        </nav>

        <!-- Category Banner -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-start space-x-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-[#1E3A5F] flex items-center justify-center text-2xl shrink-0">
                <i class="{{ $category['icon'] }}"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $category['title'] }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ $category['description'] }}</p>
                <div class="mt-2 text-[11px] font-semibold text-indigo-600">{{ count($articles) }} Articles Available</div>
            </div>
        </div>

        <!-- Articles List -->
        <div class="space-y-4">
            @forelse($articles as $art)
                <a href="{{ route('help.show', $art['slug']) }}" class="p-5 rounded-xl bg-white border border-slate-200 hover:border-[#1E3A5F] hover:shadow-md transition block space-y-2 group">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-slate-900 group-hover:text-[#1E3A5F] transition text-sm">{{ $art['title'] }}</h3>
                        <span class="text-[11px] text-slate-400 shrink-0"><i class="fa-regular fa-clock mr-1"></i>{{ $art['read_time'] }}</span>
                    </div>
                    <p class="text-xs text-slate-500">{{ $art['summary'] }}</p>
                    <div class="text-[11px] font-semibold text-[#1E3A5F] flex items-center pt-1">
                        <span>Read full guide &rarr;</span>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center bg-white rounded-xl border border-slate-200 text-slate-400 text-xs">
                    No articles currently published in this category.
                </div>
            @endforelse
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} Flow Enterprise Platform (Smart HCM).
    </footer>
</body>
</html>
