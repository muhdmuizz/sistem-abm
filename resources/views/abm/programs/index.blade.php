@extends('abm.layout')
@section('title', 'Program/ Aktiviti')
@section('cycle-context', 'Kitaran ABM '.$bid->kitaran->tahun)
@section('content')
<link rel="stylesheet" href="{{ asset('programs.css') }}">
<div class="page-heading"><div><p class="eyebrow">Bida Peruntukan</p><h1>Program/ Aktiviti</h1><p class="intro">{{ $bid->aktivitiKewangan->nama }} · {{ $bid->ptj->nama }} · {{ $bid->kitaran->tahun }}</p></div><a class="button" href="{{ route('bids.show',$bid->id) }}">Kembali ke Bidaan</a></div>
@include('abm.bids.feedback')
<div class="bid-section-heading"><p>{{ $programs->total() }} Program/ Aktiviti</p>@if($editable)<a class="button primary" href="{{ route('programs.create',$bid->id) }}"><x-abm-icon name="plus"/>Tambah Program/ Aktiviti</a>@endif</div>
@if(!$editable)<p class="notice">Paparan sahaja. Penyediaan memerlukan Penyedia Anggaran, Bidaan aktif dalam status penyediaan dan Fasa yang dibuka.</p>@endif
@if($programs->isEmpty())
<section class="empty-state"><h2>Tiada Program/ Aktiviti dalam skop anda.</h2><p>Program/ Aktiviti yang disimpan akan dipaparkan di sini.</p></section>
@else
<div class="data-table program-list"><table><thead><tr><th>Program/ Aktiviti</th><th>Tempat Bertugas</th><th>Tarikh</th><th>Tindakan</th></tr></thead><tbody>
@foreach($programs as $entry)<tr><td data-label="Program/ Aktiviti"><strong>{{ $entry->nama ?? 'Draf tanpa nama' }}</strong><div class="small">{{ $entry->jenis ?? 'Jenis belum dipilih' }} · {{ $entry->kategori ?? 'Kategori belum dipilih' }}</div></td><td data-label="Tempat Bertugas">{{ $entry->tempatBertugas->nama }}</td><td data-label="Tarikh">{{ $entry->tarikh_mula?->format('d/m/Y') ?? 'Belum ditetapkan' }}@if($entry->tarikh_tamat) – {{ $entry->tarikh_tamat->format('d/m/Y') }}@endif</td><td data-label="Tindakan"><a class="button" href="{{ route('programs.edit',[$bid->id,$entry->id]) }}">{{ $editable && $places->contains('id',$entry->tempat_bertugas_id) ? 'Pinda' : 'Lihat' }}<span class="sr-only"> {{ $entry->nama ?? 'draf tanpa nama' }}</span></a></td></tr>@endforeach
</tbody></table></div>
<div class="pagination"><span>Halaman {{ $programs->currentPage() }} daripada {{ $programs->lastPage() }}</span><div>@if($programs->previousPageUrl())<a href="{{ $programs->previousPageUrl() }}">Sebelumnya</a>@endif @if($programs->nextPageUrl())<a href="{{ $programs->nextPageUrl() }}">Seterusnya</a>@endif</div></div>
@endif
@endsection
