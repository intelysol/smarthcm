@extends('benefits.layout')

@section('title', 'Benefit Enrollments')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Employee Benefit Enrollments</h1>
            <p class="text-sm text-slate-400">Track employee coverage selections, contribution splits, and dependents.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-850 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Benefit Plan</th>
                        <th class="px-6 py-4">Coverage Tier</th>
                        <th class="px-6 py-4">Employee Share</th>
                        <th class="px-6 py-4">Employer Share</th>
                        <th class="px-6 py-4">Effective Dates</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($enrollments as $enr)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-medium text-white">
                            <div>{{ $enr->employee?->first_name }} {{ $enr->employee?->last_name }}</div>
                            <div class="text-xs text-slate-500 font-mono">{{ $enr->employee?->employee_number }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-200">{{ $enr->plan?->name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 text-xs rounded bg-slate-800 text-slate-300 border border-slate-700">
                                {{ ucwords(str_replace('_', ' ', $enr->coverage_level)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-200">${{ number_format((float)$enr->employee_contribution, 2) }}/mo</td>
                        <td class="px-6 py-4 text-emerald-400 font-medium">${{ number_format((float)$enr->employer_contribution, 2) }}/mo</td>
                        <td class="px-6 py-4 text-xs text-slate-400 font-mono">
                            {{ $enr->effective_from?->format('Y-m-d') }} &rarr; {{ $enr->effective_to ? $enr->effective_to->format('Y-m-d') : 'Indefinite' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-300 border border-emerald-700/50">
                                {{ ucfirst($enr->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-500">No enrollments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($enrollments, 'links'))
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900/60">
            {{ $enrollments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
