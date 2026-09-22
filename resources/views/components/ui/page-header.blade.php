@props([
    'title',
    'description' => null,
    'breadcrumbs' => [],
    'primaryAction' => null, // ['label' => '...', 'url' => '...', 'icon' => '...', 'action' => '...']
    'secondaryAction' => null,
])

<div class="mb-6">
    @if(!empty($breadcrumbs))
        <nav aria-label="Breadcrumb" class="mb-2">
            <ol class="flex items-center space-x-2 text-xs text-slate-500">
                @foreach($breadcrumbs as $crumb)
                    @if(!$loop->last)
                        <li>
                            <a href="{{ $crumb['url'] ?? '#' }}" class="hover:text-slate-800 transition">
                                {{ $crumb['label'] }}
                            </a>
                        </li>
                        <li class="text-slate-300" aria-hidden="true">/</li>
                    @else
                        <li class="font-medium text-slate-800" aria-current="page">
                            {{ $crumb['label'] }}
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                {{ $title }}
            </h1>
            @if($description)
                <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
            @endif
        </div>

        @if($primaryAction || $secondaryAction || isset($actions))
            <div class="flex items-center gap-3">
                @if($secondaryAction)
                    @if(!empty($secondaryAction['url']))
                        <a href="{{ $secondaryAction['url'] }}" class="btn-secondary text-xs flex items-center gap-1.5">
                            @if(!empty($secondaryAction['icon'])) <i class="{{ $secondaryAction['icon'] }}"></i> @endif
                            <span>{{ $secondaryAction['label'] }}</span>
                        </a>
                    @else
                        <button type="button" @if(!empty($secondaryAction['click'])) onclick="{{ $secondaryAction['click'] }}" @endif class="btn-secondary text-xs flex items-center gap-1.5">
                            @if(!empty($secondaryAction['icon'])) <i class="{{ $secondaryAction['icon'] }}"></i> @endif
                            <span>{{ $secondaryAction['label'] }}</span>
                        </button>
                    @endif
                @endif

                @if($primaryAction)
                    @if(!empty($primaryAction['url']))
                        <a href="{{ $primaryAction['url'] }}" class="btn-primary text-xs flex items-center gap-1.5 shadow-sm">
                            @if(!empty($primaryAction['icon'])) <i class="{{ $primaryAction['icon'] }}"></i> @endif
                            <span>{{ $primaryAction['label'] }}</span>
                        </a>
                    @else
                        <button type="button" @if(!empty($primaryAction['click'])) onclick="{{ $primaryAction['click'] }}" @endif class="btn-primary text-xs flex items-center gap-1.5 shadow-sm">
                            @if(!empty($primaryAction['icon'])) <i class="{{ $primaryAction['icon'] }}"></i> @endif
                            <span>{{ $primaryAction['label'] }}</span>
                        </button>
                    @endif
                @endif

                {{ $actions ?? '' }}
            </div>
        @endif
    </div>
</div>
