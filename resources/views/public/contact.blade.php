@extends('public.layouts.marketing')

@section('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "ContactPage",
    "name": "Contact SmartHCM",
    "description": "Contact SmartHCM enterprise sales, technical support, and partnership directors.",
    "url": "{{ url('/contact') }}"
}
</script>
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
            "name": "Contact",
            "item": "{{ url('/contact') }}"
        }
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
            <span class="text-[#F4E7B2] font-semibold">Contact</span>
        </nav>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            Contact SmartHCM Enterprise Team
        </h1>
        <p class="text-sm sm:text-base text-slate-200 leading-relaxed">
            Connect directly with our workforce solutions architects, sales specialists, and technical account directors.
        </p>
    </div>
</section>

<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <!-- Contact Info -->
            <div class="lg:col-span-5 space-y-6">
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-base font-bold text-[#1E3A5F]">Headquarters &amp; Direct Channels</h2>
                    <ul class="space-y-3 text-xs text-slate-700">
                        <li class="flex items-start">
                            <i class="fa-solid fa-building text-[#C9A227] text-sm mr-3 mt-0.5"></i>
                            <div>
                                <strong class="block text-slate-900">Enterprise Entity:</strong>
                                Intelysol SmartHCM Enterprise Solutions
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-location-dot text-[#C9A227] text-sm mr-3 mt-0.5"></i>
                            <div>
                                <strong class="block text-slate-900">Office Location:</strong>
                                100 Enterprise Boulevard, Suite 500<br>Austin, TX 78701, United States
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-envelope text-[#C9A227] text-sm mr-3 mt-0.5"></i>
                            <div>
                                <strong class="block text-slate-900">Sales Inquiries:</strong>
                                <a href="mailto:sales@smarthcm.com" class="text-[#1E3A5F] hover:underline font-medium">sales@smarthcm.com</a>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-headset text-[#C9A227] text-sm mr-3 mt-0.5"></i>
                            <div>
                                <strong class="block text-slate-900">Technical Support:</strong>
                                <a href="mailto:support@smarthcm.com" class="text-[#1E3A5F] hover:underline font-medium">support@smarthcm.com</a>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-phone text-[#C9A227] text-sm mr-3 mt-0.5"></i>
                            <div>
                                <strong class="block text-slate-900">Direct Telephone:</strong>
                                +1 (800) 555-HCM1 (Toll Free)
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="p-6 rounded-2xl bg-[#1E3A5F] text-white space-y-3">
                    <h3 class="text-sm font-bold text-[#F4E7B2]">Looking for a Full Platform Demo?</h3>
                    <p class="text-xs text-slate-200">
                        Schedule a dedicated session with an HCM architect to walk through real shift rosters, mobile geofenced punches, and automated payroll runs.
                    </p>
                    <a href="{{ url('/demo') }}" class="inline-block px-4 py-2 rounded-lg bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F] text-xs font-bold transition">
                        Go to Demo Request Form &rarr;
                    </a>
                </div>
            </div>

            <!-- Validated Contact Form -->
            <div class="lg:col-span-7">
                <div class="p-8 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-bold text-[#1E3A5F]">Send an Inquiry</h2>
                        <p class="text-xs text-slate-600 mt-1">Our team responds to verified enterprise inquiries within one business day.</p>
                    </div>

                    @if ($errors->any())
                        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs space-y-1">
                            @foreach ($errors->all() as $error)
                                <div><i class="fa-solid fa-circle-exclamation mr-1.5"></i> {{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('public.contact.submit') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Full Name *</label>
                                <input type="text" name="name" id="name" required value="{{ old('name') }}" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-[#1E3A5F] focus:border-transparent outline-none">
                            </div>
                            <div>
                                <label for="company" class="block text-xs font-semibold text-slate-700 mb-1">Company / Organization *</label>
                                <input type="text" name="company" id="company" required value="{{ old('company') }}" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-[#1E3A5F] focus:border-transparent outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="work_email" class="block text-xs font-semibold text-slate-700 mb-1">Work Email Address *</label>
                                <input type="email" name="work_email" id="work_email" required value="{{ old('work_email') }}" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-[#1E3A5F] focus:border-transparent outline-none">
                            </div>
                            <div>
                                <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-[#1E3A5F] focus:border-transparent outline-none">
                            </div>
                        </div>

                        <div>
                            <label for="message" class="block text-xs font-semibold text-slate-700 mb-1">Message / Requirements *</label>
                            <textarea name="message" id="message" rows="4" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-[#1E3A5F] focus:border-transparent outline-none" placeholder="Describe your current HCM challenges or questions...">{{ old('message') }}</textarea>
                        </div>

                        <div>
                            <button type="submit" onclick="window.trackHcmEvent('contact_submitted');" class="w-full py-3 rounded-xl bg-[#1E3A5F] hover:bg-[#142A44] text-white font-bold text-xs shadow transition">
                                Submit Inquiry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
