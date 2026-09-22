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
            "name": "Request a Demo",
            "item": "{{ url('/demo') }}"
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
            <span class="text-[#F4E7B2] font-semibold">Request a Demo</span>
        </nav>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
            Schedule an Enterprise SmartHCM Walkthrough
        </h1>
        <p class="text-sm sm:text-base text-slate-200 leading-relaxed">
            Experience our live cloud environment with pre-seeded personas: Super Admin, Tenant Admin, HR Director, People Manager, and Employee Self-Service.
        </p>
    </div>
</section>

<section class="py-16 px-4 sm:px-6 lg:px-8 bg-[#F7F9FC]">
    <div class="max-w-4xl mx-auto">
        <div class="p-8 sm:p-10 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-6">
            <div>
                <h2 class="text-xl font-bold text-[#1E3A5F]">Tell Us About Your Workforce Scale</h2>
                <p class="text-xs text-slate-600 mt-1">Our solutions engineering team will tailor your walkthrough to your operational requirements.</p>
            </div>

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <div><i class="fa-solid fa-circle-exclamation mr-1.5"></i> {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('public.demo.submit') }}" method="POST" class="space-y-5">
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
                        <label for="work_email" class="block text-xs font-semibold text-slate-700 mb-1">Business Work Email *</label>
                        <input type="email" name="work_email" id="work_email" required value="{{ old('work_email') }}" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-[#1E3A5F] focus:border-transparent outline-none">
                    </div>
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">Direct Phone Number *</label>
                        <input type="text" name="phone" id="phone" required value="{{ old('phone') }}" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-[#1E3A5F] focus:border-transparent outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="country" class="block text-xs font-semibold text-slate-700 mb-1">Country / Region</label>
                        <input type="text" name="country" id="country" value="{{ old('country', 'United States') }}" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-[#1E3A5F] focus:border-transparent outline-none">
                    </div>
                    <div>
                        <label for="organization_size" class="block text-xs font-semibold text-slate-700 mb-1">Total Employee Headcount *</label>
                        <select name="organization_size" id="organization_size" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-[#1E3A5F] focus:border-transparent outline-none bg-white">
                            <option value="">Select workforce size...</option>
                            <option value="1-50" {{ old('organization_size') === '1-50' ? 'selected' : '' }}>1 – 50 Employees</option>
                            <option value="51-250" {{ old('organization_size') === '51-250' ? 'selected' : '' }}>51 – 250 Employees</option>
                            <option value="251-1000" {{ old('organization_size') === '251-1000' ? 'selected' : '' }}>251 – 1,000 Employees</option>
                            <option value="1000-5000" {{ old('organization_size') === '1000-5000' ? 'selected' : '' }}>1,000 – 5,000 Employees</option>
                            <option value="5000+" {{ old('organization_size') === '5000+' ? 'selected' : '' }}>5,000+ Enterprise Scale</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="hcm_requirements" class="block text-xs font-semibold text-slate-700 mb-1">Primary Modules of Interest</label>
                    <input type="text" name="hcm_requirements" id="hcm_requirements" value="{{ old('hcm_requirements') }}" placeholder="e.g. Mobile GPS Attendance, Core HR, Payroll, Workforce Rostering" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-[#1E3A5F] focus:border-transparent outline-none">
                </div>

                <div>
                    <label for="message" class="block text-xs font-semibold text-slate-700 mb-1">Additional Project Context or Scheduling Preferences</label>
                    <textarea name="message" id="message" rows="3" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-[#1E3A5F] focus:border-transparent outline-none" placeholder="Let us know your implementation timeframe or specific integration needs...">{{ old('message') }}</textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" onclick="window.trackHcmEvent('demo_submitted');" class="w-full py-3.5 rounded-xl bg-[#C9A227] hover:bg-[#B08E20] text-[#1E3A5F] font-bold text-xs shadow-lg transition transform hover:-translate-y-0.5">
                        Schedule My Custom Demo
                    </button>
                </div>

                <p class="text-[11px] text-center text-slate-500">
                    We respect your data. Your information is never sold and is processed strictly under our <a href="{{ url('/privacy') }}" class="underline text-[#1E3A5F]">Privacy Policy</a>.
                </p>
            </form>
        </div>
    </div>
</section>

@endsection
