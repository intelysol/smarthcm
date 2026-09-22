@extends('shells.platform')

@section('title', 'Tenants & Provisioning')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Managed Enterprise Tenants</h1>
            <p class="text-xs text-slate-400">All customer environments and provisioning status</p>
        </div>
        <a href="{{ route('platform.control-center') }}" class="text-xs font-semibold text-[#C9A227] hover:underline">&larr; Back to Control Center</a>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl shadow overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-950/60 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                <tr>
                    <th class="px-5 py-3 font-semibold">Tenant Name</th>
                    <th class="px-5 py-3 font-semibold">Slug</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 font-semibold">Created At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-300">
                @forelse($tenants as $t)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3.5 font-bold text-white">{{ $t->name }}</td>
                        <td class="px-5 py-3.5 font-mono text-[11px] text-slate-400">{{ $t->slug }}</td>
                        <td class="px-5 py-3.5">
                            @php
                                $statusStr = is_string($t->status) ? $t->status : ($t->status?->value ?? (string) $t->status);
                            @endphp
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ strtolower($statusStr) === 'active' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' }}">
                                {{ strtoupper($statusStr) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-400">{{ $t->created_at ? $t->created_at->format('M d, Y') : 'System' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-6 text-center text-slate-500">No tenants registered.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
