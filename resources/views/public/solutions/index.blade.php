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
            "name": "Solutions",
            "item": "{{ url('/solutions') }}"
        }
    ]
}
</script>
@endsection

@section('content')

<section class="bg-[#1E3A5F] text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-7xl mx-auto space-y-4">
        <nav class="text-xs text-slate-300 flex items-center space-x-2" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-white">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-[#F4E7B2] font-semibold">Solutions</span>
        </nav>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            Enterprise HCM Business Solutions
        </h1>
        <p class="text-sm sm:text-base text-slate-200 max-w-3xl leading-relaxed">
            Solve acute operational workforce challenges. SmartHCM maps enterprise business problems directly to automated workflows, robust audit controls, and real-time intelligence.
        </p>
    </div>
</section>

<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($solutions as $sol)
                <div class="p-8 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#1E3A5F] transition flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-[#1E3A5F]/10 text-[#1E3A5F] flex items-center justify-center text-xl">
                            <i class="{{ $sol['icon'] }}"></i>
                        </div>
                        <h2 class="text-lg font-bold text-[#1E3A5F]">{{ $sol['title'] }}</h2>
                        <p class="text-xs font-semibold text-[#C9A227]">{{ $sol['tagline'] }}</p>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $sol['problem'] }}</p>
                    </div>

                    <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ url('/solutions/' . $sol['slug']) }}" class="text-xs font-bold text-[#1E3A5F] hover:text-[#C9A227] flex items-center">
                            <span>Read Solution Blueprint</span>
                            <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#142A44] text-white text-center">
    <div class="max-w-3xl mx-auto space-y-4">
        <h2 class="text-2xl font-bold">Have a Specific Workforce Challenge?</h2>
        <p class="text-xs text-slate-300">Our HCM solutions architects can map your organizational requirements to tailored automated workflows.</p>
        <div class="pt-2">
            <a href="{{ url('/demo') }}" class="px-7 py-3 rounded-xl bg-[#C9A227] text-[#1E3A5F] font-bold text-xs shadow hover:bg-[#B08E20] inline-block">
                Schedule Solution Session
            </a>
        </div>
    </div>
</section>

@endsection
