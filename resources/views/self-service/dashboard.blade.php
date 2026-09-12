@extends('self-service.layout')

@section('title', 'Employee Self-Service (ESS) Home')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Welcome back, {{ $employee->first_name }}</h1>
            <p class="text-sm text-slate-400">{{ $employee->designation?->designation_name ?? 'Employee' }} &bull; {{ $employee->department?->department_name ?? 'Corporate' }} &bull; ID: {{ $employee->employee_code ?? $employee->employee_number }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('self-service.catalog.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium bg-teal-600 hover:bg-teal-500 text-white shadow-lg shadow-teal-600/30 transition">
                <i class="fa-solid fa-plus mr-2"></i> Request HR Service
            </a>
        </div>
    </div>

    <!-- Quick Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <a href="{{ route('self-service.catalog.index') }}" class="bg-slate-900 border border-slate-800 hover:border-teal-500/50 rounded-xl p-5 shadow-sm transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Service Catalog</span>
                <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-teal-400 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-lg font-bold text-white">HR Catalog</div>
                <span class="text-xs text-teal-400">Letters, certificates, inquiries &rarr;</span>
            </div>
        </a>

        <a href="{{ route('self-service.requests.index') }}" class="bg-slate-900 border border-slate-800 hover:border-amber-500/50 rounded-xl p-5 shadow-sm transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">My Requests</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-ticket"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ $open_requests_count ?? 0 }}</div>
                <span class="text-xs text-amber-400">Open / in progress tickets &rarr;</span>
            </div>
        </a>

        <a href="{{ route('self-service.knowledge.index') }}" class="bg-slate-900 border border-slate-800 hover:border-sky-500/50 rounded-xl p-5 shadow-sm transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Knowledge Base</span>
                <div class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-book-open"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-lg font-bold text-white">Policies &amp; FAQs</div>
                <span class="text-xs text-sky-400">Find quick answers &rarr;</span>
            </div>
        </a>

        <a href="{{ route('self-service.announcements.index') }}" class="bg-slate-900 border border-slate-800 hover:border-indigo-500/50 rounded-xl p-5 shadow-sm transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Company Notices</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold text-white">{{ count($announcements ?? []) }}</div>
                <span class="text-xs text-indigo-400">Active announcements &rarr;</span>
            </div>
        </a>
    </div>

    <!-- Main Grid: Recent Requests & Popular Services -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Requests -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-white">My Active Requests</h2>
                <a href="{{ route('self-service.requests.index') }}" class="text-xs text-teal-400 hover:text-teal-300 font-medium">View All &rarr;</a>
            </div>
            <div class="divide-y divide-slate-800">
                @forelse($recent_requests as $req)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-slate-200">{{ $req->subject }}</div>
                        <div class="text-xs text-slate-400 font-mono">{{ $req->request_number }} &bull; {{ $req->service?->name }}</div>
                    </div>
                    <div class="text-right">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                            {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                        </span>
                        <div class="text-xs text-slate-500 mt-1">{{ $req->created_at?->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-sm text-slate-500">No active HR requests.</div>
                @endforelse
            </div>
        </div>

        <!-- Popular Services -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-white">Popular HR Services</h2>
                <a href="{{ route('self-service.catalog.index') }}" class="text-xs text-teal-400 hover:text-teal-300 font-medium">Full Catalog &rarr;</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @forelse($popular_services as $svc)
                <a href="{{ route('self-service.catalog.show', $svc) }}" class="p-3 rounded-lg bg-slate-850 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 transition flex items-start space-x-3">
                    <div class="w-8 h-8 rounded bg-teal-500/10 text-teal-400 flex items-center justify-center mt-0.5 shrink-0">
                        <i class="fa-solid {{ $svc->icon ?? 'fa-file-lines' }} text-sm"></i>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-white">{{ $svc->name }}</div>
                        <div class="text-xs text-slate-400 truncate max-w-[180px]">{{ $svc->description }}</div>
                    </div>
                </a>
                @empty
                <div class="text-center py-6 text-sm text-slate-500 col-span-2">No services configured.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
