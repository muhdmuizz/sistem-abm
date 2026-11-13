@extends('abm.layout')
@section('title', 'Pengguna & Peranan')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Pentadbiran</p><h1>Pengguna & Peranan</h1><p class="intro">Urus identiti, status akaun dan penugasan peranan.</p></div>
<a class="button primary" href="{{ route('users.create') }}"><x-abm-icon name="plus"/>Tambah Pengguna</a></div>
@if(session('status'))<p class="notice" role="status">{{ session('status') }}</p>@endif
<form class="search-form" method="get"><label for="user-search">Cari nama atau e-mel</label><div class="inline-controls"><input id="user-search" type="search" name="q" value="{{ $search }}" maxlength="255"><button class="button" type="submit">Cari</button><a class="button subtle" href="{{ route('users.index') }}">Set Semula</a></div></form>
<p class="small">{{ $users->total() }} pengguna</p>
<div class="data-table"><table><thead><tr><th>Pengguna</th><th>Organisasi</th><th>Status</th><th>Peranan & Skop</th><th>Tindakan</th></tr></thead><tbody>
@forelse($users as $account)
<tr><td data-label="Pengguna"><strong>{{ $account->name }}</strong><br><span class="small">{{ $account->email }}</span></td>
<td data-label="Organisasi">{{ $account->ptj?->nama ?? 'Belum ditetapkan' }}<br><span class="small">{{ $account->tempatBertugas?->nama }}</span></td>
<td data-label="Status"><span class="status-pill {{ $account->is_active ? 'positive' : '' }}">{{ $account->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
<td data-label="Peranan & Skop"><details class="scope-details"><summary>{{ $account->penugasanPeranan->count() }} penugasan</summary><ul>
@forelse($account->penugasanPeranan as $assignment)
<li>{{ str($assignment->peranan->value)->headline() }}<span class="small"> · PTJ {{ $assignment->ptj?->nama ?? 'semua' }} · Aktiviti {{ $assignment->aktivitiKewangan?->nama ?? 'semua' }}@if($assignment->tempat_bertugas_id) · Tempat Bertugas {{ $assignment->tempatBertugas?->nama }}@endif</span></li>
@empty<li>Tiada penugasan. Akses dilindungi tidak dibenarkan.</li>@endforelse
</ul></details></td>
<td data-label="Tindakan"><a class="button icon-button" href="{{ route('users.edit', $account) }}" aria-label="Pinda {{ $account->name }}" title="Pinda pengguna"><x-abm-icon name="pencil"/></a></td></tr>
@empty<tr><td colspan="5">Tiada pengguna ditemui.</td></tr>@endforelse
</tbody></table></div>
<div class="pagination"><span>Halaman {{ $users->currentPage() }} daripada {{ $users->lastPage() }}</span><div>@if($users->previousPageUrl())<a href="{{ $users->previousPageUrl() }}">Sebelumnya</a>@endif @if($users->nextPageUrl())<a href="{{ $users->nextPageUrl() }}">Seterusnya</a>@endif</div></div>
@endsection
