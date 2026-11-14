@extends('abm.layout')
@section('title','OS'.$os->kod_objek.' — '.$os->butiran_perbelanjaan)
@section('cycle-context','Kitaran ABM '.$bid->kitaran->tahun)
@section('content')
<link rel="stylesheet" href="{{ asset('anggaran-grid.css') }}">
<script src="{{ asset('anggaran-grid.js') }}" defer></script>
<div class="page-heading"><div><p class="eyebrow">Anggaran</p><h1>OS{{ $os->kod_objek }} — {{ $os->butiran_perbelanjaan }}</h1><p class="intro">{{ $bid->ptj->nama }} · {{ $bid->aktivitiKewangan->nama }} · {{ $bid->kitaran->tahun }}</p></div><a class="button" href="{{ route('estimates.show',[$bid->id,$estimate->id]) }}">Kembali ke Anggaran</a></div>
@if(session('status'))<p class="notice success" role="status">{{ session('status') }}</p>@endif
<p class="notice">Simpan Draf untuk menyimpan butiran. Jumlah setiap baris ialah pratonton berdasarkan rujukan semasa; jumlah rasmi dan kelengkapan Anggaran belum diaktifkan.</p>
@if(!$editable)<p class="notice">Paparan sahaja. Penyediaan tertakluk pada peranan, kebenaran OS, status Bidaan dan Fasa semasa.</p>@endif
<form data-anggaran-grid data-track-unsaved method="post" action="{{ route('estimates.editor-save',[$bid->id,$estimate->id]) }}"
 data-records="{{ json_encode($records) }}" data-places="{{ json_encode($places) }}" data-references="{{ json_encode($references) }}"
 data-columns="{{ json_encode($columns) }}" data-reference-selector="{{ $referenceSelector }}"
 data-editable="{{ $editable ? 'yes' : 'no' }}" data-bid-revision="{{ $bid->semakan }}" data-estimate-revision="{{ $estimate->semakan }}">
@csrf
<div class="notice error" role="alert" tabindex="-1" data-grid-error hidden></div>
@if($editable)
<div class="grid-toolbar"><label>Tambah baris<input type="number" min="1" step="1" value="5" data-grid-add-count></label><button type="button" class="button" data-grid-add><x-abm-icon name="plus"/>Tambah Baris</button><button type="button" class="button" data-grid-copy>Salin Baris Dipilih</button><button type="button" class="button danger" data-grid-remove>Padam Baris Dipilih</button><button type="button" class="button" data-grid-undo hidden>Batal Padam</button></div>
<p class="small grid-help">Tab/ Shift+Tab untuk bergerak antara sel. Tampal lajur input mengikut turutan: {{ implode(', ',$inputLabels) }}. {{ $inputHelp }}</p>
@endif
<div class="anggaran-grid-scroll" tabindex="0" role="region" aria-label="Jadual Butiran Anggaran boleh ditatal mendatar">
<table class="anggaran-grid" style="min-width:{{ 160+array_sum(array_column($columns,'width')) }}px"><caption class="sr-only">Butiran Anggaran dengan identiti baris tersimpan</caption><colgroup><col style="width:60px"><col style="width:100px">@foreach($columns as $column)<col style="width:{{ $column['width'] }}px">@endforeach</colgroup><thead><tr><th>Pilih</th><th>Rujukan</th>@foreach($columns as $column)<th>{{ $column['label'] }}</th>@endforeach</tr></thead><tbody data-grid-body></tbody></table>
</div>
<p class="empty-state" data-grid-empty>Tiada Butiran Anggaran. Tambah baris untuk mula menyediakan draf.</p>
<noscript><p class="notice error">Aktifkan JavaScript untuk menggunakan jadual Butiran Anggaran.</p></noscript>
@if($editable)
@if($needsReason)<label class="grid-reason">Alasan pintasan Superadmin (wajib)<textarea maxlength="1000" required name="reason"></textarea></label>@endif
<div class="grid-save"><button type="submit" class="button primary"><x-abm-icon name="save"/>Simpan Draf</button><span role="status" class="small" data-grid-status>Tiada perubahan belum disimpan.</span></div>
@endif
</form>
<div class="pagination"><span>Halaman {{ $sources->currentPage() }} daripada {{ $sources->lastPage() }} · {{ $sources->total() }} rekod dalam skop anda</span><div>@if($sources->previousPageUrl())<a href="{{ $sources->previousPageUrl() }}">Sebelumnya</a>@endif @if($sources->nextPageUrl())<a href="{{ $sources->nextPageUrl() }}">Seterusnya</a>@endif</div></div>
@endsection
