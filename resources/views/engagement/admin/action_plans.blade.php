@extends('layouts.engagement')

@section('title', 'Action Plans & Closed-Loop Followup')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-800/80 p-6 rounded-2xl border border-slate-700/60 shadow-xl">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-list-check text-pink-400"></i> Closed-Loop Action Plans
            </h1>
            <p class="text-slate-400 text-sm mt-1">Translate survey findings into concrete organizational and departmental improvement actions.</p>
        </div>
        <button onclick="document.getElementById('newPlanModal').classList.remove('hidden')" class="px-4 py-2 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-pink-500/25 transition">
            <i class="fa-solid fa-plus mr-1.5"></i> Create Action Plan
        </button>
    </div>

    <!-- Action Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($plans as $plan)
        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-6 hover:border-pink-500/40 transition flex flex-col justify-between shadow-lg space-y-4">
            <div>
                <div class="flex justify-between items-start">
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-slate-700 text-slate-300">
                        {{ ucfirst($plan->scope_type) }} Scope
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $plan->status === 'completed' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }}">
                        {{ ucfirst(str_replace('_', ' ', $plan->status)) }}
                    </span>
                </div>

                <h3 class="font-bold text-white text-base mt-2">{{ $plan->title }}</h3>
                <p class="text-xs text-slate-400 mt-1">{{ $plan->description }}</p>

                <!-- Tasks / Action Items -->
                <div class="mt-4 space-y-2">
                    <span class="text-xs font-bold text-slate-300">Tasks ({{ $plan->items?->count() ?? 0 }}):</span>
                    @foreach($plan->items ?? [] as $item)
                    <div class="p-2.5 bg-slate-900/60 border border-slate-700/40 rounded-xl flex justify-between items-center text-xs">
                        <span class="text-slate-200">{{ $item->title }}</span>
                        <span class="font-bold {{ $item->status === 'completed' ? 'text-emerald-400' : 'text-amber-400' }}">{{ $item->completion_percentage }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-slate-700/40 text-xs text-slate-400">
                <span>Owner: <strong class="text-slate-200">{{ $plan->owner ? "{$plan->owner->first_name} {$plan->owner->last_name}" : 'Unassigned' }}</strong></span>
                <span>Due: {{ $plan->due_date?->format('M d, Y') ?? 'No deadline' }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-slate-800/30 border border-slate-700/40 rounded-xl p-12 text-center text-slate-400">
            <i class="fa-solid fa-list-check text-pink-400 text-3xl mb-2"></i>
            <p>No action plans created yet. Build one from your recent survey insights!</p>
        </div>
        @endforelse
    </div>
</div>

<!-- New Plan Modal -->
<div id="newPlanModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex justify-between items-center">
            <h3 class="text-base font-bold text-white">Create Engagement Action Plan</h3>
            <button onclick="document.getElementById('newPlanModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ url('/api/v1/hcm/engagement/action-plans') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-slate-300 font-medium mb-1">Plan Title *</label>
                <input type="text" name="title" placeholder="e.g. Engineering Leadership & Wellbeing Followup" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-medium mb-1">Scope Type *</label>
                    <select name="scope_type" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500">
                        <option value="company">Company-wide</option>
                        <option value="department">Department</option>
                        <option value="location">Location</option>
                        <option value="team">Team</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 font-medium mb-1">Priority *</label>
                    <select name="priority" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500">
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Due Date</label>
                <input type="date" name="due_date" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500">
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-pink-500"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('newPlanModal').classList.add('hidden')" class="px-4 py-2 bg-slate-700 text-slate-300 rounded-xl font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-pink-500 hover:bg-pink-600 text-white font-bold rounded-xl shadow-lg shadow-pink-500/20">Create Plan</button>
            </div>
        </form>
    </div>
</div>
@endsection
