@extends('portal.layout')

@section('title', 'My Growth')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-xl font-bold text-white tracking-wide">My Growth &amp; Development</h1>
        <p class="text-xs text-slate-400 mt-1">Track your performance objectives, ongoing reviews, enrolled learning modules, and skill certifications.</p>
    </div>

    <!-- 2 Cols: Performance & Learning -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Performance Section -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white flex items-center">
                    <i class="fa-solid fa-bullseye text-amber-400 mr-2"></i> Performance Reviews &amp; Goals
                </h3>
            </div>

            <div class="space-y-3 text-xs">
                @forelse($reviews as $rev)
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-white">{{ $rev->cycle_name ?? 'Annual Review Cycle' }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Status: <span class="text-amber-400 font-semibold">{{ strtoupper($rev->status ?? 'PENDING') }}</span></div>
                        </div>
                        <span class="px-3 py-1 rounded bg-indigo-600 text-white font-medium text-[11px]">
                            View Review
                        </span>
                    </div>
                @empty
                    <div class="p-6 rounded-xl bg-slate-950 border border-slate-800/80 text-center text-slate-400">
                        <i class="fa-regular fa-calendar-check text-2xl text-slate-600 mb-2 block"></i>
                        No active performance review cycles currently requiring action.
                    </div>
                @endforelse

                <!-- Sample Goal -->
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-200">Department Operational Excellence</span>
                        <span class="text-emerald-400 font-bold">75%</span>
                    </div>
                    <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full" style="width: 75%"></div>
                    </div>
                    <p class="text-[11px] text-slate-400">Key Result: Optimize cross-team response times and documentation.</p>
                </div>
            </div>
        </div>

        <!-- Learning Section -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold text-white flex items-center">
                    <i class="fa-solid fa-graduation-cap text-indigo-400 mr-2"></i> Assigned Learning &amp; Courses
                </h3>
            </div>

            <div class="space-y-3 text-xs">
                @forelse($courses as $c)
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-white">{{ $c->title ?? 'Compliance Training' }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Due: {{ $c->due_date ?? 'Next week' }}</div>
                        </div>
                        <span class="px-3 py-1 rounded bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-[11px] cursor-pointer">
                            Launch
                        </span>
                    </div>
                @empty
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-white">Enterprise Data Security &amp; ISO 27001</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Mandatory annual compliance refresher</div>
                        </div>
                        <span class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-bold text-[11px]">
                            Launch Module
                        </span>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-white">Workforce Collaboration &amp; Leadership</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Professional growth path &bull; 4 modules remaining</div>
                        </div>
                        <span class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium text-[11px]">
                            Resume
                        </span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
