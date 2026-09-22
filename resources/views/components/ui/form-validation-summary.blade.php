@props([
    'errors' => null,
])

@php
    $errorBag = $errors ?? ($errors ?? null);
    $hasErrors = $errorBag && method_exists($errorBag, 'any') && $errorBag->any();
@endphp

@if($hasErrors)
    <div role="alert" 
         aria-labelledby="validation-summary-title"
         tabindex="-1"
         x-data
         x-init="$el.focus()"
         class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-900 shadow-2xs">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-circle-exclamation text-base text-rose-600 shrink-0 mt-0.5" aria-hidden="true"></i>
            <div class="flex-1">
                <h3 id="validation-summary-title" class="font-bold text-rose-900 text-sm">
                    Please correct the following before submitting:
                </h3>
                <ul class="mt-2 list-disc list-inside space-y-1 text-rose-800">
                    @foreach($errorBag->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
