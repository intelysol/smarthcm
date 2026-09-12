@extends('personal_data.layout')

@section('title', 'Change Requests & Approval Queue — Flow HCM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Master Data Change Requests & Approvals</h1>
            <p class="text-sm text-slate-500 mt-1">Review, approve, or reject employee-submitted changes to personal profiles, addresses, emergency contacts and bank details.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <a href="{{ route('personal-data.governance') }}" class="px-4 py-2 text-xs font-semibold rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition">
                <i class="fa-solid fa-gauge mr-1.5"></i>Governance Overview
            </a>
        </div>
    </div>

    <!-- Instructions / Governance Alert -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start space-x-3">
        <i class="fa-solid fa-circle-info text-blue-600 mt-0.5 text-sm"></i>
        <div class="text-xs text-blue-800 space-y-1">
            <p class="font-bold">Side-by-Side Verification Enforced</p>
            <p>Every change request records the previous value alongside the proposed value. Approvals immediately apply updates to active Core HR and Personal Data records with automated effective dating.</p>
        </div>
    </div>

    <!-- Active Requests Workspace Placeholder -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-inbox text-slate-600"></i>
                <h3 class="text-sm font-bold text-slate-800">Pending Review Queue</h3>
            </div>
            <span class="text-xs text-slate-400">All submissions are digitally signed and audited</span>
        </div>
        <div class="p-8 text-center text-slate-500 space-y-3">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center text-xl">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <h4 class="text-sm font-semibold text-slate-700">Governance Queue In Sync</h4>
            <p class="text-xs text-slate-400 max-w-md mx-auto">
                No backlog currently blocking payroll or master governance. Employee self-service changes will appear here for side-by-side inspection.
            </p>
        </div>
    </div>
</div>
@endsection
