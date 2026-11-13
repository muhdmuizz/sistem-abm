<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Utama') · Sistem ABM</title>
    <link rel="stylesheet" href="{{ asset('abm.css') }}">
    <script src="{{ asset('abm.js') }}" defer></script>
</head>
<body class="app-body" data-deadline="{{ min(session('abm_last_activity') + 1800, session('abm_authenticated_at') + 28800) }}">
<a class="skip-link" href="#content">Langkau ke kandungan</a>
<aside class="sidebar">
    <a class="sidebar-brand" aria-label="Sistem ABM — Utama" href="{{ route('home') }}"><span class="brand-mark">ABM</span><span class="nav-label">Sistem ABM<small>JPNT</small></span></a>
    @include('abm.navigation')
    <button id="sidebar-toggle" class="button sidebar-toggle" type="button" aria-expanded="true" aria-label="Runtuhkan atau kembangkan navigasi"><span class="nav-label">Runtuhkan</span><span aria-hidden="true">↔</span></button>
</aside>
<div class="workspace">
    <header class="app-header">
        <button class="button mobile-menu" id="mobile-open" type="button">Menu</button>
        <span class="system-name">Sistem ABM</span><span class="cycle">Kitaran belum ditetapkan</span>
        <div class="account"><span>{{ auth()->user()->name }}</span><form method="post" action="{{ route('logout') }}">@csrf<button class="button subtle" type="submit">Log keluar</button></form></div>
    </header>
    <main id="content" tabindex="-1">
        @yield('content')
    </main>
    <footer class="workspace-footer">Jabatan Pendidikan Negeri Terengganu</footer>
</div>
<dialog id="mobile-navigation" aria-label="Navigasi"><div class="dialog-heading"><strong>Sistem ABM</strong><button class="button" id="mobile-close" type="button">Tutup</button></div>@include('abm.navigation')</dialog>
<dialog id="session-warning" aria-labelledby="session-title"><h2 id="session-title">Sesi Hampir Tamat</h2><p>Simpan kerja anda. Sesi akan tamat selepas tempoh tidak aktif atau had lapan jam.</p><form method="post" action="{{ route('session.continue') }}">@csrf<button class="button primary" type="submit">Teruskan Sesi</button></form><p class="small">Had lapan jam tidak dilanjutkan.</p></dialog>
</body>
</html>
