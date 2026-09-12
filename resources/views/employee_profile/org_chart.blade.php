@extends('employee_profile.layout')

@section('title', 'Organizational Hierarchy & Org Chart — Flow HCM')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-1">
                <span>Enterprise Hierarchy</span>
                <span>&bull;</span>
                <span>Reporting Relationships</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Organization Chart</h1>
            <p class="text-sm text-slate-500 mt-1">
                Explore reporting structures, direct reports, managerial spans of control, and organizational lines of command.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ route('employee_profile.directory') }}" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <i class="fa-solid fa-address-book mr-1.5 text-indigo-600"></i>Back to Directory
            </a>
        </div>
    </div>

    <!-- Org Chart Canvas Container -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 overflow-x-auto min-h-[500px]">
        <div class="text-center max-w-2xl mx-auto mb-8">
            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200">
                Top-Level Executive Leadership
            </span>
        </div>

        <div class="flex flex-col items-center space-y-12">
            @forelse($rootNodes as $root)
                <div class="flex flex-col items-center">
                    <!-- Root Node Card -->
                    <div class="w-72 bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-2xl p-5 shadow-lg border border-indigo-700/50 relative hover:scale-105 transition transform">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-full bg-indigo-500/30 border border-indigo-400/40 flex items-center justify-center font-bold text-white shadow">
                                {{ substr($root['name'], 0, 2) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-white">{{ $root['name'] }}</h3>
                                <p class="text-xs text-indigo-300">{{ $root['job_title'] }}</p>
                                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $root['department'] }}</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-indigo-800/60 flex items-center justify-between text-xs">
                            <span class="text-indigo-200">Direct Reports:</span>
                            <span class="px-2 py-0.5 rounded-full font-bold bg-indigo-500/40 text-indigo-100">
                                {{ $root['span_of_control'] }}
                            </span>
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('employee_profile.profile', $root['id']) }}" class="w-full inline-flex items-center justify-center px-3 py-1 rounded-lg text-xs font-semibold bg-white/10 hover:bg-white/20 text-white transition">
                                View Profile
                            </a>
                        </div>
                    </div>

                    @if($root['has_children'])
                        <!-- Connecting Branch Line -->
                        <div class="w-0.5 h-8 bg-indigo-300"></div>

                        <div class="text-xs text-indigo-600 font-semibold bg-indigo-50 px-3 py-1 rounded-full border border-indigo-200">
                            {{ $root['span_of_control'] }} Direct Reporting Teams
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center text-slate-400 py-12 text-sm">
                    No active employees found to construct the organizational hierarchy.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
