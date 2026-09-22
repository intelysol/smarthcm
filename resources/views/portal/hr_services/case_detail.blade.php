@extends('portal.layout')

@section('title', 'Case #' . $case->request_number)

@section('content')
<div class="space-y-6">
    <!-- Top Breadcrumb & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-sm text-[#6B7280]">
                <a href="/portal/hr-services" class="hover:text-[#1E3A5F]">Command Center</a>
                <span>/</span>
                <a href="/portal/hr-services/cases" class="hover:text-[#1E3A5F]">Cases</a>
                <span>/</span>
                <span class="font-mono font-bold text-[#1E3A5F]">{{ $case->request_number }}</span>
            </div>
            <div class="flex items-center space-x-3 mt-1">
                <h1 class="text-2xl font-bold text-[#1F2937]">{{ $case->subject }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                    @if(in_array($case->status, ['resolved', 'closed'])) bg-[#16805C]/10 text-[#16805C] border border-[#16805C]/20
                    @elseif($case->status === 'in_progress') bg-[#2563EB]/10 text-[#2563EB] border border-[#2563EB]/20
                    @elseif($case->status === 'waiting_on_employee') bg-[#B7791F]/10 text-[#B7791F] border border-[#B7791F]/20
                    @else bg-[#1E3A5F]/10 text-[#1E3A5F] border border-[#1E3A5F]/20 @endif">
                    {{ str_replace('_', ' ', strtoupper($case->status)) }}
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                    @if($case->priority === 'urgent') bg-[#C0392B]/10 text-[#C0392B] border border-[#C0392B]/20
                    @elseif($case->priority === 'high') bg-[#B7791F]/10 text-[#B7791F] border border-[#B7791F]/20
                    @elseif($case->priority === 'medium') bg-[#1E3A5F]/10 text-[#1E3A5F] border border-[#1E3A5F]/20
                    @else bg-[#6B7280]/10 text-[#6B7280] border border-[#6B7280]/20 @endif">
                    {{ strtoupper($case->priority) }} PRIORITY
                </span>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            @if(!in_array($case->status, ['resolved', 'closed']))
                <button onclick="document.getElementById('resolveModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-[#16805C] hover:bg-[#12684b] transition">
                    <i class="fa-solid fa-check mr-1.5 text-[#F4E7B2]"></i>
                    Resolve Case
                </button>
            @endif
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Details, Timeline, Communications -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Case Overview Card -->
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-[#E5E7EB] pb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#6B7280]">Case Information</span>
                    <span class="text-xs text-[#6B7280]">Created {{ \Carbon\Carbon::parse($case->created_at)->format('M d, Y h:i A') }}</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[#1F2937] mb-1">Description</h3>
                    <div class="text-sm text-[#1F2937] bg-[#F7F9FC] p-3.5 rounded-lg border border-[#E5E7EB] whitespace-pre-line leading-relaxed">
                        {{ $case->description ?: 'No detailed description provided.' }}
                    </div>
                </div>

                @if(!empty($case->form_data) && is_array($case->form_data))
                <div>
                    <h3 class="text-sm font-bold text-[#1F2937] mb-2">Form Data Payload</h3>
                    <div class="grid grid-cols-2 gap-2 text-xs bg-[#F7F9FC] p-3 rounded-lg border border-[#E5E7EB]">
                        @foreach($case->form_data as $key => $val)
                            <div>
                                <span class="font-semibold text-[#6B7280]">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                <span class="text-[#1F2937] ml-1">{{ is_array($val) ? json_encode($val) : $val }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Grounded AI Case Summary Drawer / Box -->
            <div class="bg-white rounded-xl border-2 border-[#1E3A5F]/20 p-5 shadow-sm relative">
                <div class="flex items-center justify-between mb-3 border-b border-[#E5E7EB] pb-2">
                    <div class="flex items-center space-x-2">
                        <span class="flex h-6 w-6 rounded-md bg-[#1E3A5F] text-[#F4E7B2] items-center justify-center text-xs font-bold shadow-sm">AI</span>
                        <h3 class="text-sm font-bold text-[#1E3A5F]">Grounded Case Assistant &amp; SLA Summary</h3>
                    </div>
                    <span class="text-xs text-[#B7791F] bg-[#F4E7B2]/60 px-2.5 py-0.5 rounded-full font-bold border border-[#C9A227]/30">Advisory Only</span>
                </div>
                <div id="aiSummaryContent" class="text-xs text-[#1F2937] space-y-2">
                    <p class="italic text-[#6B7280]">Loading grounded AI summary and sentiment analysis...</p>
                </div>
            </div>

            <!-- Dual-Channel Communication & Timeline -->
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="border-b border-[#E5E7EB] bg-[#F7F9FC] px-5 py-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-[#1F2937]">Activity Timeline &amp; Case Notes</h3>
                    <span class="text-xs text-[#6B7280]">{{ count($timeline) }} entries</span>
                </div>

                <!-- Add Comment Tabs Form -->
                <div class="p-5 border-b border-[#E5E7EB] bg-white">
                    <form action="/portal/hr-services/cases/{{ $case->id }}/comment" method="POST" class="space-y-3">
                        @csrf
                        <div class="flex items-center space-x-4 border-b border-[#E5E7EB] pb-2">
                            <label class="inline-flex items-center text-xs font-semibold text-[#1E3A5F] cursor-pointer">
                                <input type="radio" name="comment_type" value="public" checked class="text-[#1E3A5F] focus:ring-[#1E3A5F] mr-1.5" onclick="setCommentTypeStyle(false)">
                                Public Reply (Visible to Employee)
                            </label>
                            <label class="inline-flex items-center text-xs font-semibold text-[#B7791F] cursor-pointer">
                                <input type="radio" name="comment_type" value="internal" class="text-[#B7791F] focus:ring-[#B7791F] mr-1.5" onclick="setCommentTypeStyle(true)">
                                Internal HR Note (Confidential to HR)
                            </label>
                        </div>
                        <div>
                            <textarea name="message" id="commentMessage" rows="3" required placeholder="Type your response or internal case note..." class="w-full text-sm border-[#E5E7EB] rounded-lg focus:ring-[#1E3A5F] focus:border-[#1E3A5F] p-2.5 border text-[#1F2937]"></textarea>
                        </div>
                        <div class="flex justify-between items-center">
                            <span id="commentVisibilityBadge" class="text-xs text-[#6B7280] font-medium">Will be emailed/visible to the employee in Self-Service.</span>
                            <button type="submit" class="bg-[#1E3A5F] hover:bg-[#142A44] text-white text-xs font-semibold px-4 py-2 rounded-lg shadow-sm transition">
                                Post Update
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Chronological Timeline Stream -->
                <div class="p-5 space-y-4 max-h-[500px] overflow-y-auto">
                    @forelse($timeline as $entry)
                        <div class="flex space-x-3 text-xs">
                            <div class="flex-shrink-0 mt-0.5">
                                @if($entry['type'] === 'creation')
                                    <div class="h-6 w-6 rounded-full bg-[#1E3A5F]/10 text-[#1E3A5F] flex items-center justify-center font-bold">C</div>
                                @elseif($entry['type'] === 'resolution')
                                    <div class="h-6 w-6 rounded-full bg-[#16805C]/20 text-[#16805C] flex items-center justify-center font-bold">&check;</div>
                                @elseif($entry['type'] === 'internal_note')
                                    <div class="h-6 w-6 rounded-full bg-[#B7791F]/20 text-[#B7791F] flex items-center justify-center font-bold"><i class="fa-solid fa-lock text-[10px]"></i></div>
                                @else
                                    <div class="h-6 w-6 rounded-full bg-[#F7F9FC] text-[#6B7280] border border-[#E5E7EB] flex items-center justify-center font-bold"><i class="fa-solid fa-envelope text-[10px]"></i></div>
                                @endif
                            </div>
                            <div class="flex-1 bg-[#F7F9FC] rounded-lg p-3 border {{ $entry['type'] === 'internal_note' ? 'border-[#B7791F]/30 bg-amber-50/50' : 'border-[#E5E7EB]' }}">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-[#1F2937]">{{ $entry['actor'] ?? 'System' }}</span>
                                        @if($entry['type'] === 'internal_note')
                                            <span class="px-1.5 py-0.5 text-[10px] uppercase font-bold rounded bg-[#B7791F]/10 text-[#B7791F] border border-[#B7791F]/20">Internal HR Note</span>
                                        @elseif($entry['type'] === 'public_comment')
                                            <span class="px-1.5 py-0.5 text-[10px] uppercase font-bold rounded bg-[#1E3A5F]/10 text-[#1E3A5F] border border-[#1E3A5F]/20">Public Response</span>
                                        @endif
                                    </div>
                                    <span class="text-[#6B7280] text-[11px]">{{ \Carbon\Carbon::parse($entry['created_at'])->diffForHumans() }}</span>
                                </div>
                                <p class="text-[#1F2937] whitespace-pre-line">{{ $entry['description'] ?? '' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-[#6B7280] text-center py-4 text-xs">No timeline events recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Metadata, SLA, Requester Card -->
        <div class="space-y-6">
            <!-- Requester Card -->
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#6B7280]">Requester Profile</h3>
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-[#1E3A5F] text-[#F4E7B2] flex items-center justify-center font-bold text-sm shadow-sm">
                        {{ substr($case->employee->user->name ?? $case->employee->first_name ?? 'E', 0, 1) }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-[#1F2937]">{{ $case->employee->user->name ?? ($case->employee->first_name . ' ' . $case->employee->last_name ?? 'Employee') }}</div>
                        <div class="text-xs text-[#6B7280]">{{ $case->employee->employee_number ?? 'EMP-N/A' }}</div>
                    </div>
                </div>
                <div class="border-t border-[#E5E7EB] pt-3 space-y-1.5 text-xs">
                    <div class="flex justify-between text-[#6B7280]">
                        <span>Department:</span>
                        <span class="font-semibold text-[#1F2937]">{{ $case->employee->department->name ?? 'HR / General' }}</span>
                    </div>
                    <div class="flex justify-between text-[#6B7280]">
                        <span>Email:</span>
                        <span class="font-semibold text-[#1F2937]">{{ $case->employee->user->email ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Triage & Routing Assignment -->
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#6B7280]">Triage &amp; Queue Assignment</h3>
                <form action="/portal/hr-services/cases/{{ $case->id }}/assign" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#1F2937] mb-1">Queue</label>
                        <select name="queue_id" class="w-full text-xs border-[#E5E7EB] rounded-lg focus:ring-[#1E3A5F] focus:border-[#1E3A5F] p-2 border text-[#1F2937]">
                            <option value="">Unassigned</option>
                            @foreach($queues as $q)
                                <option value="{{ $q->id }}" {{ $case->assigned_queue_id == $q->id ? 'selected' : '' }}>{{ $q->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#1F2937] mb-1">Assigned Agent</label>
                        <select name="assigned_user_id" class="w-full text-xs border-[#E5E7EB] rounded-lg focus:ring-[#1E3A5F] focus:border-[#1E3A5F] p-2 border text-[#1F2937]">
                            <option value="">Unassigned</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}" {{ $case->assigned_user_id == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-[#1E3A5F] hover:bg-[#142A44] text-white text-xs font-semibold py-2 rounded-lg shadow-sm transition">
                        Update Assignment
                    </button>
                </form>
            </div>

            <!-- SLA Card -->
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-5 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#6B7280]">SLA Milestone Clock</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-[#6B7280]">Target Resolution:</span>
                        <span class="font-bold {{ ($case->due_at && \Carbon\Carbon::parse($case->due_at)->isPast() && !in_array($case->status, ['resolved', 'closed'])) ? 'text-[#C0392B]' : 'text-[#1F2937]' }}">
                            {{ $case->due_at ? \Carbon\Carbon::parse($case->due_at)->format('M d, Y h:i A') : 'None set' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[#6B7280]">Status:</span>
                        @if(in_array($case->status, ['resolved', 'closed']))
                            <span class="text-[#16805C] font-bold">Met / Completed</span>
                        @elseif($case->due_at && \Carbon\Carbon::parse($case->due_at)->isPast())
                            <span class="text-[#C0392B] font-bold">BREACHED</span>
                        @else
                            <span class="text-[#1E3A5F] font-bold">On Schedule</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Resolve Case -->
<div id="resolveModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-xl border border-[#E5E7EB]">
        <h3 class="text-lg font-bold text-[#1F2937]">Resolve Case #{{ $case->request_number }}</h3>
        <form action="/portal/hr-services/cases/{{ $case->id }}/resolve" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-[#1F2937] uppercase tracking-wider mb-1">Resolution Summary Note</label>
                <textarea name="resolution_notes" rows="4" required placeholder="Explain how the inquiry was addressed..." class="w-full text-xs border-[#E5E7EB] rounded-lg p-2.5 border focus:ring-[#16805C] focus:border-[#16805C] text-[#1F2937]"></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('resolveModal').classList.add('hidden')" class="px-4 py-2 text-xs font-medium text-[#1F2937] bg-[#F7F9FC] hover:bg-slate-200 rounded-lg border border-[#E5E7EB] transition">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#16805C] hover:bg-[#12684b] rounded-lg shadow-sm transition">Confirm Resolution</button>
            </div>
        </form>
    </div>
</div>

<script>
function setCommentTypeStyle(isInternal) {
    const badge = document.getElementById('commentVisibilityBadge');
    if (isInternal) {
        badge.innerText = "Confidential! Only HR personnel can read this note.";
        badge.className = "text-xs text-[#B7791F] font-semibold";
    } else {
        badge.innerText = "Will be emailed/visible to the employee in Self-Service.";
        badge.className = "text-xs text-[#6B7280] font-medium";
    }
}

// Fetch AI Summary asynchronously
document.addEventListener('DOMContentLoaded', function() {
    fetch('/api/hr-services/cases/{{ $case->id }}/ai-summary', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        const el = document.getElementById('aiSummaryContent');
        if (data && data.success && data.summary) {
            const s = data.summary;
            el.innerHTML = `
                <div class="space-y-2">
                    <p class="text-[#1F2937] leading-relaxed font-medium">${s.summary_text}</p>
                    <div class="flex flex-wrap gap-2 pt-2 border-t border-[#E5E7EB]">
                        <span class="px-2 py-0.5 rounded bg-[#F7F9FC] text-[#1E3A5F] border border-[#E5E7EB] font-semibold">Category: ${s.category_detected}</span>
                        <span class="px-2 py-0.5 rounded bg-[#F7F9FC] text-[#1F2937] border border-[#E5E7EB]">Sentiment: ${s.sentiment}</span>
                        <span class="px-2 py-0.5 rounded bg-[#F7F9FC] text-[#1F2937] border border-[#E5E7EB]">SLA: ${s.sla_status}</span>
                    </div>
                    ${s.recommended_next_action ? `<div class="p-2 rounded bg-[#F7F9FC] border border-[#C9A227]/30 text-[#1E3A5F] font-semibold">💡 Recommended Next Step: ${s.recommended_next_action}</div>` : ''}
                </div>
            `;
        } else {
            el.innerHTML = '<p class="text-[#6B7280]">Summary unavailable for this record.</p>';
        }
    })
    .catch(() => {
        document.getElementById('aiSummaryContent').innerHTML = '<p class="text-[#6B7280]">AI Assistant summary currently offline.</p>';
    });
});
</script>
@endsection
