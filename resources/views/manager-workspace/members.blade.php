@extends('shells.manager')

@section('title', 'Team Roster')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Team Roster</h1>
            <p class="text-xs text-slate-500">Directory of direct and matrix reporting personnel</p>
        </div>
        <a href="{{ route('manager.workbench') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">&larr; Back to Workbench</a>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3 font-semibold">Name</th>
                    <th class="px-5 py-3 font-semibold">Employee ID</th>
                    <th class="px-5 py-3 font-semibold">Department</th>
                    <th class="px-5 py-3 font-semibold">Designation</th>
                    <th class="px-5 py-3 font-semibold">Email</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($roster as $member)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5 font-bold text-slate-900">{{ $member['name'] ?? 'Team Member' }}</td>
                        <td class="px-5 py-3.5 font-mono text-slate-500">{{ $member['employee_code'] ?? 'EMP-000' }}</td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $member['department'] ?? 'Engineering' }}</td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $member['designation'] ?? 'Staff' }}</td>
                        <td class="px-5 py-3.5 text-slate-500 font-mono">{{ $member['email'] ?? 'staff@enterprise.internal' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-6 text-center text-slate-500">No members in roster.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
