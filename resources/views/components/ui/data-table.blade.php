@props([
    'columns' => [], // ['key' => '...', 'label' => '...', 'sortable' => true, 'align' => 'left', 'primary' => false]
    'rows' => [],
    'searchable' => true,
    'selectable' => false,
    'bulkActions' => [], // [['label' => '...', 'action' => '...', 'icon' => '...', 'danger' => false]]
    'emptyTitle' => 'No records found',
    'emptyDescription' => 'Try adjusting your search query or filters to find what you are looking for.',
    'emptyActionLabel' => null,
    'emptyActionUrl' => null,
    'pagination' => null, // Paginated collection or null
])

<div x-data="{
    search: '',
    selectedRows: [],
    selectAll: false,
    sortCol: '',
    sortAsc: true,
    toggleAll() {
        if (this.selectAll) {
            this.selectedRows = [];
            this.selectAll = false;
        } else {
            const checkboxes = $el.querySelectorAll('tbody input[type=checkbox]');
            this.selectedRows = Array.from(checkboxes).map(cb => cb.value);
            this.selectAll = true;
        }
    },
    toggleRow(id) {
        if (this.selectedRows.includes(id)) {
            this.selectedRows = this.selectedRows.filter(r => r !== id);
            this.selectAll = false;
        } else {
            this.selectedRows.push(id);
        }
    },
    sortBy(col) {
        if (this.sortCol === col) {
            this.sortAsc = !this.sortAsc;
        } else {
            this.sortCol = col;
            this.sortAsc = true;
        }
    }
}" class="w-full space-y-4">

    <!-- Table Controls Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        @if($searchable)
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs" aria-hidden="true"></i>
                </span>
                <input type="text" 
                       x-model="search" 
                       placeholder="Filter records..." 
                       aria-label="Filter records"
                       class="w-full pl-9 pr-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#1E3A5F] focus:ring-1 focus:ring-[#1E3A5F] transition">
            </div>
        @endif

        <div class="flex items-center gap-2">
            {{ $filters ?? '' }}

            @if(isset($actions))
                {{ $actions }}
            @endif
        </div>
    </div>

    <!-- Bulk Actions Selection Bar -->
    @if($selectable)
        <div x-show="selectedRows.length > 0" 
             x-transition 
             class="bg-[#1E3A5F] text-white px-4 py-2 rounded-xl flex items-center justify-between text-xs shadow-md"
             style="display: none;">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-check-double text-[#C9A227]"></i>
                <span class="font-semibold"><span x-text="selectedRows.length"></span> record(s) selected</span>
            </div>

            <div class="flex items-center gap-2">
                @foreach($bulkActions as $action)
                    <button type="button" 
                            @click="$dispatch('bulk-action', { action: '{{ $action['action'] }}', ids: selectedRows })"
                            class="px-2.5 py-1 rounded {{ !empty($action['danger']) ? 'bg-rose-600 hover:bg-rose-700' : 'bg-white/10 hover:bg-white/20' }} font-medium transition flex items-center gap-1.5">
                        @if(!empty($action['icon'])) <i class="{{ $action['icon'] }} text-[10px]"></i> @endif
                        <span>{{ $action['label'] }}</span>
                    </button>
                @endforeach
                <button type="button" @click="selectedRows = []; selectAll = false" class="text-white/60 hover:text-white ml-2 text-[11px] underline">
                    Deselect All
                </button>
            </div>
        </div>
    @endif

    <!-- Data Table Surface -->
    @php
        $hasData = (is_countable($rows) && count($rows) > 0) || (is_object($rows) && method_exists($rows, 'count') && $rows->count() > 0);
    @endphp

    @if(!$hasData)
        <x-ui.empty-state 
            :title="$emptyTitle" 
            :description="$emptyDescription"
            :actionLabel="$emptyActionLabel"
            :actionUrl="$emptyActionUrl"
        />
    @else
        <!-- Desktop Table View (>= 768px) -->
        <div class="hidden md:block card-corporate overflow-x-auto shadow-xs">
            <table class="table-enterprise" role="table">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75">
                        @if($selectable)
                            <th class="w-10 px-4 py-3" scope="col">
                                <input type="checkbox" 
                                       @click="toggleAll()" 
                                       :checked="selectAll"
                                       aria-label="Select all rows"
                                       class="rounded border-slate-300 text-[#1E3A5F] focus:ring-[#C9A227]">
                            </th>
                        @endif

                        @foreach($columns as $col)
                            <th scope="col" class="px-4 py-3 text-xs font-semibold text-slate-600 {{ ($col['align'] ?? 'left') === 'right' ? 'text-right' : (($col['align'] ?? 'left') === 'center' ? 'text-center' : 'text-left') }}">
                                @if(!empty($col['sortable']))
                                    <button type="button" 
                                            @click="sortBy('{{ $col['key'] }}')"
                                            class="inline-flex items-center gap-1.5 hover:text-slate-900 font-semibold focus:outline-none">
                                        <span>{{ $col['label'] }}</span>
                                        <i class="fa-solid fa-sort text-[10px] text-slate-400" aria-hidden="true"></i>
                                    </button>
                                @else
                                    <span>{{ $col['label'] }}</span>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    {{ $slot }}
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (< 768px) -->
        <div class="md:hidden space-y-3">
            {{ $mobileSlot ?? $slot }}
        </div>

        <!-- Pagination Controls -->
        @if($pagination && method_exists($pagination, 'hasPages') && $pagination->hasPages())
            <div class="flex items-center justify-between pt-2 px-1 text-xs text-slate-500">
                <div>
                    Showing 
                    <span class="font-medium text-slate-800">{{ $pagination->firstItem() }}</span>
                    to 
                    <span class="font-medium text-slate-800">{{ $pagination->lastItem() }}</span>
                    of 
                    <span class="font-medium text-slate-800">{{ $pagination->total() }}</span> 
                    results
                </div>
                <div>
                    {{ $pagination->links() }}
                </div>
            </div>
        @endif
    @endif
</div>
