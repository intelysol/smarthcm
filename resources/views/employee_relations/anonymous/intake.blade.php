@extends('layouts.employee_relations')

@section('title', 'Anonymous Case Intake')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="text-center space-y-2">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-500/10 text-amber-400 mb-2">
            <i class="fa-solid fa-user-secret text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-white">Confidential & Anonymous Reporting</h1>
        <p class="text-sm text-slate-400">Report workplace ethics, safety violations, harassment, or misconduct securely without identifying yourself.</p>
    </div>

    <div class="bg-slate-900 border border-slate-800 p-6 rounded-xl">
        <form method="POST" action="/api/v1/hcm/employee-relations/anonymous" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Organization Tenant ID *</label>
                <input type="text" name="tenant_id" required placeholder="Enter Company Tenant ID (UUID)" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-amber-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Issue Category *</label>
                <select name="case_type_id" required class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-amber-500">
                    <option value="">Select Category...</option>
                    @foreach($caseTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Subject / Title *</label>
                <input type="text" name="title" required placeholder="Brief title of concern" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-amber-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Detailed Narrative *</label>
                <textarea name="summary" rows="5" required placeholder="Describe the incident, locations, dates, and any relevant facts. Do not include personal identity details if you wish to remain strictly anonymous." class="w-full bg-slate-950 border border-slate-700 rounded-lg p-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500"></textarea>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-500 text-slate-950 font-bold rounded-lg transition shadow-lg shadow-amber-600/30">
                    Submit Anonymous Report
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
