@extends('workforce_optimization.layout')

@section('title', 'Optimization Overview - SmartHCM')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Workforce Optimization Intelligence</h1>
            <p class="text-sm text-gray-500 mt-1">Multi-objective decision intelligence and human-in-the-loop workforce actions</p>
        </div>
        <div class="flex space-x-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                AI Advisory Active
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                Human-in-the-Loop Enforced
            </span>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs font-semibold uppercase text-gray-400">Open Opportunities</p>
            <h3 class="text-3xl font-extrabold text-indigo-600 mt-2">{{ $openOpportunitiesCount }}</h3>
            <p class="text-xs text-gray-500 mt-1">Capacity gaps, overtime anomalies, skill deficits</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs font-semibold uppercase text-gray-400">Pending Recommendations</p>
            <h3 class="text-3xl font-extrabold text-amber-500 mt-2">{{ $pendingRecommendations->count() }}</h3>
            <p class="text-xs text-gray-500 mt-1">Awaiting executive / HRBP authorization</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs font-semibold uppercase text-gray-400">Recent Runs</p>
            <h3 class="text-3xl font-extrabold text-emerald-600 mt-2">{{ $latestRuns->count() }}</h3>
            <p class="text-xs text-gray-500 mt-1">Multi-criteria optimization cycles executed</p>
        </div>
    </div>

    <!-- Latest Runs Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Latest Optimization Solver Cycles</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-500 font-medium">
                <tr>
                    <th class="px-6 py-3 text-left">Run Code</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Execution (ms)</th>
                    <th class="px-6 py-3 text-left">Started At</th>
                    <th class="px-6 py-3 text-left">Summary</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($latestRuns as $run)
                <tr>
                    <td class="px-6 py-4 font-mono font-bold text-indigo-700">{{ $run->run_code }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded bg-emerald-100 text-emerald-800">
                            {{ $run->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $run->execution_time_ms ?? 'N/A' }} ms</td>
                    <td class="px-6 py-4 text-gray-500">{{ $run->started_at?->toFormattedDateString() }}</td>
                    <td class="px-6 py-4 text-gray-500 text-xs">
                        {{ json_encode($run->summary_results) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-400">No optimization runs executed yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
