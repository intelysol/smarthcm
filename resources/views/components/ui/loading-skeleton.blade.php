@props([
    'type' => 'table', // table, cards, profile, list
    'rows' => 5,
    'cols' => 4,
])

<div role="status" aria-label="Loading content..." class="w-full animate-pulse">
    @if($type === 'table')
        <div class="card-corporate overflow-hidden p-4">
            <div class="h-8 bg-slate-200 rounded mb-4 w-1/4"></div>
            <div class="space-y-3">
                @for($i = 0; $i < $rows; $i++)
                    <div class="flex gap-4">
                        @for($j = 0; $j < $cols; $j++)
                            <div class="h-4 bg-slate-100 rounded flex-1"></div>
                        @endfor
                    </div>
                @endfor
            </div>
        </div>
    @elseif($type === 'cards')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @for($i = 0; $i < $rows; $i++)
                <div class="card-corporate p-5 space-y-3">
                    <div class="h-5 bg-slate-200 rounded w-1/2"></div>
                    <div class="h-3 bg-slate-100 rounded w-3/4"></div>
                    <div class="h-3 bg-slate-100 rounded w-2/3"></div>
                    <div class="pt-2 flex justify-between">
                        <div class="h-4 bg-slate-200 rounded w-1/4"></div>
                        <div class="h-4 bg-slate-200 rounded w-1/4"></div>
                    </div>
                </div>
            @endfor
        </div>
    @elseif($type === 'profile')
        <div class="card-corporate p-6 space-y-6">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-slate-200 rounded-full"></div>
                <div class="space-y-2 flex-1">
                    <div class="h-5 bg-slate-200 rounded w-1/3"></div>
                    <div class="h-3 bg-slate-100 rounded w-1/4"></div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                <div class="h-4 bg-slate-100 rounded"></div>
                <div class="h-4 bg-slate-100 rounded"></div>
                <div class="h-4 bg-slate-100 rounded"></div>
                <div class="h-4 bg-slate-100 rounded"></div>
            </div>
        </div>
    @else
        <div class="space-y-3">
            @for($i = 0; $i < $rows; $i++)
                <div class="h-12 bg-slate-100 rounded-lg"></div>
            @endfor
        </div>
    @endif
    <span class="sr-only">Loading content, please wait...</span>
</div>
