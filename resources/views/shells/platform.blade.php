<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Platform Control Center') — SmartHCM Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="/css/corporate-tokens.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#0F172A] text-slate-100 min-h-screen flex flex-col font-sans antialiased">
    <!-- Accessible Skip Link -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    @include('components.shells.header', [
        'currentWorkspace' => \App\Domains\Shared\Enums\WorkspaceType::PLATFORM_ADMIN,
        'allowedWorkspaces' => $allowedWorkspaces ?? [],
        'navigation' => $navigation ?? []
    ])

    <main id="main-content" tabindex="-1" class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 focus:outline-none">
        @yield('content')
    </main>

    <!-- Global Interactive Components -->
    <x-ui.command-palette currentWorkspace="platform" />
    <x-ui.notification-drawer />

    <footer class="border-t border-slate-800 bg-[#0B1120] py-4 text-center text-xs text-slate-500">
        SmartHCM SaaS Platform Engine v2.69-ENTERPRISE &bull; Global Multi-Tenant Control Plane
    </footer>
</body>
</html>
