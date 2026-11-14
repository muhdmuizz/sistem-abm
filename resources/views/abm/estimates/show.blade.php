@extends('abm.layout')
@section('title', 'OS'.$os->kod_objek.' — '.$os->butiran_perbelanjaan)
@section('cycle-context', 'Kitaran ABM '.$bid->kitaran->tahun)
@section('content')
<div class="page-heading"><div><p class="eyebrow">Anggaran</p><h1>OS{{ $os->kod_objek }} — {{ $os->butiran_perbelanjaan }}</h1></div><a class="button" href="{{ route('bids.show',$bid->id) }}">Kembali ke Senarai Anggaran</a></div>
@if($errors->any())<div class="notice error" role="alert"><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
<dl class="bid-context-facts">
<div><dt>Kitaran ABM</dt><dd>{{ $bid->kitaran->tahun }}</dd></div><div><dt>PTJ</dt><dd>{{ $bid->ptj->nama }}</dd></div>
<div><dt>Aktiviti Kewangan</dt><dd>{{ $bid->aktivitiKewangan->nama }}</dd></div><div><dt>Status Bidaan</dt><dd>{{ \App\Services\RuangBidaPeruntukan::STATUS[$bid->status->value] }}</dd></div>
</dl>
<nav class="estimate-navigation" aria-label="Navigasi OS">
<form method="get" action="{{ route('estimates.navigate',$bid->id) }}"><div><label for="estimate-os">Anggaran (OS)</label><select id="estimate-os" name="os_id" required>@foreach($navigation as $item)<option value="{{ $item['os']->id }}" @selected($item['os']->id===$os->id)>OS{{ $item['os']->kod_objek }} — {{ $item['os']->butiran_perbelanjaan }}{{ $item['active'] ? '' : ' (Belum Aktif)' }}</option>@endforeach</select></div><button class="button" type="submit">Buka OS</button></form>
<div class="estimate-sequence">@if($previous)<a class="button" href="{{ $previous['url'] }}" rel="prev">Sebelumnya<span class="sr-only"> OS{{ $previous['os']->kod_objek }} {{ $previous['os']->butiran_perbelanjaan }}</span></a>@endif @if($next)<a class="button" href="{{ $next['url'] }}" rel="next">Seterusnya<span class="sr-only"> OS{{ $next['os']->kod_objek }} {{ $next['os']->butiran_perbelanjaan }}</span></a>@endif</div>
</nav>
@if($editorAvailable)<p class="notice">Sediakan Butiran Anggaran melalui jadual draf.</p><a class="button primary" href="{{ route('estimates.editor',[$bid->id,$estimate->id]) }}">Buka Jadual Butiran</a>@else<p class="notice">Penyediaan butiran OS ini belum dibuka.</p>@endif
@if(! $open)<p class="small">Tiada Fasa dibuka dalam tempoh. Rekod tersimpan boleh dilihat mengikut skop akses anda.</p>@endif
@if(! $bid->diaktifkan)<p class="small">Bidaan belum aktif.</p>@endif
<div class="bid-section-heading"><h2>Senarai Butiran Anggaran</h2><p class="small">{{ $sources->total() }} rekod dalam skop anda</p></div>
@if($sources->isEmpty())
<section class="empty-state"><h2>Tiada Butiran Anggaran dalam skop anda.</h2><p>Butiran yang telah disimpan akan dipaparkan di sini.</p></section>
@else
<div class="data-table estimate-sources"><table><caption class="sr-only">Butiran Anggaran OS{{ $os->kod_objek }} dalam skop akses anda</caption><thead><tr><th scope="col">Rujukan Butiran</th><th scope="col">Tempat Bertugas</th><th scope="col">Disimpan Pada</th></tr></thead><tbody>
@foreach($sources as $source)<tr><td data-label="Rujukan Butiran">Butiran #{{ $source->id }}</td><td data-label="Tempat Bertugas">{{ $source->tempatBertugas->nama }}</td><td data-label="Disimpan Pada">{{ $source->updated_at?->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i') }}</td></tr>@endforeach
</tbody></table></div>
@endif
@if($sources->hasPages())<nav class="pagination" aria-label="Halaman Butiran"><span>Halaman {{ $sources->currentPage() }} daripada {{ $sources->lastPage() }}</span><div>@if($sources->previousPageUrl())<a href="{{ $sources->previousPageUrl() }}">Sebelumnya</a>@endif @if($sources->nextPageUrl())<a href="{{ $sources->nextPageUrl() }}">Seterusnya</a>@endif</div></nav>@endif

<details class="section estimate-history" id="estimate-history" @if(request()->has('audit_page')) open @endif>
<summary><x-abm-icon name="chevron"/><span>Log Tindakan</span><span class="small">{{ $history->total() }} rekod dalam skop anda</span></summary>
@if($history->isEmpty())
<section class="empty-state"><h2>Tiada Log Tindakan dalam skop anda.</h2><p>Tindakan yang direkodkan akan dipaparkan di sini.</p></section>
@else
<div class="data-table estimate-audit"><table><caption class="sr-only">Log Tindakan Anggaran OS{{ $os->kod_objek }} dalam skop akses anda</caption><thead><tr><th scope="col">Tarikh & Masa</th><th scope="col">Tindakan</th><th scope="col">Rujukan</th><th scope="col">Pelaku</th></tr></thead><tbody>
@foreach($history as $event)<tr>
<td data-label="Tarikh & Masa">{{ $event->berlaku_pada->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i') }}</td>
<td data-label="Tindakan">{{ \App\Services\RuangAnggaran::ACTIONS[$event->tindakan] }}</td>
<td data-label="Rujukan">{{ $event->jenis_entiti === \App\Models\Anggaran::class ? 'OS'.$os->kod_objek : 'Butiran #'.$event->id_entiti }}</td>
<td data-label="Pelaku"><div>{{ $event->pelaku->name }}<p class="small">{{ \App\Services\RuangAnggaran::ROLE_LABELS[$event->peranan->value] }}</p></div></td>
</tr>@endforeach
</tbody></table></div>
@endif
@if($history->hasPages())<nav class="pagination" aria-label="Halaman Log Tindakan"><span>Halaman {{ $history->currentPage() }} daripada {{ $history->lastPage() }}</span><div>@if($history->previousPageUrl())<a href="{{ $history->previousPageUrl() }}#estimate-history">Sebelumnya</a>@endif @if($history->nextPageUrl())<a href="{{ $history->nextPageUrl() }}#estimate-history">Seterusnya</a>@endif</div></nav>@endif
</details>

@endsection
