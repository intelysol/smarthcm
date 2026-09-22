@extends('shells.platform')

@section('title', 'Global Users Directory — Platform Control Center')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Global Platform &amp; Tenant Users</h1>
            <p class="text-xs text-slate-400">Cross-tenant identity visibility, administrative roles, and account lifecycle states</p>
        </div>
        <a href="{{ route('platform.control-center') }}" class="text-xs font-semibold text-[#C9A227] hover:underline">&larr; Back to Control Center</a>
    </div>

    @if(session('status'))
        <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-400 flex items-center justify-between">
            <span>{{ session('status') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">&times;</button>
        </div>
    @endif

    @if(session('warning'))
        <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-400 flex items-center justify-between">
            <span>{{ session('warning') }}</span>
            <button onclick="this.parentElement.remove()" class="text-amber-400 hover:text-amber-200">&times;</button>
        </div>
    @endif

    <div class="bg-slate-900 border border-slate-800 rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/60 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800 font-semibold">
                    <tr>
                        <th class="px-5 py-3">User &amp; Email</th>
                        <th class="px-5 py-3">Tenant Context</th>
                        <th class="px-5 py-3">Roles / Privileges</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Last Active</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-white flex items-center gap-1.5">
                                    <span>{{ $u->name }}</span>
                                    @if($u->is_platform_admin)
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-[#C9A227]/20 text-[#C9A227] border border-[#C9A227]/30">SUPER ADMIN</span>
                                    @endif
                                </div>
                                <div class="text-[11px] font-mono text-slate-400">{{ $u->email }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($u->tenant)
                                    <span class="font-medium text-slate-200">{{ $u->tenant->name }}</span>
                                    <span class="block text-[10px] font-mono text-slate-500">{{ $u->tenant->slug }}</span>
                                @else
                                    <span class="text-indigo-400 font-semibold text-[11px]">Global Platform Root</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($u->roles as $r)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                            {{ $r->label ?? $r->name }}
                                        </span>
                                    @empty
                                        <span class="text-[11px] text-slate-500 italic">None</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $u->status === 'active' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                                    {{ strtoupper($u->status ?? 'ACTIVE') }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-400 text-[11px]">
                                {{ $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->diffForHumans() : 'Never' }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <form method="POST" action="{{ route('platform.users.status', $u->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded text-[11px] font-semibold border {{ $u->status === 'active' ? 'border-amber-500/30 text-amber-400 hover:bg-amber-500/10' : 'border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/10' }} transition">
                                        {{ $u->status === 'active' ? 'Suspend' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-5 py-3 border-t border-slate-800 bg-slate-950/40 text-xs">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
