@extends('self-service.layout')

@section('title', 'HR Service Catalog')

@section('content')
<div class="space-y-8">
    <div class="text-center max-w-2xl mx-auto space-y-2">
        <h1 class="text-3xl font-bold text-white tracking-tight">HR Service Catalog</h1>
        <p class="text-slate-400 text-sm">Select from our comprehensive list of HR services, certificate generators, profile change requests, and team support options.</p>
    </div>

    <!-- Category Groups -->
    @foreach($categories as $category)
    <div class="space-y-4">
        <div class="flex items-center space-x-3 border-b border-slate-800 pb-2">
            <div class="w-7 h-7 rounded bg-teal-500/10 text-teal-400 flex items-center justify-center">
                <i class="fa-solid {{ $category->icon ?? 'fa-folder' }} text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-white">{{ $category->name }}</h2>
            <span class="text-xs text-slate-500">({{ $category->services->count() }} services)</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($category->services as $service)
            <a href="{{ route('self-service.catalog.show', $service) }}" class="bg-slate-900 border border-slate-800 hover:border-teal-500/50 rounded-xl p-5 shadow-sm transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-mono text-teal-400">{{ $service->service_code }}</span>
                        @if($service->is_popular)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            Popular
                        </span>
                        @endif
                    </div>
                    <h3 class="text-base font-bold text-white group-hover:text-teal-300 transition">{{ $service->name }}</h3>
                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $service->description }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-500">
                    <span><i class="fa-regular fa-clock mr-1"></i> SLA: {{ round(($service->slaPolicy?->resolution_time_minutes ?? 2880) / 1440, 1) }} days</span>
                    <span class="text-teal-400 font-medium group-hover:translate-x-1 transition">Request &rarr;</span>
                </div>
            </a>
            @empty
            <div class="col-span-3 text-sm text-slate-500 py-4">No active services in this category.</div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>
@endsection
