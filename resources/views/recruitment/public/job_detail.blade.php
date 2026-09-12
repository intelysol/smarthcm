<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $posting->title }} — Careers at Flow HCM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col font-sans">
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 flex justify-between items-center">
            <a href="{{ route('careers.index') }}" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                &larr; Back to all roles
            </a>
            <span class="text-xs text-slate-400 font-mono">Job ID: {{ $posting->requisition?->requisition_number ?? 'REQ' }}</span>
        </div>
    </header>

    <main class="flex-grow max-w-4xl mx-auto w-full px-4 sm:px-6 py-10 space-y-8">
        <!-- Job Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <div class="flex items-center space-x-2 text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-2">
                <span>{{ $posting->requisition?->department?->department_name ?? 'General' }}</span>
                <span>&bull;</span>
                <span>{{ $posting->location_display ?? 'Remote' }}</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900">{{ $posting->title }}</h1>
            <p class="text-sm text-slate-500 mt-2">{{ $posting->summary }}</p>

            <div class="mt-8 border-t border-slate-100 pt-6 prose prose-sm max-w-none text-slate-700 leading-relaxed">
                {!! nl2br(e($posting->description)) !!}
            </div>
        </div>

        <!-- Application Form -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8" id="apply">
            <h2 class="text-xl font-bold text-slate-900 mb-2">Apply for this Position</h2>
            <p class="text-xs text-slate-500 mb-6">Submit your candidate details to enter our applicant tracking review.</p>

            <form action="/api/v1/public/recruitment/postings/{{ $posting->id }}/apply" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">First Name *</label>
                        <input type="text" name="first_name" required class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Last Name *</label>
                        <input type="text" name="last_name" required class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Email Address *</label>
                        <input type="email" name="email" required class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Phone Number</label>
                        <input type="tel" name="phone" class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Cover Letter / Note</label>
                    <textarea name="cover_letter" rows="4" placeholder="Briefly describe your experience and interest in this role..." class="w-full text-sm rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow transition">
                        Submit Application &rarr;
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} Flow HCM Enterprise Recruitment. Equal Opportunity Employer.
    </footer>
</body>
</html>
