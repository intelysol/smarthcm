@extends('shells.tenant')

@section('title', 'Organization Departments')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Departments &amp; Divisions</h1>
            <p class="text-xs text-slate-500">Manage business units and organizational branches</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">&larr; Back to Overview</a>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3 font-semibold">Department Name</th>
                    <th class="px-5 py-3 font-semibold">Code</th>
                    <th class="px-5 py-3 font-semibold">Created At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($departments as $dept)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5 font-bold text-slate-900">{{ $dept->name ?? 'Department' }}</td>
                        <td class="px-5 py-3.5 font-mono text-slate-500">{{ $dept->code ?? 'DEPT' }}</td>
                        <td class="px-5 py-3.5 text-slate-400">{{ $dept->created_at ?? 'Active' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-6 text-center text-slate-500">No departments configured for this tenant.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
