@extends('layouts.career')

@section('title', 'Team Skill Matrix')

@section('content')
<div class="space-y-6">
    <div class="bg-slate-800/80 p-6 rounded-2xl border border-slate-700">
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-table-cells text-emerald-400"></i> Team Skill Coverage Matrix
        </h1>
        <p class="text-sm text-slate-400 mt-1">Direct subordinate capability inventory and verification status.</p>
    </div>

    <div class="bg-slate-800/60 rounded-2xl border border-slate-700/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-900/80 text-xs uppercase font-semibold text-slate-400 border-b border-slate-700">
                    <tr>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Designation</th>
                        <th class="px-6 py-4">Registered Skills</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($team as $member)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-bold text-white">{{ $member->first_name }} {{ $member->last_name }}</td>
                        <td class="px-6 py-4 text-slate-400">{{ $member->designation?->title ?? 'Staff' }}</td>
                        <td class="px-6 py-4">
                            <span class="bg-emerald-500/10 text-emerald-400 text-xs px-2.5 py-1 rounded-md border border-emerald-500/20">
                                {{ $member->skills->count() }} Skills
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="showTeamSkills('{{ addslashes($member->first_name . ' ' . $member->last_name) }}', {{ json_encode($member->skills->map(fn($s) => ['name' => $s->skill->name ?? 'Skill', 'level' => $s->proficiency_level ?? 'Competent'])) }})" class="text-xs text-emerald-400 font-semibold hover:underline">View Skills &rarr;</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-400 text-sm">No team members found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Team Member Skills Modal -->
<div id="team-skills-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div>
                <h3 id="modal-employee-name" class="text-lg font-bold text-white">Employee Skills</h3>
                <p class="text-xs text-slate-400">Verified capability & proficiency level</p>
            </div>
            <button onclick="document.getElementById('team-skills-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div id="modal-skills-list" class="space-y-2 max-h-64 overflow-y-auto pr-1">
            <!-- Dynamically populated -->
        </div>
        <div class="pt-2 border-t border-slate-800 flex justify-end">
            <button onclick="document.getElementById('team-skills-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Close</button>
        </div>
    </div>
</div>

<script>
function showTeamSkills(name, skills) {
    document.getElementById('modal-employee-name').textContent = name + ' - Skills';
    const container = document.getElementById('modal-skills-list');
    container.innerHTML = '';
    if (!skills || skills.length === 0) {
        container.innerHTML = '<p class="text-xs text-slate-400 italic py-4 text-center">No registered skills found for this employee.</p>';
    } else {
        skills.forEach(s => {
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between p-2.5 bg-slate-800/60 rounded-xl border border-slate-700/50 text-xs';
            row.innerHTML = `<span class="font-medium text-slate-200">${s.name}</span><span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-semibold border border-emerald-500/20">${s.level}</span>`;
            container.appendChild(row);
        });
    }
    document.getElementById('team-skills-modal').classList.remove('hidden');
}
</script>
@endsection
