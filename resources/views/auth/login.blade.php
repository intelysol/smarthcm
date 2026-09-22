<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In &bull; Enterprise Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --brand-navy: #1E3A5F;
            --brand-dark-navy: #142A44;
            --brand-gold: #C9A227;
            --brand-gold-light: #F4E7B2;
            --bg-canvas: #F7F9FC;
        }
    </style>
</head>
<body class="bg-[#F7F9FC] text-[#1F2937] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-[#1E3A5F] text-[#C9A227] font-bold text-2xl shadow-md mb-4 border border-[#142A44]">
                HCM
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-[#1E3A5F]">Enterprise Platform</h1>
            <p class="text-xs text-[#6B7280] mt-1">Multi-Tenant Corporate Authentication Portal</p>
        </div>

        <!-- Notification Messages -->
        @if(session('warning'))
            <div class="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center">
                <svg class="w-4 h-4 mr-2 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        @if(session('status'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center">
                <svg class="w-4 h-4 mr-2 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] p-8">
            <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-[#1E3A5F] mb-1.5">
                        Corporate Email
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus
                        autocomplete="email"
                        placeholder="user@enterprise.internal"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-[#E5E7EB] text-sm text-[#1F2937] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1E3A5F] focus:border-transparent transition"
                    >
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-[#1E3A5F] mb-1.5">
                        Password
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required 
                        autocomplete="current-password"
                        placeholder="••••••••••••"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-[#E5E7EB] text-sm text-[#1F2937] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1E3A5F] focus:border-transparent transition"
                    >
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center text-[#6B7280] cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#1E3A5F] focus:ring-[#1E3A5F] mr-2">
                        Remember session
                    </label>
                    <span class="text-[#6B7280]">Protected by MFA</span>
                </div>

                <button 
                    type="submit" 
                    class="w-full py-2.5 px-4 rounded-lg bg-[#1E3A5F] hover:bg-[#142A44] text-white font-medium text-sm transition shadow-sm flex items-center justify-center space-x-2"
                >
                    <span>Sign In to Workplace</span>
                    <span class="text-[#C9A227]">&rarr;</span>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-xs text-[#6B7280] space-y-1">
            <p>&copy; {{ date('Y') }} Enterprise Application Platform. All rights reserved.</p>
            <p>ISO 27001 Certified &bull; SOC 2 Type II Compliant &bull; End-to-End Encrypted</p>
        </div>
    </div>
</body>
</html>
