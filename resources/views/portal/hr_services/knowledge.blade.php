@extends('portal.layout')

@section('title', 'HR Knowledge & Deflection')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-sm text-[#6B7280]">
                <a href="/portal/hr-services" class="hover:text-[#1E3A5F]">Command Center</a>
                <span>/</span>
                <span class="text-[#1F2937] font-semibold">Knowledge &amp; Deflection</span>
            </div>
            <h1 class="text-2xl font-bold text-[#1F2937] mt-1">HR Knowledge Base & Self-Service Deflection</h1>
            <p class="text-sm text-[#6B7280]">Empower employees with authoritative answers and monitor deflection efficiency</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="/portal/hr-services" class="inline-flex items-center px-4 py-2 border border-[#E5E7EB] rounded-lg shadow-sm text-sm font-medium text-[#1F2937] bg-white hover:bg-[#F7F9FC] hover:border-[#1E3A5F] transition">
                &larr; Back to Command Center
            </a>
        </div>
    </div>

    <!-- Deflection Analytics Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-[#E5E7EB] shadow-sm border-t-4 border-t-[#1E3A5F]">
            <div class="text-xs font-bold uppercase tracking-wider text-[#6B7280]">Total Deflected Searches</div>
            <div class="text-2xl font-black text-[#1F2937] mt-1">{{ number_format($deflection['total_deflections'] ?? 0) }}</div>
            <div class="text-xs text-[#16805C] font-semibold mt-1">Inquiries resolved without case ticket</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-[#E5E7EB] shadow-sm border-t-4 border-t-[#1E3A5F]">
            <div class="text-xs font-bold uppercase tracking-wider text-[#6B7280]">Deflection Efficiency</div>
            <div class="text-2xl font-black text-[#1E3A5F] mt-1">{{ $deflection['deflection_rate'] ?? 0 }}%</div>
            <div class="text-xs text-[#6B7280] mt-1">Target benchmark: &gt; 35%</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-[#E5E7EB] shadow-sm border-t-4 border-t-[#16805C]">
            <div class="text-xs font-bold uppercase tracking-wider text-[#6B7280]">Helpfulness Ratio</div>
            <div class="text-2xl font-black text-[#16805C] mt-1">{{ $deflection['helpfulness_rate'] ?? 0 }}%</div>
            <div class="text-xs text-[#6B7280] mt-1">Positive employee feedback</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-[#E5E7EB] shadow-sm border-t-4 border-t-[#C9A227]">
            <div class="text-xs font-bold uppercase tracking-wider text-[#6B7280]">Published Articles</div>
            <div class="text-2xl font-black text-[#1F2937] mt-1">{{ count($articles) }}</div>
            <div class="text-xs text-[#6B7280] mt-1">Active policy &amp; FAQ resources</div>
        </div>
    </div>

    <!-- Articles Directory -->
    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E7EB] bg-[#F7F9FC] flex items-center justify-between">
            <h3 class="font-bold text-[#1F2937] text-base">Authoritative Policy Articles &amp; Guides</h3>
        </div>
        <div class="divide-y divide-[#E5E7EB]">
            @forelse($articles as $article)
                <div class="p-5 hover:bg-[#F7F9FC] transition-colors flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="space-y-1 max-w-2xl">
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#1E3A5F]/10 text-[#1E3A5F] border border-[#1E3A5F]/20">
                                {{ $article->category->name ?? 'Policy' }}
                            </span>
                            <span class="text-xs text-[#6B7280]">Updated {{ \Carbon\Carbon::parse($article->updated_at)->format('M d, Y') }}</span>
                        </div>
                        <h4 class="text-base font-bold text-[#1F2937] hover:text-[#1E3A5F] transition-colors">{{ $article->title }}</h4>
                        <p class="text-xs text-[#6B7280] line-clamp-2">{{ strip_tags($article->content ?? $article->summary) }}</p>
                    </div>
                    <div class="flex items-center space-x-4 text-xs text-[#6B7280] flex-shrink-0">
                        <div class="text-center">
                            <div class="font-bold text-[#1F2937]">{{ $article->views_count ?? 0 }}</div>
                            <div class="text-[11px] text-[#6B7280]">Views</div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-[#16805C]">{{ $article->helpful_count ?? 0 }}</div>
                            <div class="text-[11px] text-[#6B7280]">Helpful</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-[#6B7280]">
                    No articles published yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
