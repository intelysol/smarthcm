@extends('layouts.employee_relations')

@section('title', 'Submit Employee Relations Report')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Submit Employee Report / Grievance</h1>
        <p class="text-sm text-slate-400">Submit a confidential workplace grievance, harassment complaint, or policy issue.</p>
    </div>

    <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl">
        <form method="POST" action="/api/v1/hcm/me/employee-relations/report" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Issue Type *</label>
                <select name="case_type_id" required class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Select Category...</option>
                    @foreach($caseTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Subject / Title *</label>
                <input type="text" name="title" required placeholder="Brief description of the issue" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Person / Dept Involved</label>
                    <input type="text" name="subject_name" placeholder="Name or department" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Incident Date</label>
                    <input type="date" name="incident_date" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Incident Details *</label>
                <textarea name="summary" rows="5" required placeholder="Please provide all relevant facts, locations, and details." class="w-full bg-slate-950 border border-slate-700 rounded-lg p-4 text-sm text-slate-200 focus:outline-none focus:border-indigo-500"></textarea>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <a href="{{ route('er.employee.dashboard') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition border border-slate-700">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-lg transition shadow-lg shadow-indigo-600/30">Submit Report</button>
            </div>
        </form>
    </div>
</div>
@endsection
