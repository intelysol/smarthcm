@props([
    'icon' => 'fa-solid fa-inbox',
    'title' => 'No items found',
    'description' => 'There are no records to display at this time.',
    'actionLabel' => null,
    'actionUrl' => null,
    'actionClick' => null,
    'actionIcon' => null,
])

<div class="rounded-xl border border-dashed border-slate-300 bg-white p-12 text-center my-6 flex flex-col items-center justify-center">
    <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 mb-4 shadow-inner">
        <i class="{{ $icon }} text-2xl" aria-hidden="true"></i>
    </div>
    
    <h3 class="text-base font-semibold text-slate-900 mb-1">{{ $title }}</h3>
    <p class="text-xs text-slate-500 max-w-sm mb-6">{{ $description }}</p>

    @if($actionLabel)
        @if($actionUrl)
            <a href="{{ $actionUrl }}" class="btn-primary text-xs inline-flex items-center gap-1.5 shadow-sm">
                @if($actionIcon) <i class="{{ $actionIcon }}"></i> @endif
                <span>{{ $actionLabel }}</span>
            </a>
        @elseif($actionClick)
            <button type="button" onclick="{{ $actionClick }}" class="btn-primary text-xs inline-flex items-center gap-1.5 shadow-sm">
                @if($actionIcon) <i class="{{ $actionIcon }}"></i> @endif
                <span>{{ $actionLabel }}</span>
            </button>
        @endif
    @endif

    {{ $slot }}
</div>
