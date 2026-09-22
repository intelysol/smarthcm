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
            "name": "Pricing",
            "item": "{{ url('/pricing') }}"
        }
    ]
}
</script>
@endsection

@section('content')

<section class="bg-[#1E3A5F] text-white py-16 px-4 sm:px-6 lg:px-8 border-b border-[#142A44]">
    <div class="max-w-7xl mx-auto text-center space-y-4">
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#C9A227]/20 text-[#F4E7B2] border border-[#C9A227]/30">
            Enterprise HCM Editions
        </span>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            Transparent Editions for Growing &amp; Global Organizations
        </h1>
        <p class="text-sm sm:text-base text-slate-200 max-w-2xl mx-auto leading-relaxed">
            Choose the edition that fits your workforce operational scale. Every tier includes our core security model, mobile self-service, and verified audit logging.
        </p>
    </div>
</section>

<section class="py-20 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
            @foreach($tiers as $key => $tier)
                <div class="rounded-2xl bg-white border {{ $tier['highlight'] ? 'border-[#C9A227] shadow-xl ring-2 ring-[#C9A227]/30' : 'border-slate-200 shadow-sm' }} p-8 flex flex-col justify-between relative">
                    @if($tier['highlight'])
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-[#C9A227] text-[#1E3A5F] font-bold text-[10px] uppercase tracking-wider shadow">
                            Most Popular for Mid-Market
                        </div>
                    @endif

                    <div class="space-y-6">
                        <div>
                            <h2 class="text-xl font-bold text-[#1E3A5F]">{{ $tier['name'] }}</h2>
                            <p class="text-xs text-slate-600 mt-1">{{ $tier['target'] }}</p>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-center">
                            <span class="text-xs font-bold text-[#1E3A5F]">{{ $tier['user_limit'] }}</span>
                        </div>

                        <div class="space-y-3 pt-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Included Capabilities:</span>
                            <ul class="space-y-2.5 text-xs text-slate-700">
                                @foreach($tier['features'] as $feat)
                                    <li class="flex items-start">
                                        <i class="fa-solid fa-check text-[#16805C] text-xs mr-2.5 mt-0.5"></i>
                                        <span>{{ $feat }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="pt-8 mt-8 border-t border-slate-100">
                        <a href="{{ url('/demo') }}" class="w-full block py-3 rounded-xl text-center text-xs font-bold transition shadow-sm {{ $tier['highlight'] ? 'bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F]' : 'bg-[#1E3A5F] hover:bg-[#142A44] text-white' }}">
                            {{ $tier['cta_text'] }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-16 p-8 rounded-2xl bg-white border border-slate-200 text-center max-w-3xl mx-auto space-y-4">
            <h3 class="text-lg font-bold text-[#1E3A5F]">Need Custom Multi-Tenant Deployment or On-Premises Isolation?</h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                SmartHCM supports custom enterprise agreements for conglomerates, sovereign cloud deployments, and dedicated database clusters.
            </p>
            <div>
                <a href="{{ url('/contact') }}" class="text-xs font-bold text-[#1E3A5F] hover:text-[#C9A227] inline-flex items-center">
                    <span>Contact Enterprise HCM Licensing</span>
                    <i class="fa-solid fa-arrow-right ml-1.5 text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
