@props([
    'currentWorkspace' => 'employee',
])

@php
    use App\Domains\Shared\Enums\WorkspaceType;
    $ws = $currentWorkspace instanceof WorkspaceType ? $currentWorkspace : WorkspaceType::tryFrom($currentWorkspace ?? 'employee');

    // Build role-tailored mobile bottom navigation items
    $safeRoute = fn(string $name, string $fallback = '#') => \Illuminate\Support\Facades\Route::has($name) ? route($name) : $fallback;

    // Build role-tailored mobile bottom navigation items
    if ($ws === WorkspaceType::MANAGER) {
        $items = [
            ['label' => 'Workbench', 'icon' => 'fa-solid fa-house', 'url' => $safeRoute('manager.workbench'), 'active' => request()->routeIs('manager.workbench')],
            ['label' => 'Team', 'icon' => 'fa-solid fa-users', 'url' => $safeRoute('manager.members'), 'active' => request()->routeIs('manager.members')],
            ['label' => 'Approvals', 'icon' => 'fa-solid fa-clipboard-check', 'url' => $safeRoute('portal.requests'), 'active' => request()->routeIs('portal.requests')],
            ['label' => 'Analytics', 'icon' => 'fa-solid fa-chart-pie', 'url' => $safeRoute('manager.analytics'), 'active' => request()->routeIs('manager.analytics')],
            ['label' => 'Search', 'icon' => 'fa-solid fa-magnifying-glass', 'url' => '#search', 'click' => '$dispatch(\'open-command-palette\')', 'active' => false],
        ];
    } else {
        // Default Employee
        $items = [
            ['label' => 'Home', 'icon' => 'fa-solid fa-house', 'url' => $safeRoute('portal.dashboard', $safeRoute('employee.home')), 'active' => request()->routeIs('portal.dashboard') || request()->routeIs('employee.home')],
            ['label' => 'My Work', 'icon' => 'fa-solid fa-calendar-days', 'url' => $safeRoute('portal.schedule'), 'active' => request()->routeIs('portal.schedule')],
            ['label' => 'Requests', 'icon' => 'fa-solid fa-clipboard-list', 'url' => $safeRoute('portal.requests'), 'active' => request()->routeIs('portal.requests')],
            ['label' => 'Profile', 'icon' => 'fa-solid fa-user', 'url' => $safeRoute('portal.profile'), 'active' => request()->routeIs('portal.profile')],
            ['label' => 'Search', 'icon' => 'fa-solid fa-magnifying-glass', 'url' => '#search', 'click' => '$dispatch(\'open-command-palette\')', 'active' => false],
        ];
    }
@endphp

<nav aria-label="Mobile Navigation" class="md:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200 z-40 px-2 py-1 shadow-lg">
    <div class="flex items-center justify-around">
        @foreach($items as $item)
            @if(!empty($item['click']))
                <button type="button" 
                        @click="{{ $item['click'] }}"
                        class="flex flex-col items-center justify-center py-1.5 px-3 text-slate-500 hover:text-[#1E3A5F] transition focus:outline-none">
                    <i class="{{ $item['icon'] }} text-base mb-1" aria-hidden="true"></i>
                    <span class="text-[10px] font-medium">{{ $item['label'] }}</span>
                </button>
            @else
                <a href="{{ $item['url'] }}" 
                   class="flex flex-col items-center justify-center py-1.5 px-3 {{ $item['active'] ? 'text-[#1E3A5F] font-bold' : 'text-slate-500 hover:text-slate-800' }} transition">
                    <i class="{{ $item['icon'] }} text-base mb-1" aria-hidden="true"></i>
                    <span class="text-[10px]">{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</nav>
