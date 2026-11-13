<nav aria-label="Navigasi utama">
    <a aria-label="Utama" href="{{ route('home') }}" @class(['nav-link', 'selected' => request()->routeIs('home')]) @if(request()->routeIs('home')) aria-current="page" @endif><span class="nav-short" aria-hidden="true">U</span><span class="nav-label">Utama</span></a>
    @if (auth()->user()->penugasanPeranan()->where('peranan', 'superadmin')->exists())
    <p class="nav-group">Pentadbiran</p>
    <a aria-label="Pengguna & Peranan" href="{{ route('users.index') }}" @class(['nav-link', 'selected' => request()->routeIs('users.*')]) @if(request()->routeIs('users.*')) aria-current="page" @endif><span class="nav-short" aria-hidden="true">PP</span><span class="nav-label">Pengguna & Peranan</span></a>
    @foreach (['ptj' => 'PTJ', 'aktiviti' => 'Aktiviti Kewangan', 'tempat-bertugas' => 'Tempat Bertugas', 'os' => 'OS'] as $key => $label)
        <a aria-label="{{ $label }}" href="{{ route('references', $key) }}" @class(['nav-link', 'selected' => request()->route('section') === $key]) @if(request()->route('section') === $key) aria-current="page" @endif title="{{ $label }}"><span class="nav-short" aria-hidden="true">{{ ['ptj' => 'PTJ', 'aktiviti' => 'AK', 'tempat-bertugas' => 'TB', 'os' => 'OS'][$key] }}</span><span class="nav-label">{{ $label }}</span></a>
    @endforeach
    @endif
</nav>
