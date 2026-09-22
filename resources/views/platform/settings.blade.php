@extends('shells.platform')

@section('title', 'Platform Settings')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white">Platform Settings &amp; Configuration</h1>
            <p class="text-xs text-slate-400">Global defaults, mail gateways, feature toggles, and platform branding</p>
        </div>
        <a href="{{ route('platform.control-center') }}" class="text-xs font-semibold text-[#C9A227] hover:underline">&larr; Back to Control Center</a>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
        <h2 class="text-sm font-bold text-white">System Global Configuration</h2>
        <div class="space-y-3 text-xs">
            <div class="flex justify-between items-center py-2 border-b border-slate-800">
                <span class="text-slate-300">Environment:</span>
                <span class="font-mono text-white">{{ config('app.env') }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-800">
                <span class="text-slate-300">Default Timezone:</span>
                <span class="font-mono text-white">{{ config('app.timezone', 'UTC') }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-800">
                <span class="text-slate-300">Cache Driver:</span>
                <span class="font-mono text-white">{{ config('cache.default', 'file') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
