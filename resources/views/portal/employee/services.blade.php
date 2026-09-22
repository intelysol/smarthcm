@extends('portal.layout')

@section('title', 'HR Services')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-[#E5E7EB] shadow-sm">
        <div>
            <h1 class="text-xl font-bold text-[#1F2937] tracking-tight">HR Service Catalog &amp; Helpdesk</h1>
            <p class="text-xs text-[#6B7280] mt-1">Select from standardized HR services or open a service inquiry with our Shared Services team.</p>
        </div>
        <a href="{{ route('portal.hr-services.catalog') }}" class="px-4 py-2 bg-[#1E3A5F] hover:bg-[#142A44] text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center space-x-2">
            <i class="fa-solid fa-plus text-[#C9A227]"></i>
            <span>New Service Ticket</span>
        </a>
    </div>

    <!-- Service Catalog Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-[#E5E7EB] p-5 rounded-2xl hover:border-[#1E3A5F] hover:shadow-md transition cursor-pointer shadow-sm group">
            <div class="w-10 h-10 rounded-xl bg-[#1E3A5F]/10 text-[#1E3A5F] flex items-center justify-center text-lg mb-3 group-hover:scale-105 transition">
                <i class="fa-solid fa-certificate"></i>
            </div>
            <h3 class="text-sm font-bold text-[#1F2937]">Employment Certificate</h3>
            <p class="text-[11px] text-[#6B7280] mt-1 leading-relaxed">Official proof of employment letter for bank, visa, or housing.</p>
            <div class="mt-3 text-[10px] font-bold text-[#1E3A5F] uppercase tracking-wider">SLA: 1 Business Day</div>
        </div>

        <div class="bg-white border border-[#E5E7EB] p-5 rounded-2xl hover:border-[#16805C] hover:shadow-md transition cursor-pointer shadow-sm group">
            <div class="w-10 h-10 rounded-xl bg-[#16805C]/10 text-[#16805C] flex items-center justify-center text-lg mb-3 group-hover:scale-105 transition">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            <h3 class="text-sm font-bold text-[#1F2937]">Medical Insurance Card</h3>
            <p class="text-[11px] text-[#6B7280] mt-1 leading-relaxed">Request replacement health card or add dependent coverage.</p>
            <div class="mt-3 text-[10px] font-bold text-[#16805C] uppercase tracking-wider">SLA: 2 Business Days</div>
        </div>

        <div class="bg-white border border-[#E5E7EB] p-5 rounded-2xl hover:border-[#B7791F] hover:shadow-md transition cursor-pointer shadow-sm group">
            <div class="w-10 h-10 rounded-xl bg-[#B7791F]/10 text-[#B7791F] flex items-center justify-center text-lg mb-3 group-hover:scale-105 transition">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <h3 class="text-sm font-bold text-[#1F2937]">Bank Account Modification</h3>
            <p class="text-[11px] text-[#6B7280] mt-1 leading-relaxed">Submit revised IBAN details for payroll direct deposit.</p>
            <div class="mt-3 text-[10px] font-bold text-[#B7791F] uppercase tracking-wider">SLA: 3 Business Days</div>
        </div>

        <div class="bg-white border border-[#E5E7EB] p-5 rounded-2xl hover:border-[#C0392B] hover:shadow-md transition cursor-pointer shadow-sm group">
            <div class="w-10 h-10 rounded-xl bg-[#C0392B]/10 text-[#C0392B] flex items-center justify-center text-lg mb-3 group-hover:scale-105 transition">
                <i class="fa-solid fa-circle-question"></i>
            </div>
            <h3 class="text-sm font-bold text-[#1F2937]">General HR Inquiry</h3>
            <p class="text-[11px] text-[#6B7280] mt-1 leading-relaxed">Ask questions regarding policies, leave rules, or benefits.</p>
            <div class="mt-3 text-[10px] font-bold text-[#C0392B] uppercase tracking-wider">SLA: 24 Hours</div>
        </div>
    </div>

    <!-- Active Tickets Table -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
            <h3 class="text-sm font-bold text-[#1F2937] flex items-center">
                <i class="fa-solid fa-ticket mr-2 text-[#1E3A5F]"></i> My Open Service Requests
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#1F2937]">
                <thead class="text-[10px] uppercase font-bold text-[#6B7280] border-b border-[#E5E7EB] bg-[#F7F9FC]">
                    <tr>
                        <th class="py-2.5 px-3">Ticket ID</th>
                        <th class="py-2.5 px-3">Service Name</th>
                        <th class="py-2.5 px-3">Priority</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($myRequests as $ticket)
                        <tr class="hover:bg-[#F7F9FC] transition">
                            <td class="py-3 px-3 font-mono font-bold text-[#1E3A5F]">{{ $ticket->ticket_number ?? 'HR-REQ' }}</td>
                            <td class="py-3 px-3 font-semibold text-[#1F2937]">{{ $ticket->title ?? $ticket->subject ?? 'Service Request' }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ ($ticket->priority ?? '') === 'URGENT' ? 'bg-[#C0392B]/10 text-[#C0392B] border border-[#C0392B]/20' : 'bg-[#F7F9FC] text-[#1F2937] border border-[#E5E7EB]' }}">
                                    {{ $ticket->priority ?? 'NORMAL' }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#B7791F]/10 text-[#B7791F] border border-[#B7791F]/20">
                                    {{ strtoupper($ticket->status ?? 'IN_PROGRESS') }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-[#6B7280]">{{ date('M d, Y', strtotime($ticket->created_at)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-[#6B7280]">
                                No active HR service tickets pending resolution.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
