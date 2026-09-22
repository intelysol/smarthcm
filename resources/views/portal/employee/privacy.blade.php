@extends('portal.layout')

@section('title', 'My Privacy & Data Rights')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-wide">Privacy &amp; My Data Rights</h1>
            <p class="text-xs text-slate-400 mt-1">Exercise your statutory privacy rights under GDPR, CCPA, and applicable labor regulations.</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                <i class="fa-solid fa-lock mr-1"></i> Data Protection Active
            </span>
        </div>
    </div>

    <!-- Privacy Rights Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-2">
            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 mb-2">
                <i class="fa-solid fa-file-export"></i>
            </div>
            <h2 class="text-sm font-bold text-white">Right of Access &amp; Portability</h2>
            <p class="text-xs text-slate-400">Request a complete cryptographic export of your employee profile, punch history, and compensation records in machine-readable JSON format.</p>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-2">
            <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-2">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
            <h2 class="text-sm font-bold text-white">Right to Erasure &amp; Limits</h2>
            <p class="text-xs text-slate-400">Request deletion of non-statutory personal data. Note that labor, payroll, and tax laws mandate retention of certain records for 7&ndash;10 years.</p>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-2">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-2">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h2 class="text-sm font-bold text-white">Security &amp; Encryption</h2>
            <p class="text-xs text-slate-400">All your personal information is encrypted at rest via AES-256 and in transit via TLS 1.3 with strict tenant boundary isolation.</p>
        </div>
    </div>

    <!-- My Privacy Requests History -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-list-check text-indigo-400"></i>
                My Privacy Requests
            </h2>
            <span class="text-xs text-slate-400">{{ count($privacyRequests) }} Submitted</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-400">
                <thead class="bg-slate-950 text-slate-300 uppercase font-semibold text-[11px] border-b border-slate-800">
                    <tr>
                        <th class="p-3">Reference</th>
                        <th class="p-3">Request Type</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Verification / Hash</th>
                        <th class="p-3">Submitted At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($privacyRequests as $req)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="p-3 font-mono font-bold text-white">{{ $req->request_reference }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-200">{{ $req->request_type }}</span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $req->status === 'COMPLETED' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($req->status === 'REJECTED' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20') }}">
                                {{ $req->status }}
                            </span>
                        </td>
                        <td class="p-3 font-mono text-[11px]">
                            @if($req->export_hash_sha256)
                                <span class="text-emerald-400" title="{{ $req->export_hash_sha256 }}">SHA-256: {{ Str::limit($req->export_hash_sha256, 16) }}</span>
                            @elseif($req->blocked_reason)
                                <span class="text-amber-400">{{ Str::limit($req->blocked_reason, 40) }}</span>
                            @else
                                <span class="text-slate-500">&mdash;</span>
                            @endif
                        </td>
                        <td class="p-3 text-slate-400">{{ $req->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-500">
                            You have not submitted any privacy or data rights requests yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Company Processing Activities & Transparency Notice -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-blue-400"></i>
            Transparency Notice &amp; Processing Categories
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            @forelse($processingActivities as $pa)
            <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-white">{{ $pa->name }}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase">{{ $pa->legal_basis }}</span>
                </div>
                <p class="text-slate-400">{{ $pa->purpose }}</p>
                <div class="text-[11px] text-slate-500">
                    Data categories: <span class="text-slate-400">{{ is_array($pa->data_categories) ? implode(', ', $pa->data_categories) : $pa->data_categories }}</span>
                </div>
            </div>
            @empty
            <div class="col-span-2 text-center text-slate-500 p-4">
                No active processing categories registered.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
