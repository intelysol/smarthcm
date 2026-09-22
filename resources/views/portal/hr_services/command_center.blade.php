@extends('portal.layout')

@section('title', 'HR Service Delivery Command Center')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="bg-white border border-[#E5E7EB] p-6 rounded-2xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-[#1E3A5F] text-[#F4E7B2] flex items-center justify-center text-2xl font-black shadow-sm">
                <i class="fa-solid fa-tower-broadcast"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl font-bold text-[#1F2937] tracking-tight">HR Service Delivery Command Center</h1>
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-[#1E3A5F]/10 text-[#1E3A5F] border border-[#1E3A5F]/20 uppercase tracking-wider">OPERATIONS</span>
                </div>
                <p class="text-xs text-[#6B7280] mt-0.5">
                    Operational workforce case cockpit &middot; SLA monitoring &middot; Queue capacity &middot; Deflection analytics
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('portal.hr-services.cases') }}" class="px-4 py-2 bg-[#1E3A5F] hover:bg-[#142A44] text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center space-x-2">
                <i class="fa-solid fa-inbox text-[#C9A227]"></i>
                <span>Open Case Inbox ({{ $metrics['summary']['open_cases'] }})</span>
            </a>
            <a href="{{ route('portal.hr-services.catalog') }}" class="px-3.5 py-2 bg-white hover:bg-[#F7F9FC] text-[#1F2937] rounded-xl text-xs font-semibold border border-[#E5E7EB] hover:border-[#1E3A5F] transition flex items-center space-x-1.5 shadow-sm">
                <i class="fa-solid fa-list-check text-[#1E3A5F]"></i>
                <span>Service Catalog</span>
            </a>
        </div>
    </div>

    <!-- Core Metrics Row -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="p-4 rounded-xl bg-white border border-[#E5E7EB] shadow-sm border-t-4 border-t-[#1E3A5F]">
            <div class="text-[10px] uppercase font-bold text-[#6B7280] tracking-wider">Open Cases</div>
            <div class="text-2xl font-black text-[#1F2937] mt-1">{{ $metrics['summary']['open_cases'] }}</div>
            <div class="text-[10px] text-[#2563EB] font-medium mt-0.5">+{{ $metrics['summary']['new_today'] }} new today</div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-[#E5E7EB] shadow-sm border-t-4 border-t-[#16805C]">
            <div class="text-[10px] uppercase font-bold text-[#6B7280] tracking-wider">SLA Compliance</div>
            <div class="text-2xl font-black text-[#16805C] mt-1">{{ $metrics['summary']['sla_compliance_rate'] }}%</div>
            <div class="text-[10px] text-[#6B7280] mt-0.5">Target &ge; 90%</div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-[#E5E7EB] shadow-sm border-t-4 border-t-[#C0392B]">
            <div class="text-[10px] uppercase font-bold text-[#6B7280] tracking-wider">Overdue / Risk</div>
            <div class="text-2xl font-black {{ $metrics['summary']['overdue'] > 0 ? 'text-[#C0392B]' : 'text-[#1F2937]' }} mt-1">
                {{ $metrics['summary']['overdue'] }}
            </div>
            <div class="text-[10px] text-[#B7791F] font-medium mt-0.5">{{ $metrics['summary']['due_soon'] }} due in 24h</div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-[#E5E7EB] shadow-sm border-t-4 border-t-[#B7791F]">
            <div class="text-[10px] uppercase font-bold text-[#6B7280] tracking-wider">Unassigned</div>
            <div class="text-2xl font-black {{ $metrics['summary']['unassigned'] > 0 ? 'text-[#B7791F]' : 'text-[#1F2937]' }} mt-1">
                {{ $metrics['summary']['unassigned'] }}
            </div>
            <div class="text-[10px] text-[#6B7280] mt-0.5">Requires triage</div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-[#E5E7EB] shadow-sm border-t-4 border-t-[#1E3A5F]">
            <div class="text-[10px] uppercase font-bold text-[#6B7280] tracking-wider">Avg Resolution</div>
            <div class="text-2xl font-black text-[#1E3A5F] mt-1">{{ $metrics['summary']['average_resolution_days'] }} <span class="text-xs font-normal text-[#6B7280]">days</span></div>
            <div class="text-[10px] text-[#6B7280] mt-0.5">{{ $metrics['summary']['resolved_today'] }} resolved today</div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-[#E5E7EB] shadow-sm border-t-4 border-t-[#C9A227]">
            <div class="text-[10px] uppercase font-bold text-[#6B7280] tracking-wider">CSAT Score</div>
            <div class="text-2xl font-black text-[#B7791F] mt-1 flex items-center">
                {{ $metrics['summary']['csat_average'] }} <i class="fa-solid fa-star text-xs ml-1 text-[#C9A227]"></i>
            </div>
            <div class="text-[10px] text-[#6B7280] mt-0.5">{{ $metrics['summary']['total_csat_responses'] }} responses</div>
        </div>
    </div>

    <!-- Middle Grid: Queue Health & SLA Risk Cases -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Queue Health (2 columns) -->
        <div class="lg:col-span-2 bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
                <h3 class="text-sm font-bold text-[#1E3A5F] flex items-center">
                    <i class="fa-solid fa-layer-group text-[#C9A227] mr-2"></i> Operational Queues &amp; Capacity
                </h3>
                <span class="text-[11px] text-[#6B7280]">Workload Threshold: 10 cases/member</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @forelse($metrics['queue_health'] as $queue)
                    <div class="p-4 rounded-xl bg-[#F7F9FC] border {{ $queue['is_over_capacity'] ? 'border-[#C0392B]/40 bg-red-50/40' : 'border-[#E5E7EB]' }} space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-[#1F2937]">{{ $queue['name'] }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ $queue['is_over_capacity'] ? 'bg-[#C0392B]/10 text-[#C0392B] border border-[#C0392B]/20' : 'bg-white text-[#1F2937] border border-[#E5E7EB]' }}">
                                {{ $queue['capacity_percent'] }}%
                            </span>
                        </div>
                        <div class="w-full bg-[#E5E7EB] h-2 rounded-full overflow-hidden">
                            <div class="h-full {{ $queue['is_over_capacity'] ? 'bg-[#C0392B]' : ($queue['capacity_percent'] > 75 ? 'bg-[#B7791F]' : 'bg-[#1E3A5F]') }}" style="width: {{ min(100, $queue['capacity_percent']) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-[#6B7280]">
                            <span>Active: <b class="text-[#1F2937]">{{ $queue['active_cases'] }}</b> cases</span>
                            <span>Members: <b class="text-[#1F2937]">{{ $queue['member_count'] }}</b> available</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 text-center py-6 text-xs text-[#6B7280]">
                        No operational queues configured.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Deflection & Self-Service Widget -->
        <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
                    <h3 class="text-sm font-bold text-[#1E3A5F] flex items-center">
                        <i class="fa-solid fa-shield-halved text-[#16805C] mr-2"></i> Self-Service Deflection
                    </h3>
                </div>
                <div class="text-center py-4">
                    <div class="text-4xl font-black text-[#16805C]">{{ $metrics['deflection']['deflection_rate'] }}%</div>
                    <div class="text-xs text-[#6B7280] mt-1">Inquiries resolved without human intervention</div>
                </div>
                <div class="space-y-2 text-xs text-[#1F2937] pt-2 border-t border-[#E5E7EB]">
                    <div class="flex justify-between">
                        <span class="text-[#6B7280]">Published Articles:</span>
                        <span class="font-semibold text-[#1F2937]">{{ $metrics['deflection']['total_articles'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#6B7280]">Knowledge Reads:</span>
                        <span class="font-semibold text-[#1F2937]">{{ number_format($metrics['deflection']['total_views']) }}</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('portal.hr-services.knowledge') }}" class="w-full py-2 bg-[#F7F9FC] hover:bg-[#1E3A5F] text-[#1E3A5F] hover:text-white text-xs font-semibold text-center rounded-xl border border-[#E5E7EB] hover:border-[#1E3A5F] transition">
                Manage Knowledge Center &rarr;
            </a>
        </div>
    </div>

    <!-- Bottom SLA Risk Table -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
            <h3 class="text-sm font-bold text-[#1E3A5F] flex items-center">
                <i class="fa-solid fa-triangle-exclamation text-[#B7791F] mr-2"></i> High Priority &amp; SLA At-Risk Cases
            </h3>
            <a href="{{ route('portal.hr-services.cases') }}?status=open" class="text-xs text-[#1E3A5F] hover:text-[#142A44] font-semibold flex items-center">
                View all open cases &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#1F2937]">
                <thead class="text-[10px] uppercase font-bold text-[#6B7280] border-b border-[#E5E7EB] bg-[#F7F9FC]">
                    <tr>
                        <th class="py-2.5 px-3">Case ID</th>
                        <th class="py-2.5 px-3">Subject / Service</th>
                        <th class="py-2.5 px-3">Employee</th>
                        <th class="py-2.5 px-3">Queue</th>
                        <th class="py-2.5 px-3">Priority</th>
                        <th class="py-2.5 px-3">SLA Due</th>
                        <th class="py-2.5 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($metrics['sla_risk_cases'] as $risk)
                        <tr class="hover:bg-[#F7F9FC] transition">
                            <td class="py-3 px-3 font-mono font-bold text-[#1E3A5F]">{{ $risk['case_number'] }}</td>
                            <td class="py-3 px-3">
                                <div class="font-semibold text-[#1F2937]">{{ $risk['subject'] }}</div>
                                <div class="text-[10px] text-[#6B7280]">{{ $risk['service'] }}</div>
                            </td>
                            <td class="py-3 px-3 text-[#1F2937] font-medium">{{ $risk['employee'] }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-[#F7F9FC] text-[#1F2937] border border-[#E5E7EB]">
                                    {{ $risk['queue'] }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $risk['priority'] === 'CRITICAL' || $risk['priority'] === 'HIGH' ? 'bg-[#C0392B]/10 text-[#C0392B] border border-[#C0392B]/20' : 'bg-[#F7F9FC] text-[#1F2937] border border-[#E5E7EB]' }}">
                                    {{ $risk['priority'] }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                @if($risk['is_breached'])
                                    <span class="text-[#C0392B] font-bold flex items-center">
                                        <i class="fa-solid fa-circle-exclamation mr-1"></i> Breached
                                    </span>
                                @else
                                    <span class="text-[#B7791F] font-bold">
                                        {{ date('H:i, M d', strtotime($risk['due_at'])) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right">
                                <a href="{{ route('portal.hr-services.cases.detail', $risk['id']) }}" class="px-2.5 py-1 bg-white hover:bg-[#1E3A5F] text-[#1E3A5F] hover:text-white rounded text-xs font-semibold transition border border-[#1E3A5F] shadow-sm">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-[#6B7280]">
                                <i class="fa-solid fa-circle-check text-[#16805C] text-lg mb-1 block"></i>
                                All active cases are currently within their designated SLA timeframes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
