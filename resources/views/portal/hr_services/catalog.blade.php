@extends('portal.layout')

@section('title', 'HR Service Catalog')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-sm text-[#6B7280]">
                <a href="/portal/hr-services" class="hover:text-[#1E3A5F]">Command Center</a>
                <span>/</span>
                <span class="text-[#1F2937] font-semibold">Service Catalog</span>
            </div>
            <h1 class="text-2xl font-bold text-[#1F2937] mt-1">HR Service Catalog & Intake</h1>
            <p class="text-sm text-[#6B7280]">Browse standardized service offerings, automated lifecycle requests, and submit structured inquiries</p>
        </div>
        <div>
            <a href="/portal/hr-services/cases" class="inline-flex items-center px-4 py-2 border border-[#E5E7EB] rounded-lg shadow-sm text-sm font-medium text-[#1F2937] bg-white hover:bg-[#F7F9FC] hover:border-[#1E3A5F] transition">
                &larr; Back to Case Inbox
            </a>
        </div>
    </div>

    <!-- Category Tabs / Filter -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($catalog as $item)
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6 hover:border-[#1E3A5F] hover:shadow-md transition-all flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#1E3A5F]/10 text-[#1E3A5F] border border-[#1E3A5F]/20">
                            {{ $item->category->name ?? 'General' }}
                        </span>
                        <span class="text-xs font-mono text-[#6B7280]">Code: {{ $item->service_code ?? 'GEN' }}</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#1F2937]">{{ $item->title ?? $item->name }}</h3>
                    <p class="text-sm text-[#6B7280] line-clamp-3">{{ $item->description ?? 'Standardized HR service request form.' }}</p>
                </div>

                <div class="border-t border-[#E5E7EB] pt-4 mt-4 space-y-3">
                    <div class="flex items-center justify-between text-xs text-[#6B7280]">
                        <span>Target SLA:</span>
                        <span class="font-semibold text-[#1F2937]">{{ $item->sla_hours ? $item->sla_hours . ' Hours' : 'Standard (48h)' }}</span>
                    </div>
                    <button onclick="openRequestModal('{{ $item->id }}', '{{ addslashes($item->title ?? $item->name) }}')" class="w-full bg-[#1E3A5F] hover:bg-[#142A44] text-white text-xs font-semibold py-2 px-3 rounded-lg shadow-sm transition text-center flex items-center justify-center space-x-1">
                        <span>Request Service</span>
                        <span class="text-[#C9A227]">&rarr;</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12 bg-white rounded-xl border border-[#E5E7EB]">
                <p class="text-[#6B7280]">No services currently published in the catalog.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal: Submit Service Request -->
<div id="serviceRequestModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-[#E5E7EB]">
        <div class="flex items-center justify-between border-b border-[#E5E7EB] pb-3">
            <h3 class="text-lg font-bold text-[#1F2937]" id="modalServiceTitle">Submit Service Request</h3>
            <button onclick="document.getElementById('serviceRequestModal').classList.add('hidden')" class="text-[#6B7280] hover:text-[#1F2937] text-xl font-bold">&times;</button>
        </div>
        <form action="/portal/hr-services/cases" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="service_definition_id" id="modalServiceId" value="">
            <div>
                <label class="block text-xs font-semibold text-[#1F2937] uppercase tracking-wider mb-1">Subject</label>
                <input type="text" name="subject" required placeholder="Brief summary of your request" class="w-full text-sm border-[#E5E7EB] rounded-lg p-2.5 border focus:ring-[#1E3A5F] focus:border-[#1E3A5F] text-[#1F2937]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#1F2937] uppercase tracking-wider mb-1">Urgency / Priority</label>
                <select name="priority" class="w-full text-sm border-[#E5E7EB] rounded-lg p-2.5 border focus:ring-[#1E3A5F] focus:border-[#1E3A5F] text-[#1F2937]">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#1F2937] uppercase tracking-wider mb-1">Details &amp; Description</label>
                <textarea name="description" rows="4" required placeholder="Please provide all relevant details..." class="w-full text-sm border-[#E5E7EB] rounded-lg p-2.5 border focus:ring-[#1E3A5F] focus:border-[#1E3A5F] text-[#1F2937]"></textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="document.getElementById('serviceRequestModal').classList.add('hidden')" class="px-4 py-2 text-xs font-medium text-[#1F2937] bg-[#F7F9FC] hover:bg-slate-200 rounded-lg border border-[#E5E7EB] transition">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#1E3A5F] hover:bg-[#142A44] rounded-lg shadow-sm transition">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRequestModal(serviceId, title) {
    document.getElementById('modalServiceId').value = serviceId;
    document.getElementById('modalServiceTitle').innerText = 'Request: ' + title;
    document.getElementById('serviceRequestModal').classList.remove('hidden');
}
</script>
@endsection
