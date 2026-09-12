@extends('compliance.layout')

@section('title', 'Employee Compliance Profile')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Employee Compliance Record</span>
                <h2 class="text-2xl font-bold text-slate-900 mt-1">Employee ID: {{ $employeeId }}</h2>
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-medium">Compliance Score:</span>
                    <span class="text-base font-bold text-indigo-600">{{ $evaluation['score'] ?? 0 }}%</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ ($evaluation['overall_status'] ?? '') === 'compliant' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ strtoupper($evaluation['overall_status'] ?? 'UNKNOWN') }}
                    </span>
                </div>
            </div>
            <div>
                <a href="{{ route('compliance.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Requirements Evaluation Details -->
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-base font-semibold text-slate-800">Obligations & Regulatory Requirements</h3>
        </div>
        <div class="divide-y divide-slate-200">
            @forelse($evaluation['details'] ?? [] as $detail)
                <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h4 class="font-bold text-slate-900">{{ $detail['requirement_name'] ?? 'Requirement' }}</h4>
                            @if(!empty($detail['is_mandatory']))
                                <span class="text-xs bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded font-medium">Mandatory</span>
                            @else
                                <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-medium">Optional</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500">Evaluation Reason: {{ $detail['reason'] ?? 'Standard verification' }}</p>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ ($detail['status'] ?? '') === 'satisfied' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ strtoupper($detail['status'] ?? 'PENDING') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-sm text-slate-500">
                    No active compliance requirements assigned to this employee.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
