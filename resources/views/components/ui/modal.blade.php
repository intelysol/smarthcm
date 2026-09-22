@props([
    'id',
    'title',
    'maxWidth' => 'md', // sm, md, lg, xl, 2xl
])

@php
    $maxWidthClass = match($maxWidth) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-md',
    };
@endphp

<div x-data="{ open: false, triggerElement: null }"
     x-on:open-modal.window="if ($event.detail.id === '{{ $id }}') { triggerElement = document.activeElement; open = true; $nextTick(() => { $refs.dialog?.focus(); }); }"
     x-on:close-modal.window="if ($event.detail.id === '{{ $id }}') { open = false; triggerElement?.focus(); }"
     x-on:keydown.escape.window="if (open) { open = false; triggerElement?.focus(); }"
     class="relative z-50">

    <!-- Modal Backdrop -->
    <div x-show="open"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
         aria-hidden="true"
         style="display: none;"></div>

    <!-- Modal Dialog Frame -->
    <div x-show="open"
         tabindex="-1"
         x-ref="dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="{{ $id }}-title"
         class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-center justify-center"
         style="display: none;">

        <div @click.away="open = false; triggerElement?.focus()"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full {{ $maxWidthClass }} bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">

            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 id="{{ $id }}-title" class="text-base font-bold text-slate-900">
                    {{ $title }}
                </h3>
                <button type="button" 
                        @click="open = false; triggerElement?.focus()"
                        aria-label="Close dialog"
                        class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition focus:outline-none focus:ring-2 focus:ring-[#C9A227]">
                    <i class="fa-solid fa-xmark text-sm" aria-hidden="true"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4 text-xs text-slate-600">
                {{ $slot }}
            </div>

            <!-- Modal Footer -->
            @if(isset($footer))
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
