@props([
    'status' => 'active',
    'size' => 'sm',
])

@php
    $normalized = strtolower(trim((string) $status));
    
    // Map status to semantic style & icon
    [$badgeClass, $icon, $label] = match($normalized) {
        'active', 'approved', 'completed', 'success', 'paid', 'verified' => [
            'bg-emerald-50 text-emerald-700 border-emerald-200',
            'fa-solid fa-circle-check',
            ucfirst($status)
        ],
        'pending', 'processing', 'submitted', 'review', 'in_progress', 'scheduled' => [
            'bg-amber-50 text-amber-700 border-amber-200',
            'fa-solid fa-clock',
            ucwords(str_replace('_', ' ', $status))
        ],
        'rejected', 'failed', 'cancelled', 'canceled', 'danger', 'inactive', 'terminated' => [
            'bg-rose-50 text-rose-700 border-rose-200',
            'fa-solid fa-circle-xmark',
            ucwords(str_replace('_', ' ', $status))
        ],
        'draft', 'paused', 'expired' => [
            'bg-slate-100 text-slate-700 border-slate-200',
            'fa-solid fa-circle-dot',
            ucfirst($status)
        ],
        'info', 'open', 'new' => [
            'bg-blue-50 text-blue-700 border-blue-200',
            'fa-solid fa-circle-info',
            ucfirst($status)
        ],
        default => [
            'bg-slate-50 text-slate-600 border-slate-200',
            'fa-solid fa-circle',
            ucwords(str_replace('_', ' ', $status))
        ]
    };

    $paddingClass = match($size) {
        'xs' => 'px-1.5 py-0.5 text-[10px]',
        'lg' => 'px-3 py-1 text-sm font-semibold',
        default => 'px-2.5 py-0.5 text-xs font-medium',
    };
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full border {{ $badgeClass }} {{ $paddingClass }}">
    <i class="{{ $icon }} text-[10px] opacity-80" aria-hidden="true"></i>
    <span>{{ $label }}</span>
</span>
