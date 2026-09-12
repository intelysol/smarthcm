@extends('workforce_optimization.layout')

@section('title', 'Optimization Scenarios - SmartHCM')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">What-If Scenarios & Pareto Trade-Offs</h1>
            <p class="text-sm text-gray-500 mt-1">Multi-scenario simulation comparing hiring vs. contractors vs. overtime vs. internal reskilling</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-500 font-medium">
                <tr>
                    <th class="px-6 py-3 text-left">Code</th>
                    <th class="px-6 py-3 text-left">Scenario Name</th>
                    <th class="px-6 py-3 text-left">Type</th>
                    <th class="px-6 py-3 text-center">Baseline</th>
                    <th class="px-6 py-3 text-left">Simulated Metrics</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($scenarios as $scenario)
                <tr>
                    <td class="px-6 py-4 font-mono font-bold text-indigo-700 text-xs">{{ $scenario->scenario_code }}</td>
                    <td class="px-6 py-4 text-gray-900 font-medium">{{ $scenario->name }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded bg-purple-100 text-purple-800">
                            {{ $scenario->scenario_type }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        {{ $scenario->is_baseline ? 'Yes' : 'No' }}
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500">
                        @foreach($scenario->results as $result)
                            <div><span class="font-semibold">{{ $result->metric_name }}:</span> {{ $result->scenario_value }} ({{ $result->delta_pct }}%)</div>
                        @endforeach
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-400">No scenarios simulated yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if(method_exists($scenarios, 'links'))
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $scenarios->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
