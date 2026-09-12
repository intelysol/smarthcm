@extends('onboarding.layout')

@section('title', 'My Onboarding Portal — Flow HCM')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    @if(!$case)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-person-shelter"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900">No Active Onboarding Journey</h2>
            <p class="text-sm text-slate-500 mt-2">
                Your employee profile does not currently have an open preboarding or onboarding case.
            </p>
        </div>
    @else
        <!-- Welcome Hero -->
        <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-8 text-white shadow-lg">
            <div class="flex items-start justify-between">
                <div>
                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider bg-white/20 text-white backdrop-blur-sm">
                        Preboarding Journey
                    </span>
                    <h1 class="text-3xl font-extrabold mt-3">Welcome to Flow HCM, {{ $case->employee?->first_name }}!</h1>
                    <p class="text-sm text-emerald-100 mt-1 max-w-xl">
                        We are thrilled to have you join our team. Follow the checklist below to complete your digital joining, document verification, and prepare for Day One.
                    </p>
                </div>
                <div class="hidden sm:block text-right">
                    <div class="text-xs uppercase text-emerald-200 font-semibold tracking-wider">First Working Day</div>
                    <div class="text-2xl font-black text-white mt-1">{{ $case->start_date ? $case->start_date->format('M d, Y') : 'Upcoming' }}</div>
                    <div class="text-xs text-emerald-200 mt-0.5">{{ $case->employee?->department?->department_name ?? 'Engineering' }}</div>
                </div>
            </div>

            <!-- Progress Meter -->
            <div class="mt-8 pt-6 border-t border-white/20">
                <div class="flex justify-between items-center text-xs font-semibold mb-2">
                    <span>Overall Onboarding Completion</span>
                    <span class="font-mono text-sm">{{ $case->completion_percentage }}%</span>
                </div>
                <div class="w-full bg-black/20 rounded-full h-3 p-0.5 backdrop-blur-sm">
                    <div class="bg-white h-2 rounded-full transition-all duration-500" style="width: {{ $case->completion_percentage }}%"></div>
                </div>
            </div>
        </div>

        <!-- Task Checklist -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 space-y-6">
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Onboarding Action Items</h2>
                    <p class="text-xs text-slate-500">Tasks assigned directly to you for completion</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-md">
                    {{ $case->tasks->where('status', 'completed')->count() }} of {{ $case->tasks->count() }} Tasks Done
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($case->tasks as $task)
                    <div class="py-4 flex items-center justify-between hover:bg-slate-50/60 px-2 rounded-lg transition">
                        <div class="flex items-start space-x-3">
                            <div class="mt-0.5">
                                @if($task->status === 'completed')
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                                @elseif($task->status === 'blocked')
                                    <i class="fa-solid fa-circle-pause text-amber-500 text-lg"></i>
                                @else
                                    <i class="fa-regular fa-circle text-slate-300 text-lg"></i>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 {{ $task->status === 'completed' ? 'line-through text-slate-400' : '' }}">{{ $task->title }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $task->description ?? 'Complete designated action item before the designated due date.' }}</p>
                                <div class="mt-1 flex items-center space-x-3 text-[11px] text-slate-400">
                                    <span>Due: {{ $task->due_date ? $task->due_date->format('M d, Y') : 'Day 1' }}</span>
                                    <span>&bull;</span>
                                    <span class="capitalize">{{ str_replace('_', ' ', $task->task_type) }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            @if($task->status === 'completed')
                                <span class="px-2.5 py-1 rounded text-xs font-semibold bg-emerald-50 text-emerald-700">Done</span>
                            @else
                                <form action="/api/v1/me/onboarding/tasks/{{ $task->id }}/complete" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition">
                                        Mark Done &rarr;
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">No tasks currently assigned.</div>
                @endforelse
            </div>
        </div>

        <!-- First Day Schedule & Orientation -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 space-y-4">
            <h3 class="text-base font-bold text-slate-900">First Day Schedule & Orientation Overview</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Your first day will feature a guided orientation program designed to help you settle into the company smoothly.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="text-xs font-bold text-emerald-700">09:00 AM</div>
                    <div class="text-xs font-semibold text-slate-900 mt-1">HR Welcome & Security Badge</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">HQ Reception / Virtual Room 1</div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="text-xs font-bold text-emerald-700">11:00 AM</div>
                    <div class="text-xs font-semibold text-slate-900 mt-1">IT Hardware & Account Setup</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">IT Service Desk</div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="text-xs font-bold text-emerald-700">01:30 PM</div>
                    <div class="text-xs font-semibold text-slate-900 mt-1">Manager & Team Introduction</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Department Workspace</div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
