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
            "name": "Industries",
            "item": "{{ url('/industries') }}"
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
            <span class="text-[#F4E7B2] font-semibold">Industries</span>
        </nav>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            Industry Workforce Solutions
        </h1>
        <p class="text-sm sm:text-base text-slate-200 max-w-3xl leading-relaxed">
            SmartHCM is tailored for high-concurrency, complex shift environments. Explore how our platform solves industry-specific shift, certification, and regulatory challenges.
        </p>
    </div>
</section>

<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($industries as $ind)
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-[#1E3A5F] transition flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-[#1E3A5F]/10 text-[#1E3A5F] flex items-center justify-center text-lg">
                            <i class="{{ $ind['icon'] }}"></i>
                        </div>
                        <h2 class="text-base font-bold text-[#1E3A5F]">{{ $ind['name'] }}</h2>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $ind['tagline'] }}</p>
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-100">
                        <a href="{{ url('/industries/' . $ind['slug']) }}" class="text-xs font-bold text-[#1E3A5F] hover:text-[#C9A227] flex items-center justify-between">
                            <span>View Industry Model</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
