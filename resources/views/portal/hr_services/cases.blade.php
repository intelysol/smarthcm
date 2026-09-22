@extends('portal.layout')

@section('title', 'Unified Case Inbox')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <a href="/portal/hr-services" class="text-sm font-medium text-[#6B7280] hover:text-[#1E3A5F]">Command Center</a>
                <span class="text-[#6B7280]">/</span>
                <span class="text-sm font-semibold text-[#1F2937]">Cases</span>
            </div>
            <h1 class="text-2xl font-bold text-[#1F2937] mt-1">Unified Case Inbox & Workbench</h1>
            <p class="text-sm text-[#6B7280]">Triage, prioritize, and manage cross-department HR lifecycle inquiries and cases</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="/portal/hr-services/catalog" class="inline-flex items-center px-4 py-2 border border-[#E5E7EB] rounded-lg shadow-sm text-sm font-medium text-[#1F2937] bg-white hover:bg-[#F7F9FC] hover:border-[#1E3A5F] transition">
                <i class="fa-solid fa-list-check mr-2 text-[#1E3A5F]"></i> Service Catalog
            </a>
            <a href="/portal/hr-services/catalog" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-[#1E3A5F] hover:bg-[#142A44] transition">
                <i class="fa-solid fa-plus mr-2 text-[#C9A227]"></i> New Service Request
            </a>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] shadow-sm">
        <form method="GET" action="/portal/hr-services/cases" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Subject, ticket #..." class="w-full text-sm border-[#E5E7EB] rounded-lg focus:ring-[#1E3A5F] focus:border-[#1E3A5F] px-3 py-2 border text-[#1F2937]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="w-full text-sm border-[#E5E7EB] rounded-lg focus:ring-[#1E3A5F] focus:border-[#1E3A5F] px-3 py-2 border text-[#1F2937]">
                    <option value="">All Statuses</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="waiting_on_employee" {{ request('status') === 'waiting_on_employee' ? 'selected' : '' }}>Waiting on Employee</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Priority</label>
                <select name="priority" class="w-full text-sm border-[#E5E7EB] rounded-lg focus:ring-[#1E3A5F] focus:border-[#1E3A5F] px-3 py-2 border text-[#1F2937]">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Queue</label>
                <select name="queue_id" class="w-full text-sm border-[#E5E7EB] rounded-lg focus:ring-[#1E3A5F] focus:border-[#1E3A5F] px-3 py-2 border text-[#1F2937]">
                    <option value="">All Queues</option>
                    @foreach($queues as $q)
                        <option value="{{ $q->id }}" {{ request('queue_id') == $q->id ? 'selected' : '' }}>{{ $q->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full bg-[#1E3A5F] hover:bg-[#142A44] text-white text-sm font-semibold py-2 px-3 rounded-lg shadow-sm transition">Filter</button>
                <a href="/portal/hr-services/cases" class="bg-[#F7F9FC] hover:bg-slate-200 text-[#1F2937] text-sm font-medium py-2 px-3 rounded-lg border border-[#E5E7EB] transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Cases Table -->
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#E5E7EB] bg-[#F7F9FC] text-xs font-bold text-[#6B7280] uppercase tracking-wider">
                        <th class="py-3 px-4">Ticket</th>
                        <th class="py-3 px-4">Subject</th>
                        <th class="py-3 px-4">Requester</th>
                        <th class="py-3 px-4">Queue &amp; Assignee</th>
                        <th class="py-3 px-4">Priority</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">SLA Deadline</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-sm">
                    @forelse($cases as $case)
                    @php
                        $isOverdue = $case->due_at && \Carbon\Carbon::parse($case->due_at)->isPast() && !in_array($case->status, ['resolved', 'closed']);
                    @endphp
                    <tr class="hover:bg-[#F7F9FC] transition-colors {{ $isOverdue ? 'bg-red-50/40' : '' }}">
                        <td class="py-3 px-4 font-mono font-bold text-xs">
                            <a href="/portal/hr-services/cases/{{ $case->id }}" class="text-[#1E3A5F] hover:underline font-semibold">
                                {{ $case->request_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-[#1F2937] line-clamp-1">{{ $case->subject }}</div>
                            <div class="text-xs text-[#6B7280]">{{ $case->service->title ?? 'General Inquiry' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-[#1F2937] font-medium">{{ $case->employee->user->name ?? ($case->employee->first_name . ' ' . $case->employee->last_name ?? 'Employee') }}</div>
                            <div class="text-xs text-[#6B7280]">{{ $case->employee->employee_number ?? '' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-xs font-semibold text-[#1F2937]">{{ $case->assignedQueue->name ?? 'Unassigned Queue' }}</div>
                            <div class="text-xs text-[#6B7280]">{{ $case->assignedUser->name ?? 'Unassigned' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                @if($case->priority === 'urgent') bg-[#C0392B]/10 text-[#C0392B] border border-[#C0392B]/20
                                @elseif($case->priority === 'high') bg-[#B7791F]/10 text-[#B7791F] border border-[#B7791F]/20
                                @elseif($case->priority === 'medium') bg-[#1E3A5F]/10 text-[#1E3A5F] border border-[#1E3A5F]/20
                                @else bg-[#6B7280]/10 text-[#6B7280] border border-[#6B7280]/20 @endif">
                                {{ ucfirst($case->priority) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                @if(in_array($case->status, ['resolved', 'closed'])) bg-[#16805C]/10 text-[#16805C] border border-[#16805C]/20
                                @elseif($case->status === 'in_progress') bg-[#2563EB]/10 text-[#2563EB] border border-[#2563EB]/20
                                @elseif($case->status === 'waiting_on_employee') bg-[#B7791F]/10 text-[#B7791F] border border-[#B7791F]/20
                                @else bg-[#1E3A5F]/10 text-[#1E3A5F] border border-[#1E3A5F]/20 @endif">
                                {{ str_replace('_', ' ', ucfirst($case->status)) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-xs">
                            @if($case->due_at)
                                <span class="{{ $isOverdue ? 'text-[#C0392B] font-bold flex items-center' : 'text-[#6B7280]' }}">
                                    @if($isOverdue)<i class="fa-solid fa-circle-exclamation mr-1"></i>@endif
                                    {{ \Carbon\Carbon::parse($case->due_at)->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-[#6B7280]">No SLA</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="/portal/hr-services/cases/{{ $case->id }}" class="inline-flex items-center px-2.5 py-1.5 border border-[#E5E7EB] hover:border-[#1E3A5F] hover:bg-[#1E3A5F] hover:text-white shadow-sm text-xs font-semibold rounded text-[#1E3A5F] bg-white transition">
                                Open &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-[#6B7280]">
                            No cases found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-[#E5E7EB] bg-[#F7F9FC]">
            {{ $cases->links() }}
        </div>
    </div>
</div>
@endsection
