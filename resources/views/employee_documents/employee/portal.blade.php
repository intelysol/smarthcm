@extends('employee_documents.layout')

@section('title', 'My Documents & Personnel File — Flow HCM')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-sky-600 via-indigo-600 to-purple-600 rounded-2xl p-8 text-white shadow-lg">
        <span class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider bg-white/20 text-white backdrop-blur-sm">
            Employee Self-Service Center
        </span>
        <h1 class="text-3xl font-extrabold mt-3">My Digital Personnel File & Documents</h1>
        <p class="text-sm text-sky-100 mt-1 max-w-xl">
            Access your verified personnel records, upload requested identity and compliance certificates, and review digital acknowledgements.
        </p>
    </div>

    @if($requests->isNotEmpty())
        <!-- Document Requests From HR -->
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 space-y-3">
            <div class="flex items-center space-x-2 text-amber-800 font-bold text-sm">
                <i class="fa-solid fa-bell"></i>
                <span>Action Required: Document Requests from HR</span>
            </div>
            <div class="space-y-2">
                @foreach($requests as $req)
                    <div class="bg-white p-4 rounded-xl border border-amber-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 text-sm">{{ $req->documentType?->name }}</span>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $req->instructions ?? 'Please upload the latest copy for verification.' }}</p>
                            @if($req->due_date)
                                <span class="text-[11px] text-amber-700 font-semibold block mt-1">Due by: {{ $req->due_date->format('M d, Y') }}</span>
                            @endif
                        </div>
                        <a href="{{ route('portal.documents') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition flex items-center">
                            <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Upload Now
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Document Requirements Checklist -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
        <h2 class="text-base font-bold text-slate-900">Mandatory Document Compliance Checklist</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @forelse($requirements as $req)
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between">
                    <div class="space-y-0.5">
                        <span class="text-xs font-bold text-slate-900">{{ $req->documentType?->name }}</span>
                        <span class="block text-[11px] text-slate-500">{{ $req->is_mandatory ? 'Mandatory' : 'Optional' }}</span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                        @if($req->status === 'verified') bg-emerald-100 text-emerald-800
                        @elseif($req->status === 'submitted') bg-blue-100 text-blue-800
                        @elseif($req->status === 'waived') bg-purple-100 text-purple-800
                        @else bg-rose-100 text-rose-800 @endif">
                        {{ $req->status }}
                    </span>
                </div>
            @empty
                <div class="col-span-2 text-xs text-slate-400 py-3 text-center">No specific requirements assigned.</div>
            @endforelse
        </div>
    </div>

    <!-- My Documents Repository -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
        <h2 class="text-base font-bold text-slate-900">My Uploaded & Issued Documents</h2>
        <div class="divide-y divide-slate-100">
            @forelse($documents as $doc)
                <div class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <span class="font-bold text-slate-900 text-sm">{{ $doc->title }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                @if($doc->verification_status === 'verified') bg-emerald-50 text-emerald-700
                                @elseif($doc->verification_status === 'rejected') bg-rose-50 text-rose-700
                                @else bg-amber-50 text-amber-700 @endif">
                                {{ $doc->verification_status }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 space-x-2">
                            <span>Type: {{ $doc->documentType?->name }}</span>
                            @if($doc->expiry_date)
                                <span>&bull;</span>
                                <span>Expires: {{ $doc->expiry_date->format('M d, Y') }}</span>
                            @endif
                        </div>
                        @if($doc->rejection_reason)
                            <div class="text-xs text-rose-600 bg-rose-50 p-2 rounded border border-rose-200 mt-1">
                                <strong>Rejection Reason:</strong> {{ $doc->rejection_reason }}
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center space-x-2">
                        <a href="/api/v1/hcm/employee-documents/{{ $doc->id }}/download" target="_blank" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                            Download
                        </a>
                        @if($doc->verification_status === 'rejected')
                            <a href="{{ route('portal.documents') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition flex items-center">
                                <i class="fa-solid fa-rotate mr-1"></i> Replace
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400 text-xs">
                    No documents currently recorded in your personnel file.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
