@extends('analytics.layout')

@section('title', 'Workforce & Headcount Analytics — Flow HCM')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center">
                <i class="fa-solid fa-users text-blue-600 mr-3"></i>Workforce & Headcount Analytics
            </h1>
            <p class="text-sm text-slate-500 mt-1">Detailed demographic, employment status, FTE, and organizational breakdowns.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-2">
            <span class="text-xs text-slate-500">As of Date:</span>
            <input type="date" value="{{ $asOfDate }}" class="text-sm border border-slate-300 rounded-md px-3 py-1.5 shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Active Headcount</span>
            <div class="mt-3 text-3xl font-extrabold text-blue-600">{{ $summary['active_headcount'] ?? 0 }}</div>
            <p class="text-xs text-slate-500 mt-1">{{ $summary['total_headcount'] ?? 0 }} total registered personnel</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Full-Time Equivalent (FTE)</span>
            <div class="mt-3 text-3xl font-extrabold text-indigo-600">{{ $summary['fte_total'] ?? 0 }}</div>
            <p class="text-xs text-slate-500 mt-1">{{ $summary['full_time'] ?? 0 }} Full-Time | {{ $summary['part_time'] ?? 0 }} Part-Time</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold uppercase text-slate-500">Contingent / Contractors</span>
            <div class="mt-3 text-3xl font-extrabold text-purple-600">{{ $summary['contractors'] ?? 0 }}</div>
            <p class="text-xs text-slate-500 mt-1">Flexible & external workforce</p>
        </div>
    </div>

    <!-- Branch Distribution Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">Headcount by Branch / Location</h3>
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase">
                <tr>
                    <th class="px-6 py-3 text-left">Branch / Site</th>
                    <th class="px-6 py-3 text-right">Headcount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($summary['by_branch'] ?? [] as $branch => $cnt)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 font-medium">{{ $branch }}</td>
                    <td class="px-6 py-4 text-right font-bold">{{ $cnt }}</td>
                </tr>
                @empty
                <tr><td colspan="2" class="px-6 py-4 text-center text-slate-400">No branch distribution records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
