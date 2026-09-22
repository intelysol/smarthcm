@props([
    'id',
    'title' => 'Confirm Destructive Action',
    'consequence' => 'This action cannot be undone and will permanently impact this record.',
    'confirmText' => 'Delete Permanently',
    'actionUrl' => null,
    'method' => 'POST', // POST, DELETE
])

<x-ui.modal :id="$id" :title="$title" maxWidth="md">
    <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-triangle-exclamation text-base" aria-hidden="true"></i>
        </div>
        <div>
            <p class="font-medium text-slate-800 text-sm mb-1">{{ $title }}</p>
            <p class="text-slate-500 text-xs">{{ $consequence }}</p>
        </div>
    </div>

    <x-slot name="footer">
        <button type="button" 
                @click="$dispatch('close-modal', { id: '{{ $id }}' })" 
                class="btn-secondary text-xs">
            Cancel
        </button>

        @if($actionUrl)
            <form action="{{ $actionUrl }}" method="POST" class="inline m-0">
                @csrf
                @if(strtoupper($method) === 'DELETE')
                    @method('DELETE')
                @endif
                <button type="submit" class="btn-danger text-xs">
                    {{ $confirmText }}
                </button>
            </form>
        @else
            <button type="button" 
                    @click="$dispatch('confirmed', { id: '{{ $id }}' }); $dispatch('close-modal', { id: '{{ $id }}' })" 
                    class="btn-danger text-xs">
                {{ $confirmText }}
            </button>
        @endif
    </x-slot>
</x-ui.modal>
