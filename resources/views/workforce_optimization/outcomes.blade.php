@extends('workforce_optimization.layout')

@section('title', 'Optimization Outcomes - SmartHCM')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Realized Outcomes & Closed-Loop Feedback</h1>
            <p class="text-sm text-gray-500 mt-1">Comparing predicted vs. actual impact across cost, capacity, and productivity</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-500 font-medium">
                <tr>
                    <th class="px-6 py-3 text-left">Measurement Date</th>
                    <th class="px-6 py-3 text-left">Category</th>
                    <th class="px-6 py-3 text-left">Metric</th>
                    <th class="px-6 py-3 text-right">Baseline</th>
                    <th class="px-6 py-3 text-right">Predicted</th>
                    <th class="px-6 py-3 text-right">Actual</th>
                    <th class="px-6 py-3 text-right">Variance %</th>
                    <th class="px-6 py-3 text-center">Realization</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($outcomes as $outcome)
                <tr>
                    <td class="px-6 py-4 text-xs text-gray-500">{{ $outcome->measurement_date?->toFormattedDateString() }}</td>
                    <td class="px-6 py-4 text-xs font-semibold text-gray-700">{{ $outcome->metric_category }}</td>
                    <td class="px-6 py-4 text-gray-900 font-medium">{{ $outcome->metric_name }}</td>
                    <td class="px-6 py-4 text-right font-mono">{{ $outcome->baseline_value }}</td>
                    <td class="px-6 py-4 text-right font-mono text-indigo-600">{{ $outcome->predicted_value }}</td>
                    <td class="px-6 py-4 text-right font-mono font-bold">{{ $outcome->actual_value }}</td>
                    <td class="px-6 py-4 text-right font-mono {{ $outcome->variance_pct >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $outcome->variance_pct }}%
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded {{ $outcome->realization_status === 'ACHIEVED' ? 'bg-green-100 text-green-800' : ($outcome->realization_status === 'EXCEEDED' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800') }}">
                            {{ $outcome->realization_status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-400">No outcomes measured yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if(method_exists($outcomes, 'links'))
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $outcomes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
