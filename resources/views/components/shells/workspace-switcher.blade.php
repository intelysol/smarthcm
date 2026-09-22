@props([
    'currentWorkspace',
    'allowedWorkspaces' => []
])

@php
    use App\Domains\Shared\Enums\WorkspaceType;
    $current = $currentWorkspace instanceof WorkspaceType ? $currentWorkspace : WorkspaceType::tryFrom($currentWorkspace ?? 'employee');
@endphp

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" type="button" 
        class="flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-black/20 hover:bg-black/30 border border-white/10 text-white text-xs font-semibold transition focus:outline-none">
        <i class="{{ $current?->icon() ?? 'fa-solid fa-layer-group' }} text-[#C9A227]"></i>
        <span class="hidden sm:inline font-bold">{{ $current?->label() ?? 'Workspace' }}</span>
        <i class="fa-solid fa-chevron-down text-[10px] text-white/60 ml-1"></i>
    </button>

    <div x-show="open" @click.away="open = false" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute left-0 mt-2 w-72 bg-white rounded-xl shadow-2xl border border-slate-200 z-50 text-slate-800 py-2 text-xs overflow-hidden"
        style="display: none;">
        
        <div class="px-4 py-2 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <span class="font-bold text-[11px] text-slate-500 uppercase tracking-wider">Switch Workspace</span>
            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 font-mono">{{ count($allowedWorkspaces) }} available</span>
        </div>

        <div class="max-h-72 overflow-y-auto divide-y divide-slate-100">
            @foreach($allowedWorkspaces as $workspace)
                @php
                    $isCurrent = $workspace === $current;
                @endphp
                <form action="{{ route('workspace.switch') }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="workspace" value="{{ $workspace->value }}">
                    <button type="submit" class="w-full text-left px-4 py-3 hover:bg-slate-50 flex items-start space-x-3 transition {{ $isCurrent ? 'bg-slate-50/80' : '' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5 {{ $isCurrent ? 'bg-[#1E3A5F] text-[#C9A227]' : 'bg-slate-100 text-slate-600' }}">
                            <i class="{{ $workspace->icon() }} text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-slate-900 truncate {{ $isCurrent ? 'text-[#1E3A5F]' : '' }}">{{ $workspace->label() }}</p>
                                @if($isCurrent)
                                    <span class="text-[#16805C] text-[10px] font-bold"><i class="fa-solid fa-circle-check"></i> Active</span>
                                @endif
                            </div>
                            <p class="text-[10px] text-slate-500 leading-tight line-clamp-2 mt-0.5">{{ $workspace->description() }}</p>
                        </div>
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</div>
