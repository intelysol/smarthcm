@extends('portal.layout')

@section('title', 'People Directory')

@section('content')
<div class="space-y-6">
    <!-- Header & Search -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-wide">People Directory &amp; Organization</h1>
            <p class="text-xs text-slate-400 mt-1">Connect with colleagues across departments, find job titles, and view team reporting lines.</p>
        </div>
        <div class="w-full sm:w-72">
            <input type="text" id="dir-search" placeholder="Search by name, title, department..." onkeyup="filterDirectory()" 
                class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
        </div>
    </div>

    <!-- People Grid -->
    <div id="people-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($people as $person)
            <div class="person-card bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm hover:border-slate-700 transition space-y-4">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-200 text-lg font-bold">
                        @if($person->photo_path)
                            <img src="{{ $person->photo_path }}" alt="Photo" class="w-full h-full object-cover rounded-xl">
                        @else
                            {{ substr($person->first_name ?? 'E', 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <h3 class="person-name text-sm font-bold text-white">{{ $person->fullName() }}</h3>
                        <p class="person-title text-xs text-indigo-400">{{ $person->designation?->name ?? 'Team Member' }}</p>
                        <p class="person-dept text-[11px] text-slate-400">{{ $person->department?->name ?? 'General' }}</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                    <span><i class="fa-solid fa-id-badge text-slate-500 mr-1"></i> {{ $person->employee_code ?? $person->employee_number }}</span>
                    @if($person->official_email)
                        <a href="mailto:{{ $person->official_email }}" class="text-indigo-400 hover:text-indigo-300">
                            <i class="fa-solid fa-envelope mr-1"></i> Email
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-500">
                No colleagues registered in this tenant directory yet.
            </div>
        @endforelse
    </div>
</div>

<script>
    function filterDirectory() {
        const query = document.getElementById('dir-search').value.toLowerCase();
        const cards = document.querySelectorAll('.person-card');

        cards.forEach(card => {
            const name = card.querySelector('.person-name')?.innerText.toLowerCase() || '';
            const title = card.querySelector('.person-title')?.innerText.toLowerCase() || '';
            const dept = card.querySelector('.person-dept')?.innerText.toLowerCase() || '';

            if (name.includes(query) || title.includes(query) || dept.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endsection
