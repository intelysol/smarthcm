@extends('shells.tenant')

@section('title', 'Users & Access Control')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Users &amp; Access Control</h1>
            <p class="text-xs text-slate-500">Manage user authentication credentials, activation, and role assignments</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">&larr; Back to Overview</a>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3 font-semibold">User Name</th>
                    <th class="px-5 py-3 font-semibold">Email</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($users as $u)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5 font-bold text-slate-900">{{ $u->name }}</td>
                        <td class="px-5 py-3.5 font-mono text-slate-500">{{ $u->email }}</td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                {{ strtoupper($u->status ?? 'ACTIVE') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-6 text-center text-slate-500">No users found for this organization.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
