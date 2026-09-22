<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article['title'] }} &bull; Help Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .help-article-content h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .help-article-content p {
            margin-bottom: 1rem;
            line-height: 1.65;
            color: #334155;
            font-size: 0.875rem;
        }
        .help-article-content ul, .help-article-content ol {
            margin-left: 1.5rem;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            color: #334155;
        }
        .help-article-content ul {
            list-style-type: disc;
        }
        .help-article-content ol {
            list-style-type: decimal;
        }
        .help-article-content li {
            margin-bottom: 0.35rem;
        }
        .help-article-content code {
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 0.15rem 0.35rem;
            border-radius: 0.25rem;
            font-size: 0.8125rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .help-article-content pre {
            background-color: #0f172a;
            color: #f8fafc;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin-bottom: 1.25rem;
            font-size: 0.8125rem;
        }
        .help-article-content pre code {
            background-color: transparent;
            color: inherit;
            padding: 0;
        }
    </style>
</head>
<body class="bg-[#F7F9FC] text-slate-800 min-h-screen flex flex-col">

    <!-- Top Header -->
    <header class="bg-[#1E3A5F] text-white border-b border-[#142A44] shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('help.index') }}" class="flex items-center space-x-2">
                <span class="w-9 h-9 rounded-lg bg-[#C9A227] text-[#1E3A5F] font-black text-lg flex items-center justify-center shadow">FEP</span>
                <span class="font-bold tracking-tight text-white text-sm">Help Center</span>
            </a>
            <div class="flex items-center space-x-4">
                <a href="{{ route('help.category', $category['key'] ?? $article['category']) }}" class="text-xs font-semibold text-slate-200 hover:text-white flex items-center">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to {{ $category['title'] }}
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Breadcrumbs -->
        <nav class="flex items-center space-x-2 text-xs text-slate-500 font-medium mb-6">
            <a href="{{ route('help.index') }}" class="hover:underline">Help Center</a>
            <span>&rsaquo;</span>
            <a href="{{ route('help.category', $category['key'] ?? $article['category']) }}" class="hover:underline">{{ $category['title'] }}</a>
            <span>&rsaquo;</span>
            <span class="text-slate-800 font-semibold truncate max-w-xs sm:max-w-md">{{ $article['title'] }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <!-- Main Article Body -->
            <article class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                <!-- Article Header -->
                <div class="border-b border-slate-100 pb-6">
                    <div class="flex items-center space-x-2 mb-3">
                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 uppercase tracking-wide">
                            <i class="{{ $category['icon'] ?? 'fa-solid fa-book' }} mr-1"></i> {{ $category['title'] }}
                        </span>
                        <span class="text-[11px] text-slate-400">&bull;</span>
                        <span class="text-[11px] text-slate-500 font-medium">
                            <i class="fa-regular fa-clock mr-1"></i> {{ $article['read_time'] }} read
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-snug">
                        {{ $article['title'] }}
                    </h1>
                    <p class="text-sm text-slate-500 mt-2">
                        {{ $article['summary'] }}
                    </p>
                </div>

                <!-- Article Content -->
                <div class="help-article-content prose prose-slate max-w-none">
                    {!! $article['content'] !!}
                </div>

                <!-- Footer feedback / help box -->
                <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50 p-4 rounded-xl">
                    <div>
                        <div class="text-xs font-bold text-slate-900">Need further assistance?</div>
                        <div class="text-[11px] text-slate-500">Refer to system diagnostics or consult your Platform Super Administrator.</div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('help.category', 'troubleshooting') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 transition shadow-sm">
                            Troubleshooting
                        </a>
                        <a href="{{ route('help.category', 'faq') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#1E3A5F] text-white hover:bg-[#142A44] transition shadow-sm">
                            View FAQ
                        </a>
                    </div>
                </div>
            </article>

            <!-- Sidebar -->
            <aside class="space-y-6">
                <!-- Related Articles in this category -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center">
                        <i class="fa-solid fa-list-check mr-2 text-indigo-500"></i> In this Category
                    </h3>
                    @if(count($relatedArticles) > 0)
                        <div class="space-y-2">
                            @foreach($relatedArticles as $rel)
                                <a href="{{ route('help.show', $rel['slug']) }}" class="block p-3 rounded-lg hover:bg-slate-50 border border-transparent hover:border-slate-200 transition">
                                    <div class="text-xs font-bold text-slate-800 hover:text-indigo-600 transition">{{ $rel['title'] }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center">
                                        <i class="fa-regular fa-clock mr-1"></i> {{ $rel['read_time'] }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-xs text-slate-400 italic">No other articles in this section.</div>
                    @endif
                </div>

                <!-- Quick Help Navigator -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center">
                        <i class="fa-solid fa-compass mr-2 text-amber-500"></i> Explore Guides
                    </h3>
                    <div class="space-y-1.5">
                        <a href="{{ route('help.category', 'getting-started') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium text-slate-700 hover:bg-slate-50 transition">
                            <span class="flex items-center"><i class="fa-solid fa-rocket w-5 text-indigo-500"></i> Getting Started</span>
                            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                        </a>
                        <a href="{{ route('help.category', 'super-admin') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium text-slate-700 hover:bg-slate-50 transition">
                            <span class="flex items-center"><i class="fa-solid fa-server w-5 text-amber-500"></i> Super Admin</span>
                            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                        </a>
                        <a href="{{ route('help.category', 'tenant-admin') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium text-slate-700 hover:bg-slate-50 transition">
                            <span class="flex items-center"><i class="fa-solid fa-building-user w-5 text-blue-500"></i> Tenant Admin</span>
                            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                        </a>
                        <a href="{{ route('help.category', 'hr-admin') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium text-slate-700 hover:bg-slate-50 transition">
                            <span class="flex items-center"><i class="fa-solid fa-users-gear w-5 text-emerald-500"></i> HR Admin</span>
                            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                        </a>
                        <a href="{{ route('help.category', 'manager') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium text-slate-700 hover:bg-slate-50 transition">
                            <span class="flex items-center"><i class="fa-solid fa-user-group w-5 text-cyan-500"></i> People Manager</span>
                            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                        </a>
                        <a href="{{ route('help.category', 'employee') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium text-slate-700 hover:bg-slate-50 transition">
                            <span class="flex items-center"><i class="fa-solid fa-user-tie w-5 text-purple-500"></i> Employee Self-Service</span>
                            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-12 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500">
            Smart HCM / Flow Enterprise Platform &bull; Knowledge Base &amp; System Manual
        </div>
    </footer>
</body>
</html>
