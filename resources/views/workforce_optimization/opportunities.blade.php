@extends('workforce_optimization.layout')

@section('title', 'Optimization Opportunities - SmartHCM')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Workforce Opportunities</h1>
            <p class="text-sm text-gray-500 mt-1">Detected capacity shortages, underutilization, skill bottlenecks, and overtime spikes</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-500 font-medium">
                <tr>
                    <th class="px-6 py-3 text-left">Code</th>
                    <th class="px-6 py-3 text-left">Category</th>
                    <th class="px-6 py-3 text-left">Severity</th>
                    <th class="px-6 py-3 text-left">Title</th>
                    <th class="px-6 py-3 text-right">Hours Gap</th>
                    <th class="px-6 py-3 text-right">Cost Impact</th>
                    <th class="px-6 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($opportunities as $opp)
                <tr>
                    <td class="px-6 py-4 font-mono font-bold text-indigo-700 text-xs">{{ $opp->opportunity_code }}</td>
                    <td class="px-6 py-4 text-gray-700 text-xs">{{ $opp->category }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded {{ $opp->severity === 'CRITICAL' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $opp->severity }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-900 font-medium">
                        <div>{{ $opp->title }}</div>
                        <div class="text-xs text-gray-500">{{ $opp->description }}</div>
                    </td>
                    <td class="px-6 py-4 text-right font-mono text-gray-700">{{ $opp->estimated_hours_gap }} hrs</td>
                    <td class="px-6 py-4 text-right font-mono text-gray-700">\${{ number_format($opp->estimated_cost_impact, 2) }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded bg-gray-100 text-gray-800">
                            {{ $opp->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-400">No opportunities detected.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if(method_exists($opportunities, 'links'))
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $opportunities->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
