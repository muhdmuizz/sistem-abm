<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log Masuk · Sistem ABM</title>
    <link rel="stylesheet" href="{{ asset('abm.css') }}">
</head>
<body class="login-body">
<main class="login-panel">
    <div class="login-identity"><div class="brand-mark" aria-hidden="true">ABM</div><p class="login-organisation">Jabatan Pendidikan Negeri Terengganu</p></div>
    <h1>Anggaran Belanja Mengurus</h1>
    <section class="login-form" aria-labelledby="login-heading">
        <h2 id="login-heading">Log masuk</h2>
        <p>Gunakan akaun Google MyGovUC yang berdaftar dengan Sistem ABM.</p>
        @if(session('status'))<p class="notice success" role="status">{{ session('status') }}</p>@endif
        @if ($errors->any())<div class="notice error" role="alert">{{ $errors->first() }}</div>@endif
        @if ($configured)
            <a class="button primary full" href="{{ route('google.redirect') }}">Log masuk dengan Google</a>
        @else
            <button class="button primary full" type="button" disabled>Log masuk dengan Google</button>
            <p class="notice">Log masuk Google belum disediakan. Sila hubungi pentadbir untuk mengaktifkan akses.</p>
        @endif
        <p class="small">Akses mengikut peranan dan skop yang ditetapkan.</p>
    </section>
</main>
</body>
</html>
