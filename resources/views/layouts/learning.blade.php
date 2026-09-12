<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Learning & Development') - Flow HCM Enterprise</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --success: #16a34a;
            --warning: #eab308;
            --danger: #dc2626;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Instrument Sans', sans-serif; background: var(--bg-main); color: var(--text-main); line-height: 1.5; }
        .navbar { background: #1e293b; color: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #cbd5e1; text-decoration: none; margin-right: 1.5rem; font-weight: 500; }
        .navbar a:hover, .navbar a.active { color: #fff; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; }
        .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .btn { display: inline-block; padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 500; cursor: pointer; text-decoration: none; border: 1px solid transparent; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-outline { border-color: var(--border); color: var(--text-main); background: #fff; }
        .badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef9c3; color: #a16207; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .progress-bar { background: #e2e8f0; border-radius: 9999px; height: 0.5rem; overflow: hidden; margin: 0.5rem 0; }
        .progress-fill { background: var(--primary); height: 100%; border-radius: 9999px; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--border); }
        th { background: #f8fafc; font-weight: 600; color: var(--text-muted); font-size: 0.875rem; }
    </style>
</head>
<body>
    <header class="navbar">
        <div>
            <strong style="font-size: 1.2rem; color: #60a5fa;">Flow HCM</strong>
            <span style="color: #94a3b8; margin-left: 0.5rem;">| LMS & Development</span>
        </div>
        <nav>
            <a href="{{ route('hcm.me.learning.dashboard') }}">My Learning</a>
            <a href="{{ route('hcm.me.learning.catalog') }}">Course Catalog</a>
            <a href="{{ route('hcm.me.learning.transcript') }}">Transcript</a>
            <a href="{{ route('hcm.manager.learning.dashboard') }}">Manager Hub</a>
            <a href="{{ route('hcm.learning.dashboard') }}">Admin LMS</a>
        </nav>
    </header>

    <main class="container">
        @yield('content')
    </main>
</body>
</html>
