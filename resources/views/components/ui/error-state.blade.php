@props([
    'title' => 'Something went wrong',
    'message' => 'We were unable to complete this request or retrieve the requested records.',
    'referenceId' => null,
    'retryUrl' => null,
    'retryClick' => null,
])

@php
    $ref = $referenceId ?? ('ERR-' . strtoupper(substr(md5(microtime()), 0, 8)));
@endphp

<div class="rounded-xl border border-rose-200 bg-rose-50/50 p-8 text-center my-6 flex flex-col items-center justify-center">
    <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
        <i class="fa-solid fa-triangle-exclamation text-xl" aria-hidden="true"></i>
    </div>

    <h3 class="text-base font-bold text-slate-900 mb-1">{{ $title }}</h3>
    <p class="text-xs text-slate-600 max-w-md mb-4">{{ $message }}</p>

    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-white border border-rose-200 text-[11px] font-mono text-slate-500 mb-6 shadow-2xs">
        <span class="text-slate-400">Reference:</span>
        <span class="font-bold text-slate-700">{{ $ref }}</span>
    </div>

    <div class="flex items-center gap-3">
        @if($retryUrl)
            <a href="{{ $retryUrl }}" class="btn-primary text-xs inline-flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-arrow-rotate-right"></i>
                <span>Try Again</span>
            </a>
        @elseif($retryClick)
            <button type="button" onclick="{{ $retryClick }}" class="btn-primary text-xs inline-flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-arrow-rotate-right"></i>
                <span>Try Again</span>
            </a>
        @else
            <button type="button" onclick="window.location.reload()" class="btn-primary text-xs inline-flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-arrow-rotate-right"></i>
                <span>Try Again</span>
            </button>
        @endif

        <a href="mailto:support@smarthcm.internal?subject=Support%20Request%20{{ $ref }}" class="btn-secondary text-xs inline-flex items-center gap-1.5">
            <i class="fa-regular fa-envelope"></i>
            <span>Contact Support</span>
        </a>
    </div>
</div>
