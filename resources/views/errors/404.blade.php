<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found (404) — SmartHCM Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/corporate-tokens.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#F7F9FC] text-[#1F2937] min-h-screen flex items-center justify-center p-4 font-sans antialiased">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200 p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center mx-auto mb-5 text-[#1E3A5F]">
            <i class="fa-solid fa-compass text-2xl" aria-hidden="true"></i>
        </div>
        
        <span class="text-xs font-bold text-[#C9A227] uppercase tracking-wider font-mono">Error 404</span>
        <h1 class="text-2xl font-black text-[#1E3A5F] mt-1 mb-2 tracking-tight">Page Not Found</h1>
        <p class="text-xs text-slate-600 mb-6 leading-relaxed">
            The page you're looking for doesn't exist, has been archived, or may have moved to a different workspace location.
        </p>

        @php
            use App\Domains\Shared\Enums\WorkspaceType;
            $user = auth()->user();
            $targetUrl = '/portal';
            $workspaceLabel = 'My Workspace';

            if ($user) {
                if ($user->is_platform_admin ?? false) {
                    $targetUrl = route('platform.control-center');
                    $workspaceLabel = 'Platform Control Center';
                } elseif ($user->is_tenant_admin ?? false) {
                    $targetUrl = route('admin.settings');
                    $workspaceLabel = 'Tenant Administration';
                } elseif ($user->is_hr_admin ?? false) {
                    $targetUrl = route('hr.dashboard');
                    $workspaceLabel = 'HR Operations';
                } elseif (method_exists($user, 'isManager') && $user->isManager()) {
                    $targetUrl = route('manager.workbench');
                    $workspaceLabel = 'Manager Workbench';
                } else {
                    $targetUrl = route('employee.home');
                    $workspaceLabel = 'Employee Workplace';
                }
            }
        @endphp

        <div class="space-y-3">
            <a href="{{ $targetUrl }}" class="btn-primary w-full py-2.5 px-4 rounded-xl inline-flex items-center justify-center text-xs font-semibold shadow-md shadow-slate-900/10 transition">
                <i class="fa-solid fa-house mr-2" aria-hidden="true"></i> Go to {{ $workspaceLabel }}
            </a>

            <button type="button" onclick="window.history.back()" class="btn-secondary w-full py-2 text-xs font-medium">
                <i class="fa-solid fa-arrow-left mr-1.5" aria-hidden="true"></i> Go Back
            </button>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 text-[11px] text-slate-400">
            SmartHCM Enterprise Experience &bull; Reference: 404-NOT-FOUND
        </div>
    </div>
</body>
</html>
