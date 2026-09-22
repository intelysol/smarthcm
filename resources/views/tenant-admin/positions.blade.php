@extends('shells.tenant')

@section('title', 'Positions & Job Architecture')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Job Positions &amp; Architecture</h1>
            <p class="text-xs text-slate-500">Define job codes, reporting hierarchies, and position budgets</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">&larr; Back to Overview</a>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3 font-semibold">Position Title</th>
                    <th class="px-5 py-3 font-semibold">Position Code</th>
                    <th class="px-5 py-3 font-semibold">Department</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($positions as $pos)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5 font-bold text-slate-900">{{ $pos->title ?? 'Staff Position' }}</td>
                        <td class="px-5 py-3.5 font-mono text-slate-500">{{ $pos->code ?? 'POS-001' }}</td>
                        <td class="px-5 py-3.5 text-slate-600">Enterprise Operations</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-6 text-center text-slate-500">No positions configured for this tenant.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
