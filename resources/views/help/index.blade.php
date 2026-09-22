<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Help Center &bull; Flow Enterprise Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand-navy: #1E3A5F;
            --brand-dark-navy: #142A44;
            --brand-gold: #C9A227;
        }
    </style>
</head>
<body class="bg-[#F7F9FC] text-slate-800 min-h-screen flex flex-col">

    <!-- Top Navigation Header -->
    <header class="bg-[#1E3A5F] text-white border-b border-[#142A44] shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('help.index') }}" class="flex items-center space-x-2">
                    <span class="w-9 h-9 rounded-lg bg-[#C9A227] text-[#1E3A5F] font-black text-lg flex items-center justify-center shadow">FEP</span>
                    <div>
                        <span class="font-bold tracking-tight text-white block text-sm">Flow Enterprise Platform</span>
                        <span class="text-[10px] text-slate-300 font-medium block">Documentation &amp; Help Center</span>
                    </div>
                </a>
            </div>
            <div class="flex items-center space-x-4 text-xs font-semibold">
                @auth
                    <a href="{{ url()->previous() }}" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition flex items-center">
                        <i class="fa-solid fa-arrow-left mr-1.5"></i> Return to Workplace
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-lg bg-[#C9A227] hover:bg-[#b08e20] text-[#1E3A5F] font-bold transition">
                        Sign In &rarr;
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Search Banner -->
    <section class="bg-gradient-to-b from-[#1E3A5F] to-[#142A44] text-white py-12 px-4">
        <div class="max-w-3xl mx-auto text-center space-y-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#C9A227]/20 text-[#C9A227] border border-[#C9A227]/30">
                <i class="fa-solid fa-circle-question mr-1.5"></i> Knowledge Base &amp; System Manuals
            </span>
            <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">How can we assist you today?</h1>
            <p class="text-sm text-slate-300">Find answers, administrator walkthroughs, workflow setup, and role-specific guides.</p>

            <!-- Search Form -->
            <form method="GET" action="{{ route('help.index') }}" class="max-w-xl mx-auto mt-6 relative">
                <input 
                    type="text" 
                    name="q" 
                    value="{{ $query }}" 
                    placeholder="Search articles, guides, errors, or keywords..." 
                    class="w-full pl-11 pr-4 py-3 rounded-xl bg-white text-slate-900 placeholder-slate-400 text-sm shadow-lg focus:outline-none focus:ring-2 focus:ring-[#C9A227]"
                >
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400 text-base"></i>
                @if($query)
                    <a href="{{ route('help.index') }}" class="absolute right-4 top-3 text-xs text-slate-400 hover:text-slate-600 font-bold">Clear</a>
                @endif
            </form>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">

        @if($query)
            <div>
                <h2 class="text-lg font-bold text-slate-900 mb-4">Search Results for "{{ $query }}" ({{ count($articles) }})</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($articles as $art)
                        <a href="{{ route('help.show', $art['slug']) }}" class="p-5 rounded-xl bg-white border border-slate-200 hover:border-[#1E3A5F] hover:shadow-md transition block space-y-2 group">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ $art['category'] }}</span>
                                <span class="text-[11px] text-slate-400"><i class="fa-regular fa-clock mr-1"></i>{{ $art['read_time'] }}</span>
                            </div>
                            <h3 class="font-bold text-slate-900 group-hover:text-[#1E3A5F] transition text-sm">{{ $art['title'] }}</h3>
                            <p class="text-xs text-slate-500 line-clamp-2">{{ $art['summary'] }}</p>
                        </a>
                    @empty
                        <div class="col-span-2 p-8 text-center bg-white rounded-xl border border-slate-200 text-slate-500 text-xs">
                            No articles matched your search query. Try exploring the categories below.
                        </div>
                    @endforelse
                </div>
            </div>
        @else

            <!-- Browse by Role / Persona -->
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-users text-[#1E3A5F]"></i> Browse by Role
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 text-xs">
                    <a href="{{ route('help.show', 'platform-super-admin-guide') }}" class="p-3.5 rounded-xl bg-white border border-slate-200 hover:border-[#1E3A5F] hover:shadow transition flex flex-col items-center text-center group">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition">
                            <i class="fa-solid fa-server"></i>
                        </div>
                        <span class="font-bold text-slate-800 group-hover:text-[#1E3A5F]">Super Admin</span>
                        <span class="text-[10px] text-slate-400 mt-0.5">Platform Operations</span>
                    </a>

                    <a href="{{ route('help.show', 'tenant-administrator-guide') }}" class="p-3.5 rounded-xl bg-white border border-slate-200 hover:border-[#1E3A5F] hover:shadow transition flex flex-col items-center text-center group">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition">
                            <i class="fa-solid fa-building-user"></i>
                        </div>
                        <span class="font-bold text-slate-800 group-hover:text-[#1E3A5F]">Tenant Admin</span>
                        <span class="text-[10px] text-slate-400 mt-0.5">Org &amp; Users</span>
                    </a>

                    <a href="{{ route('help.show', 'hr-administrator-guide') }}" class="p-3.5 rounded-xl bg-white border border-slate-200 hover:border-[#1E3A5F] hover:shadow transition flex flex-col items-center text-center group">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <span class="font-bold text-slate-800 group-hover:text-[#1E3A5F]">HR Admin</span>
                        <span class="text-[10px] text-slate-400 mt-0.5">HCM Operations</span>
                    </a>

                    <a href="{{ route('help.show', 'people-manager-workbench-guide') }}" class="p-3.5 rounded-xl bg-white border border-slate-200 hover:border-[#1E3A5F] hover:shadow transition flex flex-col items-center text-center group">
                        <div class="w-10 h-10 rounded-lg bg-cyan-50 text-cyan-700 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition">
                            <i class="fa-solid fa-user-group"></i>
                        </div>
                        <span class="font-bold text-slate-800 group-hover:text-[#1E3A5F]">People Manager</span>
                        <span class="text-[10px] text-slate-400 mt-0.5">Team Approvals</span>
                    </a>

                    <a href="{{ route('help.show', 'employee-self-service-guide') }}" class="p-3.5 rounded-xl bg-white border border-slate-200 hover:border-[#1E3A5F] hover:shadow transition flex flex-col items-center text-center group">
                        <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <span class="font-bold text-slate-800 group-hover:text-[#1E3A5F]">Employee</span>
                        <span class="text-[10px] text-slate-400 mt-0.5">Self-Service Portal</span>
                    </a>
                </div>
            </div>

            <!-- Categories Grid -->
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-[#1E3A5F]"></i> Knowledge Categories
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($categories as $cat)
                        <a href="{{ route('help.category', $cat['key']) }}" class="p-5 rounded-2xl bg-white border border-slate-200 hover:border-[#1E3A5F] hover:shadow-md transition flex flex-col justify-between space-y-3 group">
                            <div class="space-y-2">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-[#1E3A5F] group-hover:bg-[#1E3A5F] group-hover:text-[#C9A227] flex items-center justify-center text-lg transition">
                                    <i class="{{ $cat['icon'] }}"></i>
                                </div>
                                <h3 class="font-bold text-sm text-slate-900 group-hover:text-[#1E3A5F] transition">{{ $cat['title'] }}</h3>
                                <p class="text-xs text-slate-500">{{ $cat['description'] }}</p>
                            </div>
                            <div class="text-[11px] font-bold text-[#1E3A5F] flex items-center group-hover:translate-x-1 transition">
                                <span>Browse Articles</span>
                                <i class="fa-solid fa-chevron-right ml-1.5 text-[9px]"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Popular / Featured Guides -->
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-star text-[#C9A227]"></i> Essential Documentation Guides
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    @foreach(array_slice($articles, 0, 6) as $art)
                        <a href="{{ route('help.show', $art['slug']) }}" class="p-5 rounded-xl bg-white border border-slate-200 hover:border-[#1E3A5F] hover:shadow transition block space-y-2 group">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 bg-slate-100 px-2 py-0.5 rounded">{{ $art['category'] }}</span>
                                <span class="text-[11px] text-slate-400"><i class="fa-regular fa-clock mr-1"></i>{{ $art['read_time'] }}</span>
                            </div>
                            <h3 class="font-bold text-slate-900 group-hover:text-[#1E3A5F] transition text-sm">{{ $art['title'] }}</h3>
                            <p class="text-xs text-slate-500 line-clamp-2">{{ $art['summary'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>

        @endif

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500 space-y-1">
        <p>&copy; {{ date('Y') }} Flow Enterprise Platform (Smart HCM). All rights reserved.</p>
        <p>Authoritative Multi-Tenant Architecture &bull; ISO 27001 &bull; SOC 2 Type II Certified</p>
    </footer>

</body>
</html>
