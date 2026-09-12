@extends('mobility.layout')

@section('title', 'Mobility Programs')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Mobility Programs & Policy Catalog</h1>
            <p class="text-sm text-slate-400">Global mobility program guidelines, duration policies, housing tiers, and hardship allowances.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-slate-400 bg-slate-850/60 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Program Name</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Duration Range</th>
                        <th class="py-3 px-4">Relocation</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($programs as $p)
                    <tr class="hover:bg-slate-850/50">
                        <td class="py-3 px-4 font-mono text-indigo-300">{{ $p->code }}</td>
                        <td class="py-3 px-4 font-semibold text-white">{{ $p->name }}</td>
                        <td class="py-3 px-4">{{ ucwords(str_replace('_', ' ', $p->mobility_type)) }}</td>
                        <td class="py-3 px-4">{{ $p->min_duration_months }} - {{ $p->max_duration_months ?? 'No limit' }} mos</td>
                        <td class="py-3 px-4">
                            @if($p->requires_relocation)
                                <span class="text-emerald-400"><i class="fa-solid fa-check mr-1"></i> Required</span>
                            @else
                                <span class="text-slate-500">Not required</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $p->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                                {{ $p->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">No mobility programs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
