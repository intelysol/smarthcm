@extends('portal.layout')

@section('title', 'My Documents')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-wide">My Documents &amp; Policies</h1>
            <p class="text-xs text-slate-400 mt-1">Review official company policies, sign acknowledgements, and download your personal employment documents.</p>
        </div>
        <button onclick="openUploadModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow transition flex items-center space-x-2">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span>Upload Document</span>
        </button>
    </div>

    <!-- Mandatory Acknowledgements Section -->
    <div class="bg-slate-900 border border-amber-500/30 rounded-2xl p-5 shadow-sm space-y-3">
        <div class="flex items-center space-x-2 text-amber-400">
            <i class="fa-solid fa-triangle-exclamation text-sm"></i>
            <h3 class="text-sm font-bold">Mandatory Policy Acknowledgements</h3>
        </div>
        <p class="text-xs text-slate-400 leading-relaxed">
            Enterprise governance mandates that all employees acknowledge revised organizational code of conduct and security policies.
        </p>

        <div class="space-y-2 mt-2">
            @forelse($acknowledgements as $ack)
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between text-xs">
                    <div>
                        <div class="font-bold text-white">{{ $ack->title ?? 'Annual Code of Conduct & Ethics Policy' }}</div>
                        <div class="text-[11px] text-slate-400">Version 2.60 &bull; Compliance standard</div>
                    </div>
                    @if(!empty($ack->acknowledged_at))
                        <span class="px-2.5 py-1 rounded bg-emerald-500/20 text-emerald-400 font-bold text-[10px] flex items-center">
                            <i class="fa-solid fa-check mr-1"></i> Acknowledged
                        </span>
                    @else
                        <button onclick="handleAcknowledge(this, '{{ $ack->id }}')" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-bold text-[11px] transition shadow">
                            Acknowledge &amp; Sign
                        </button>
                    @endif
                </div>
            @empty
                <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between text-xs">
                    <div>
                        <div class="font-bold text-white">Enterprise Information Security Policy (ISO 27001)</div>
                        <div class="text-[11px] text-slate-400">Annual security acknowledgement &bull; Requires review</div>
                    </div>
                    <button onclick="handleAcknowledge(this, 'sec-iso-27001')" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[11px] transition shadow">
                        Review &amp; Sign
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Official Documents Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white flex items-center">
                <i class="fa-solid fa-folder-closed mr-2 text-indigo-400"></i> Personal Employment Documents
            </h3>
        </div>

        <div class="divide-y divide-slate-800/60 text-xs">
            <div class="py-3 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-indigo-400">
                        <i class="fa-regular fa-file-pdf"></i>
                    </div>
                    <div>
                        <div class="font-bold text-white">Employment Offer &amp; Agreement Letter</div>
                        <div class="text-[11px] text-slate-400">Official employment contract</div>
                    </div>
                </div>
                <a href="{{ route('employee_documents.employee.portal') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg font-medium transition text-[11px] flex items-center">
                    <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> View Portal
                </a>
            </div>

            <div class="py-3 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-teal-400">
                        <i class="fa-regular fa-file-lines"></i>
                    </div>
                    <div>
                        <div class="font-bold text-white">Tax Withholding Declaration &amp; Registration</div>
                        <div class="text-[11px] text-slate-400">National statutory tax filing certificate</div>
                    </div>
                </div>
                <a href="{{ route('portal.pay') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg font-medium transition text-[11px] flex items-center">
                    <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> View Tax Slips
                </a>
            </div>

            @foreach($documents as $doc)
                <div class="py-3 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-indigo-400">
                            <i class="fa-regular fa-file"></i>
                        </div>
                        <div>
                            <div class="font-bold text-white">{{ $doc->title ?? $doc->file_name ?? 'Document' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $doc->category ?? 'General' }}</div>
                        </div>
                    </div>
                    <a href="/api/v1/documents/{{ $doc->id }}/download" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg font-medium transition text-[11px] flex items-center">
                        <i class="fa-solid fa-download mr-1"></i> Download
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div id="upload-doc-modal" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-sm font-bold text-white flex items-center">
                <i class="fa-solid fa-cloud-arrow-up text-indigo-400 mr-2"></i> Upload Employment Document
            </h3>
            <button onclick="closeUploadModal()" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="upload-doc-form" onsubmit="handleUploadDoc(event)" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Document Title</label>
                <input type="text" id="doc-title" name="title" required placeholder="e.g. Educational Degree, National ID" 
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Category</label>
                <select id="doc-category" name="category" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-white focus:outline-none focus:border-indigo-500">
                    <option value="identification">Identification &amp; Passports</option>
                    <option value="education">Certifications &amp; Degrees</option>
                    <option value="medical">Medical &amp; Health Records</option>
                    <option value="tax">Tax &amp; Banking Records</option>
                    <option value="other">Other Supporting Documents</option>
                </select>
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Choose File (PDF, DOCX, PNG)</label>
                <input type="file" id="doc-file" name="file" required
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-300 file:mr-3 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-indigo-600 file:text-white hover:file:bg-indigo-500">
            </div>
            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="closeUploadModal()" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 transition">Cancel</button>
                <button type="submit" id="btn-submit-upload" class="px-4 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-500 transition flex items-center">
                    <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Submit Upload
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUploadModal() {
        const m = document.getElementById('upload-doc-modal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function closeUploadModal() {
        const m = document.getElementById('upload-doc-modal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

    async function handleUploadDoc(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-upload');
        const form = document.getElementById('upload-doc-form');
        const formData = new FormData(form);

        await window.submitAsync(btn, async () => {
            try {
                const res = await fetch('/api/v1/documents', {
                    method: 'POST',
                    headers: {
                        'X-Tenant-ID': '{{ $employee->tenant_id ?? "default" }}'
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success || res.status === 200 || res.status === 201) {
                    window.showNotification('success', 'Document uploaded successfully.');
                    closeUploadModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    window.showNotification('error', data.error?.message || 'Unable to upload document.', null, data.request_id);
                }
            } catch (err) {
                window.showNotification('error', 'Network error occurred during document upload.');
            }
        });
    }

    async function handleAcknowledge(btn, id) {
        await window.submitAsync(btn, async () => {
            try {
                const res = await fetch('/api/me/documents/' + id + '/acknowledge', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Tenant-ID': '{{ $employee->tenant_id ?? "default" }}',
                        'X-Employee-ID': '{{ $employee->id ?? "" }}'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    window.showNotification('success', 'Policy acknowledgement recorded successfully.');
                    setTimeout(() => location.reload(), 800);
                } else {
                    window.showNotification('error', data.error?.message || 'Unable to record acknowledgement.', null, data.request_id);
                }
            } catch (err) {
                window.showNotification('error', 'Network communication error.');
            }
        });
    }
</script>
@endsection
