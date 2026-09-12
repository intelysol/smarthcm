@extends('layouts.career')

@section('title', 'Manager Talent Dashboard')

@section('content')
<div class="space-y-6">
    <div class="bg-slate-800/80 p-6 rounded-2xl border border-slate-700">
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-users-gear text-emerald-400"></i> Manager Talent & Development Hub
        </h1>
        <p class="text-sm text-slate-400 mt-1">Review team skills, verify declared capabilities, and support career progression.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80">
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Direct Reports</span>
            <div class="text-2xl font-bold text-white mt-1">Active Team</div>
            <p class="text-xs text-slate-400 mt-2">Manage developmental actions and reviews.</p>
        </div>
        <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80">
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Skill Verifications</span>
            <div class="text-2xl font-bold text-amber-400 mt-1">Pending Action</div>
            <p class="text-xs text-slate-400 mt-2">Verify declared skills and evidence.</p>
        </div>
        <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80">
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Active Career Plans</span>
            <div class="text-2xl font-bold text-emerald-400 mt-1">In Progress</div>
            <p class="text-xs text-slate-400 mt-2">Team development plans under guidance.</p>
        </div>
    </div>
</div>
@endsection
