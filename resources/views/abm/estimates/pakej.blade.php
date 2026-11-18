@extends('abm.layout')
@section('title','OS'.$os->kod_objek.' — '.$os->butiran_perbelanjaan)
@section('cycle-context','Kitaran ABM '.$bid->kitaran->tahun)
@section('content')
<link rel="stylesheet" href="{{ asset('anggaran-grid.css') }}">
<link rel="stylesheet" href="{{ asset('pakej-grid.css') }}">
<script src="{{ asset('abm-money.js') }}" defer></script>
<script src="{{ asset('pakej-grid.js') }}" defer></script>
<div class="page-heading"><div><p class="eyebrow">Anggaran</p><h1>OS{{ $os->kod_objek }} — {{ $os->butiran_perbelanjaan }}</h1><p class="intro">{{ $bid->ptj->nama }} · {{ $bid->aktivitiKewangan->nama }} · {{ $bid->kitaran->tahun }}</p></div><a class="button" href="{{ route('estimates.show',[$bid->id,$estimate->id]) }}">Kembali ke Anggaran</a></div>
@if(session('status'))<p class="notice success" role="status">{{ session('status') }}</p>@endif
<p class="notice">Simpan Draf untuk menyimpan aktiviti dan komponennya. Jumlah ialah pratonton; jumlah rasmi dan kelengkapan Anggaran belum diaktifkan.</p>
@if(!$editable)<p class="notice">Paparan sahaja. Penyediaan tertakluk pada peranan, kebenaran OS, status Bidaan dan Fasa semasa.</p>@endif
<form data-pakej-grid data-track-unsaved method="post" action="{{ route('estimates.editor-save',[$bid->id,$estimate->id]) }}"
 data-columns="{{ json_encode($columns) }}" data-reference-selector="{{ $referenceSelector }}" data-reference-child-selector="{{ $referenceChildSelector }}" data-group-label="{{ $groupLabel }}" data-total-label="{{ $groupLabel==='Umum' ? 'Jumlah Aktiviti' : 'Jumlah Pakej' }} — Pratonton" data-records="{{ json_encode($records) }}" data-places="{{ json_encode($places) }}" data-references="{{ json_encode($references) }}" data-year="{{ $bid->kitaran->tahun }}"
 data-editable="{{ $editable ? 'yes' : 'no' }}" data-bid-revision="{{ $bid->semakan }}" data-estimate-revision="{{ $estimate->semakan }}">
@csrf
<div class="notice error" role="alert" tabindex="-1" data-pakej-error hidden></div>
@if($editable)<div class="grid-toolbar"><button type="button" class="button" data-add-activity><x-abm-icon name="plus"/>Tambah Aktiviti</button><button type="button" class="button" data-undo-activity hidden>Batal Padam</button></div>@endif
<div data-activities></div>
<p class="empty-state" data-pakej-empty>Tiada aktiviti {{ $groupLabel }}.@if($editable) Tambah aktiviti untuk mula menyediakan draf.@endif</p>
<noscript><p class="notice error">Aktifkan JavaScript untuk menyediakan Pakej.</p></noscript>
@if($editable)
@if($needsReason)<label class="grid-reason">Alasan pintasan Superadmin (wajib)<textarea maxlength="1000" required name="reason"></textarea></label>@endif
<div class="grid-save"><button type="submit" class="button primary"><x-abm-icon name="save"/>Simpan Draf</button><span role="status" class="small" data-pakej-status>Tiada perubahan belum disimpan.</span></div>
@endif
</form>
<div class="pagination"><span>Halaman {{ $sources->currentPage() }} daripada {{ $sources->lastPage() }} · {{ $sources->total() }} aktiviti dalam skop anda</span><div>@if($sources->previousPageUrl())<a href="{{ $sources->previousPageUrl() }}">Sebelumnya</a>@endif @if($sources->nextPageUrl())<a href="{{ $sources->nextPageUrl() }}">Seterusnya</a>@endif</div></div>
@include('abm.estimates.catatan', ['grouped' => true])
@endsection
