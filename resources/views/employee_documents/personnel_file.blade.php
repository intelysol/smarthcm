@extends('employee_documents.layout')

@section('title', 'Digital Personnel File — ' . $employee->fullName() . ' — Flow HCM')

@section('content')
<div class="space-y-8">

    <!-- Employee Profile Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl shadow">
                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-2xl font-extrabold text-slate-900">{{ $employee->fullName() }}</h1>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-sky-50 text-sky-700">
                        {{ $employee->employee_code }}
                    </span>
                </div>
                <div class="text-xs text-slate-500 mt-1 flex items-center space-x-3">
                    <span><i class="fa-solid fa-building mr-1 text-slate-400"></i>{{ $employee->department?->name ?? 'General' }}</span>
                    <span>&bull;</span>
                    <span><i class="fa-solid fa-calendar mr-1 text-slate-400"></i>Joined {{ $employee->joining_date ? $employee->joining_date->format('M Y') : 'N/A' }}</span>
                    <span>&bull;</span>
                    <span class="font-semibold text-emerald-600 uppercase">{{ $employee->employment_status }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <span class="text-xs text-slate-500">Authorized Records: <strong>{{ $fileData['total_authorized_documents'] }}</strong></span>
            <button onclick="document.getElementById('upload-file-modal').classList.remove('hidden')" class="inline-flex items-center px-3.5 py-2 rounded-lg text-xs font-semibold bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition">
                <i class="fa-solid fa-upload mr-1.5"></i>Upload to File
            </button>
        </div>
    </div>

    <!-- Personnel File Categories & Document Groups -->
    <div class="space-y-6">
        @forelse($fileData['categories'] as $code => $catData)
            @if(!empty($catData['documents']))
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50/70 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid {{ $catData['category']->icon ?? 'fa-folder' }} text-sky-600 text-sm"></i>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">{{ $catData['category']->name }}</h2>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">
                            {{ count($catData['documents']) }}
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($catData['documents'] as $doc)
                            <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 hover:bg-slate-50/50 transition">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <h3 class="text-base font-bold text-slate-900">{{ $doc->title }}</h3>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                            @if($doc->verification_status === 'verified') bg-emerald-50 text-emerald-700
                                            @elseif($doc->verification_status === 'rejected') bg-rose-50 text-rose-700
                                            @else bg-amber-50 text-amber-700 @endif">
                                            {{ $doc->verification_status }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-500 space-x-3">
                                        <span>Type: <strong>{{ $doc->documentType?->name }}</strong></span>
                                        @if($doc->document_number)
                                            <span>&bull;</span>
                                            <span>Doc #: <strong>{{ $doc->document_number }}</strong></span>
                                        @endif
                                        @if($doc->expiry_date)
                                            <span>&bull;</span>
                                            <span>Expires: <strong>{{ $doc->expiry_date->format('M d, Y') }}</strong></span>
                                        @endif
                                        <span>&bull;</span>
                                        <span>Current Version: <strong>v{{ $doc->sharedDocument?->current_version ?? 1 }}</strong></span>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <a href="/api/v1/hcm/employee-documents/{{ $doc->id }}/download" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition">
                                        <i class="fa-solid fa-download mr-1.5 text-sky-600"></i>Secure Download
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center text-slate-400 text-sm">
                No documents found in this employee's personnel file.
            </div>
        @endforelse
    </div>

</div>

<!-- Upload to Personnel File Modal -->
<div id="upload-file-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Upload to Personnel File</h3>
                <p class="text-xs text-slate-500">{{ $employee->first_name }} {{ $employee->last_name }}</p>
            </div>
            <button onclick="document.getElementById('upload-file-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1">&times;</button>
        </div>
        <form onsubmit="handleUploadToFile(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Document Title</label>
                <input type="text" required placeholder="e.g. Updated Passport Copy 2026" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-sky-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Target Category</label>
                <select class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:border-sky-500">
                    <option value="identification">Identification & Verification</option>
                    <option value="contract">Contracts & Agreements</option>
                    <option value="tax">Tax & Financial Compliance</option>
                    <option value="training">Certifications & Training</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Document File</label>
                <input type="file" required class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
            </div>
            <div class="pt-2 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('upload-file-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">Save to File</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleUploadToFile(e) {
    e.preventDefault();
    document.getElementById('upload-file-modal').classList.add('hidden');
    window.showNotification('success', 'Document uploaded and archived into digital personnel file.');
}
</script>
@endsection
