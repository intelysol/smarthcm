@extends('public.layouts.marketing')

@section('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "{{ url('/') }}"
        },
        {
            "@@type": "ListItem",
            "position": 2,
            "name": "FAQ",
            "item": "{{ url('/faq') }}"
        }
    ]
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        @php $allFaqsList = []; foreach($faqs as $cat) { foreach($cat['questions'] as $q) { $allFaqsList[] = $q; } } @endphp
        @foreach($allFaqsList as $index => $item)
        {
            "@@type": "Question",
            "name": "{{ addslashes($item['q']) }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ addslashes($item['a']) }}"
            }
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endsection

@section('content')

<section class="bg-[#1E3A5F] text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-4xl mx-auto space-y-4">
        <nav class="text-xs text-slate-300 flex items-center space-x-2" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-white">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-[#F4E7B2] font-semibold">FAQ</span>
        </nav>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            Frequently Asked Questions
        </h1>
        <p class="text-sm sm:text-base text-slate-200 leading-relaxed">
            Factual answers regarding platform architecture, mobile attendance, gross-to-net payroll, data isolation, and deployment workflows.
        </p>
    </div>
</section>

<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-4xl mx-auto space-y-12">
        @foreach($faqs as $catKey => $catData)
            <div class="space-y-4">
                <div class="border-b border-slate-200 pb-2">
                    <h2 class="text-xl font-bold text-[#1E3A5F]">{{ $catData['category'] }}</h2>
                </div>
                <div class="space-y-4">
                    @foreach($catData['questions'] as $qItem)
                        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-2">
                            <h3 class="text-sm font-bold text-[#1E3A5F] flex items-center">
                                <i class="fa-solid fa-circle-question text-[#C9A227] mr-2.5"></i>
                                {{ $qItem['q'] }}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed pl-6">{{ $qItem['a'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>

@endsection
