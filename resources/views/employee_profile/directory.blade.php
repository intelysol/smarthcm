@extends('employee_profile.layout')

@section('title', 'Employee Directory & People Search — Flow HCM')

@section('content')
<div class="space-y-8">

    <!-- Page Title & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-1">
                <span>Workforce Identity</span>
                <span>&bull;</span>
                <span>People Search & Navigation Layer</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Employee Directory</h1>
            <p class="text-sm text-slate-500 mt-1">
                Discover teammates, browse departments, view reporting structures, and search enterprise workforce records.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ route('employee_profile.org_chart') }}" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <i class="fa-solid fa-sitemap mr-1.5 text-indigo-600"></i>View Org Chart
            </a>
        </div>
    </div>

    <!-- AI People Search Bar -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-950 rounded-2xl p-5 text-white shadow-md border border-indigo-700/40 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-300">
                <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-bold text-white">AI People Search & Skill Finder</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-indigo-500/30 text-indigo-200">Advisory</span>
                </div>
                <p class="text-xs text-slate-300 mt-0.5">Try searching naturally: <em>"Software engineers in Karachi with PHP skills"</em></p>
            </div>
        </div>
        <div class="w-full md:w-auto text-xs text-indigo-300/80">
            <i class="fa-solid fa-shield-check mr-1 text-indigo-400"></i>Safety Guardrails Active &bull; Minimum necessary data
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <form method="GET" action="{{ route('employee_profile.directory') }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Search Keyword</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, ID, email, title..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Department</label>
            <select name="department_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') === $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Branch / Office</label>
            <select name="branch_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ ($filters['branch_id'] ?? '') === $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end space-x-2">
            <button type="submit" class="w-full px-4 py-2 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition">
                <i class="fa-solid fa-magnifying-glass mr-1.5"></i>Search Directory
            </button>
            <a href="{{ route('employee_profile.directory') }}" class="px-3 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition">
                Reset
            </a>
        </div>
    </form>

    <!-- Employee Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($employees as $emp)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow">
                            {{ substr($emp->first_name, 0, 1) }}{{ substr($emp->last_name, 0, 1) }}
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                            @if($emp->employment_status === 'active') bg-emerald-50 text-emerald-700
                            @elseif($emp->employment_status === 'notice_period') bg-amber-50 text-amber-700
                            @else bg-slate-100 text-slate-600 @endif">
                            {{ $emp->employment_status }}
                        </span>
                    </div>

                    <div class="mt-3">
                        <h3 class="text-base font-bold text-slate-900 leading-snug">{{ $emp->fullName() }}</h3>
                        <p class="text-xs font-semibold text-indigo-600 mt-0.5">{{ $emp->designation?->name ?? 'Team Member' }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $emp->department?->name ?? 'General' }} &bull; {{ $emp->branch?->name ?? 'HQ' }}</p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 space-y-1 text-xs text-slate-600">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-id-badge text-slate-400 w-4"></i>
                            <span class="font-mono text-[11px]">{{ $emp->employee_code ?? $emp->employee_number }}</span>
                        </div>
                        @if($emp->official_email)
                            <div class="flex items-center space-x-2 truncate">
                                <i class="fa-solid fa-envelope text-slate-400 w-4"></i>
                                <span class="truncate">{{ $emp->official_email }}</span>
                            </div>
                        @endif
                        @if($emp->reportingManager)
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-user-tie text-slate-400 w-4"></i>
                                <span>Mgr: {{ $emp->reportingManager->first_name }} {{ $emp->reportingManager->last_name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-5 pt-3 border-t border-slate-100">
                    <a href="{{ route('employee_profile.profile', $emp->id) }}" class="w-full inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-50 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 border border-slate-200 transition">
                        View Full Profile <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400 text-sm">
                No employees found matching the search criteria.
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $employees->links() }}
    </div>

</div>
@endsection
