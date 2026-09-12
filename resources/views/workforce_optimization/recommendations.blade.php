@extends('workforce_optimization.layout')

@section('title', 'Optimization Recommendations - SmartHCM')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Workforce Decision Recommendations</h1>
            <p class="text-sm text-gray-500 mt-1">Multi-criteria recommendations evaluated across cost, capacity, productivity, and feasibility</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-500 font-medium">
                <tr>
                    <th class="px-6 py-3 text-left">Code</th>
                    <th class="px-6 py-3 text-left">Action Type</th>
                    <th class="px-6 py-3 text-left">Title & Rationale</th>
                    <th class="px-6 py-3 text-right">Decision Score</th>
                    <th class="px-6 py-3 text-right">Cost Delta</th>
                    <th class="px-6 py-3 text-right">Capacity (hrs)</th>
                    <th class="px-6 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($recommendations as $rec)
                <tr>
                    <td class="px-6 py-4 font-mono font-bold text-indigo-700 text-xs">{{ $rec->recommendation_code }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded bg-blue-100 text-blue-800">
                            {{ $rec->action_type }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $rec->title }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $rec->rationale_narrative }}</div>
                        <div class="text-xs text-indigo-600 mt-1 italic">{{ $rec->trade_off_explanation }}</div>
                    </td>
                    <td class="px-6 py-4 text-right font-bold text-emerald-600 font-mono">{{ $rec->decision_score }} / 100</td>
                    <td class="px-6 py-4 text-right font-mono {{ $rec->cost_impact <= 0 ? 'text-green-600' : 'text-gray-900' }}">
                        \${{ number_format($rec->cost_impact, 2) }}
                    </td>
                    <td class="px-6 py-4 text-right font-mono text-gray-700">+{{ $rec->capacity_impact_hours }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded {{ $rec->status === 'APPROVED' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $rec->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-400">No recommendations generated yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if(method_exists($recommendations, 'links'))
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $recommendations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
