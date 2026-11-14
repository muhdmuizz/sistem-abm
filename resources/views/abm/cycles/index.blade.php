@extends('abm.layout')
@section('title', 'Kitaran ABM')
@section('cycle-context', 'Pentadbiran Kitaran ABM')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Pentadbiran</p><h1>Kitaran ABM</h1><p class="intro">Urus tahun Kitaran, Fasa dan skop pembukaan.</p></div><a class="button primary" href="{{ route('cycles.create') }}"><x-abm-icon name="plus"/>Tambah Kitaran</a></div>
@if(session('status'))<p class="notice success" role="status">{{ session('status') }}</p>@endif
<form method="get" class="search-form"><label for="cycle-year-search">Tahun Kitaran</label><div class="inline-controls"><input id="cycle-year-search" name="tahun" type="number" min="1" max="9999" value="{{ $year }}"><button class="button" type="submit">Cari</button><a class="button subtle" href="{{ route('cycles.index') }}">Set Semula</a></div></form>
<p class="small">{{ $cycles->total() }} Kitaran</p>
<div class="cycle-table"><table><thead><tr><th>Tahun Kitaran</th><th class="count-heading">Bilangan Fasa</th></tr></thead><tbody>
@forelse($cycles as $cycle)<tr><td colspan="2"><details class="cycle-record" @if($year) open @endif><summary><x-abm-icon name="chevron"/><strong>{{ $cycle->tahun }}</strong><span>{{ $cycle->fasa_count }} Fasa</span></summary>
<div class="cycle-content"><div class="phase-list">
@forelse($cycle->fasa as $phase)
<article class="phase-card"><div class="phase-heading"><h2>{{ $phase->label() }}</h2><span class="status-pill {{ $phase->dibuka ? 'positive' : '' }}">{{ $phase->dibuka ? 'Dibuka' : 'Ditutup' }}</span></div>
<div class="phase-facts"><div><span class="small">Tarikh Mula</span><p>{{ $phase->tarikh_mula->format('d/m/Y') }}</p></div><div><span class="small">Tarikh Tamat</span><p>{{ $phase->tarikh_tamat->format('d/m/Y') }}</p></div><div><span class="small">Skop Pembukaan</span>@include('abm.cycles.scope-summary')</div></div>
@if($phase->dibuka && ! $phase->dalamTempoh())<p class="small phase-window-note">Di luar tempoh pembukaan. Akses penyedia masih tertakluk pada tarikh Fasa.</p>@endif
<div class="phase-actions"><a class="button icon-button" href="{{ route('phases.edit', [$cycle->id, $phase->id]) }}" aria-label="Pinda {{ $phase->label() }} Kitaran {{ $cycle->tahun }}" title="Pinda Fasa"><x-abm-icon name="pencil"/></a>
<a class="button" href="{{ route('phases.action', [$cycle->id, $phase->id, 'lanjutkan']) }}"><x-abm-icon name="calendar"/>Lanjutkan</a>
@php($openingAction = $phase->dibuka ? 'tutup' : ($phase->pernah_dibuka ? 'buka-semula' : 'buka'))
<a class="button {{ $phase->dibuka ? 'caution' : 'positive-action' }}" href="{{ route('phases.action', [$cycle->id, $phase->id, $openingAction]) }}"><x-abm-icon :name="$phase->dibuka ? 'lock' : 'unlock'"/>{{ $phase->dibuka ? 'Tutup' : ($phase->pernah_dibuka ? 'Buka Semula' : 'Buka') }}</a></div>
</article>
@empty<p class="notice">Belum ada Fasa bagi Kitaran ini.</p>@endforelse
</div><a class="button primary" href="{{ route('phases.create', $cycle->id) }}"><x-abm-icon name="plus"/>Tambah Fasa</a></div></details></td></tr>
@empty<tr><td colspan="2">Tiada Kitaran ditemui.</td></tr>@endforelse
</tbody></table></div>
<div class="pagination"><span>Halaman {{ $cycles->currentPage() }} daripada {{ $cycles->lastPage() }}</span><div>@if($cycles->previousPageUrl())<a href="{{ $cycles->previousPageUrl() }}">Sebelumnya</a>@endif @if($cycles->nextPageUrl())<a href="{{ $cycles->nextPageUrl() }}">Seterusnya</a>@endif</div></div>
@endsection
