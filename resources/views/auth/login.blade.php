<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In &bull; Enterprise Platform</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
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
            <form id="login-form" method="POST" action="{{ route('login.submit') }}" class="space-y-5">
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
                    <a href="javascript:void(0)" onclick="alert('For development demo accounts, the default password is defined by DEMO_USER_PASSWORD in your local .env (default: Demo1234!@#$). For production accounts, please contact your Organization Administrator.')" class="text-xs text-[#1E3A5F] hover:underline font-medium">Forgot password?</a>
                </div>

                <button 
                    type="submit" 
                    class="w-full py-2.5 px-4 rounded-lg bg-[#1E3A5F] hover:bg-[#142A44] text-white font-medium text-sm transition shadow-sm flex items-center justify-center space-x-2"
                >
                    <span>Sign In to Workplace</span>
                    <span class="text-[#C9A227]">&rarr;</span>
                </button>
            </form>

            @if(!app()->isProduction())
            <!-- Development & Demo Quick-Login Picker -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1 text-[#C9A227]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                        </svg>
                        Demo Access Personas
                    </span>
                    <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-mono">dev-only</span>
                </div>
                <p class="text-[11px] text-slate-500 mb-2.5">
                    Click any persona to auto-sign in. Universal password: <code class="bg-amber-50 text-amber-900 border border-amber-200 px-1.5 py-0.5 rounded font-mono font-semibold">Demo1234!@#$</code>
                </p>
                <div class="grid grid-cols-1 gap-1.5 text-xs">
                    <button type="button" onclick="loginAs('superadmin@example.test', 'Demo1234!@#$')" class="text-left px-3 py-2 rounded-lg border border-slate-200 hover:border-[#1E3A5F] hover:bg-slate-50 transition flex items-center justify-between group">
                        <div>
                            <span class="font-bold text-slate-800 group-hover:text-[#1E3A5F]">Platform Super Admin</span>
                            <span class="text-[11px] text-slate-500 font-mono block">superadmin@example.test</span>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-500 group-hover:text-[#C9A227] flex items-center">Sign in &rarr;</span>
                    </button>
                    <button type="button" onclick="loginAs('admin@example.test', 'Demo1234!@#$')" class="text-left px-3 py-2 rounded-lg border border-slate-200 hover:border-[#1E3A5F] hover:bg-slate-50 transition flex items-center justify-between group">
                        <div>
                            <span class="font-bold text-slate-800 group-hover:text-[#1E3A5F]">Tenant Admin</span>
                            <span class="text-[11px] text-slate-500 font-mono block">admin@example.test</span>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-500 group-hover:text-[#C9A227] flex items-center">Sign in &rarr;</span>
                    </button>
                    <button type="button" onclick="loginAs('hr@example.test', 'Demo1234!@#$')" class="text-left px-3 py-2 rounded-lg border border-slate-200 hover:border-[#1E3A5F] hover:bg-slate-50 transition flex items-center justify-between group">
                        <div>
                            <span class="font-bold text-slate-800 group-hover:text-[#1E3A5F]">HR Administrator</span>
                            <span class="text-[11px] text-slate-500 font-mono block">hr@example.test</span>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-500 group-hover:text-[#C9A227] flex items-center">Sign in &rarr;</span>
                    </button>
                    <button type="button" onclick="loginAs('manager@example.test', 'Demo1234!@#$')" class="text-left px-3 py-2 rounded-lg border border-slate-200 hover:border-[#1E3A5F] hover:bg-slate-50 transition flex items-center justify-between group">
                        <div>
                            <span class="font-bold text-slate-800 group-hover:text-[#1E3A5F]">People Manager</span>
                            <span class="text-[11px] text-slate-500 font-mono block">manager@example.test</span>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-500 group-hover:text-[#C9A227] flex items-center">Sign in &rarr;</span>
                    </button>
                    <button type="button" onclick="loginAs('employee@example.test', 'Demo1234!@#$')" class="text-left px-3 py-2 rounded-lg border border-slate-200 hover:border-[#1E3A5F] hover:bg-slate-50 transition flex items-center justify-between group">
                        <div>
                            <span class="font-bold text-slate-800 group-hover:text-[#1E3A5F]">Employee (Alex Chen)</span>
                            <span class="text-[11px] text-slate-500 font-mono block">employee@example.test</span>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-500 group-hover:text-[#C9A227] flex items-center">Sign in &rarr;</span>
                    </button>
                </div>
            </div>

            <script>
                function loginAs(email, password) {
                    document.getElementById('email').value = email;
                    document.getElementById('password').value = password;
                    const form = document.getElementById('login-form');
                    if (form) {
                        form.submit();
                    }
                }
                function fillCreds(email, password) {
                    document.getElementById('email').value = email;
                    document.getElementById('password').value = password;
                }
            </script>
            @endif
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-xs text-[#6B7280] space-y-1">
            <p>&copy; {{ date('Y') }} Enterprise Application Platform. All rights reserved.</p>
            <p>ISO 27001 Certified &bull; SOC 2 Type II Compliant &bull; End-to-End Encrypted</p>
        </div>
    </div>
</body>
</html>
