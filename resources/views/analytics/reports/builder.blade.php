@extends('analytics.layout')

@section('title', 'Custom Report Builder — Flow HCM')

@section('content')
<div class="space-y-6">
    <div class="border-b border-slate-200 pb-5">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center">
            <i class="fa-solid fa-table-cells text-pink-600 mr-3"></i>Interactive Report Builder
        </h1>
        <p class="text-sm text-slate-500 mt-1">Select dimensions, metrics, filters, and export operational datasets to CSV or JSON.</p>
    </div>

    <!-- Query Builder Form -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <form id="reportForm" action="{{ route('hcm.analytics.reports.execute') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Dataset Selection -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">1. Select Dataset</label>
                    <select name="dataset" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 bg-white focus:ring-pink-500 focus:border-pink-500">
                        <option value="DP_WORKFORCE">Workforce & Headcount (DP_WORKFORCE)</option>
                        <option value="DP_ATTENDANCE">Attendance & Overtime (DP_ATTENDANCE)</option>
                        <option value="DP_PAYROLL">Payroll & Costs (DP_PAYROLL)</option>
                    </select>
                </div>

                <!-- Dimensions Selection -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">2. Grouping Dimensions</label>
                    <div class="space-y-2 text-sm text-slate-700">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="dimensions[]" value="department" checked class="rounded text-pink-600 focus:ring-pink-500">
                            <span>Department</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="dimensions[]" value="branch" checked class="rounded text-pink-600 focus:ring-pink-500">
                            <span>Branch / Site</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="dimensions[]" value="status" class="rounded text-pink-600 focus:ring-pink-500">
                            <span>Employment Status</span>
                        </label>
                    </div>
                </div>

                <!-- Export Format -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">3. Output Format</label>
                    <select name="format" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 bg-white focus:ring-pink-500 focus:border-pink-500">
                        <option value="json">Interactive Data Table (JSON)</option>
                        <option value="csv">Download CSV File</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <button type="submit" class="px-5 py-2.5 bg-pink-600 hover:bg-pink-700 text-white font-semibold text-sm rounded-lg shadow-sm transition flex items-center">
                    <i class="fa-solid fa-play mr-2"></i>Execute Query
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
