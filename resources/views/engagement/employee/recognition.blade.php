@extends('layouts.engagement')

@section('title', 'Recognition & Appreciation Wall')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-800/80 p-6 rounded-2xl border border-slate-700/60 shadow-xl">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-award text-amber-400"></i> Recognition Wall
            </h1>
            <p class="text-slate-400 text-sm mt-1">Celebrate peers, acknowledge extraordinary effort, and reinforce company values.</p>
        </div>
        <button onclick="document.getElementById('kudosModal').classList.remove('hidden')" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-amber-500/25 transition">
            <i class="fa-solid fa-gift mr-1.5"></i> Give Recognition
        </button>
    </div>

    <!-- Recognition Stream -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($recognitions as $rec)
        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-5 hover:border-amber-500/40 transition flex flex-col justify-between shadow-lg">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        <i class="fa-solid fa-star text-[10px] mr-1"></i> {{ $rec->value_tag ?? 'Core Values' }}
                    </span>
                    <span class="text-[11px] text-slate-500">{{ $rec->created_at?->diffForHumans() }}</span>
                </div>

                <div class="flex items-center gap-2 mb-2">
                    <div class="w-7 h-7 rounded-full bg-slate-700 text-slate-200 flex items-center justify-center text-xs font-bold">
                        {{ substr($rec->sender?->first_name ?? 'U', 0, 1) }}
                    </div>
                    <span class="text-xs text-slate-300"><strong>{{ $rec->sender?->first_name }} {{ $rec->sender?->last_name }}</strong> recognized</span>
                </div>

                <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-700/40 my-2">
                    <span class="text-xs font-bold text-white block mb-0.5">{{ $rec->title }}</span>
                    <p class="text-xs text-slate-300 italic">"{{ $rec->message }}"</p>
                </div>
            </div>

            <div class="flex justify-between items-center pt-3 border-t border-slate-700/40 mt-3 text-xs text-slate-400">
                <span>To: <strong class="text-white">{{ $rec->recipient?->first_name }} {{ $rec->recipient?->last_name }}</strong></span>
                <span class="flex items-center gap-1 text-pink-400 font-semibold">
                    <i class="fa-solid fa-heart"></i> {{ $rec->likes_count }}
                </span>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-slate-800/30 border border-slate-700/40 rounded-xl p-12 text-center text-slate-400">
            <i class="fa-solid fa-award text-amber-400 text-3xl mb-2"></i>
            <p>No recognition posts yet. Give kudos to your teammates!</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Kudos Modal -->
<div id="kudosModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-award text-amber-400"></i> Recognize a Colleague
            </h3>
            <button onclick="document.getElementById('kudosModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ url('/api/v1/hcm/me/engagement/recognition') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block text-slate-300 font-medium mb-1">Select Colleague *</label>
                <select name="recipient_employee_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-amber-500">
                    @foreach($colleagues ?? [] as $col)
                    <option value="{{ $col->id }}">{{ $col->first_name }} {{ $col->last_name }} ({{ $col->employee_code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Value Tag *</label>
                <select name="value_tag" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-amber-500">
                    <option value="Customer Obsession">Customer Obsession</option>
                    <option value="Innovation & Agility">Innovation & Agility</option>
                    <option value="Extreme Ownership">Extreme Ownership</option>
                    <option value="Team Collaboration">Team Collaboration</option>
                    <option value="Empathy & Inclusion">Empathy & Inclusion</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Title *</label>
                <input type="text" name="title" placeholder="e.g. Outstanding sprint delivery!" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-amber-500" required>
            </div>

            <div>
                <label class="block text-slate-300 font-medium mb-1">Personal Message *</label>
                <textarea name="message" rows="3" placeholder="Describe how their contribution made a difference..." class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-white focus:outline-none focus:border-amber-500" required></textarea>
            </div>

            <input type="hidden" name="recognition_type" value="peer">
            <input type="hidden" name="visibility" value="team">

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('kudosModal').classList.add('hidden')" class="px-4 py-2 bg-slate-700 text-slate-300 rounded-xl font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-lg shadow-amber-500/20">Send Appreciation</button>
            </div>
        </form>
    </div>
</div>
@endsection
