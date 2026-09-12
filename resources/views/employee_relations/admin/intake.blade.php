@extends('layouts.employee_relations')

@section('title', 'Log New ER Case')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Log Employee Relations Case Intake</h1>
        <p class="text-sm text-slate-400">Initiate formal intake for a grievance, disciplinary matter, ethics concern, or workplace dispute.</p>
    </div>

    <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl">
        <form method="POST" action="/api/v1/hcm/employee-relations/cases" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Case Classification *</label>
                <select name="case_type_id" required class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Select Case Type...</option>
                    @foreach($caseTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }} ({{ ucfirst($type->category) }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Case Title *</label>
                <input type="text" name="title" required placeholder="Concise case subject" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Subject Type</label>
                    <select name="subject_type" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="employee">Employee</option>
                        <option value="manager">Manager</option>
                        <option value="department">Department / Workplace</option>
                        <option value="former_employee">Former Employee</option>
                        <option value="external_party">External Party</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Subject Name / Identifier</label>
                    <input type="text" name="subject_name" placeholder="Name or Department" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Priority</label>
                    <select name="priority" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="normal">Normal</option>
                        <option value="low">Low</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Severity</label>
                    <select name="severity" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="moderate">Moderate</option>
                        <option value="informational">Informational</option>
                        <option value="minor">Minor</option>
                        <option value="serious">Serious</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Confidentiality</label>
                    <select name="confidentiality_level" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="standard_confidential">Standard Confidential</option>
                        <option value="highly_confidential">Highly Confidential</option>
                        <option value="restricted">Restricted</option>
                        <option value="legal_restricted">Legal Restricted</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Detailed Summary & Incident Facts *</label>
                <textarea name="summary" rows="5" required placeholder="Describe what happened, parties involved, dates, and locations." class="w-full bg-slate-950 border border-slate-700 rounded-lg p-4 text-sm text-slate-200 focus:outline-none focus:border-indigo-500"></textarea>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <a href="{{ route('er.admin.cases') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-lg transition border border-slate-700">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-lg transition shadow-lg shadow-indigo-600/30">Submit Intake</button>
            </div>
        </form>
    </div>
</div>
@endsection
