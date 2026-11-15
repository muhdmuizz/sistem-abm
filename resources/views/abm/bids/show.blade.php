@extends('abm.layout')
@section('title', 'Bidaan — '.$bid->aktivitiKewangan->nama)
@section('cycle-context', 'Kitaran ABM '.$bid->kitaran->tahun)
@section('content')
<div class="page-heading"><div><p class="eyebrow">Bida Peruntukan</p><h1>{{ $bid->aktivitiKewangan->nama }}</h1></div><a class="button" href="{{ route('bids.index',['kitaran_id'=>$bid->kitaran_id,'ptj_id'=>$bid->ptj_id]) }}">Kembali ke Bidaan</a></div>
@include('abm.bids.feedback')
<dl class="bid-context-facts"><div><dt>Kitaran ABM</dt><dd>{{ $bid->kitaran->tahun }}</dd></div><div><dt>PTJ</dt><dd>{{ $bid->ptj->nama }}</dd></div><div><dt>Status Bidaan</dt><dd>{{ \App\Services\RuangBidaPeruntukan::STATUS[$bid->status->value] }}</dd></div><div><dt>Tempoh penyediaan</dt><dd>{{ $open ? 'Fasa dibuka dalam tempoh' : 'Tiada Fasa dibuka dalam tempoh' }}</dd></div></dl>
<div class="bid-section-heading"><h2>Program/ Aktiviti</h2><a class="button" href="{{ route('programs.index',$bid->id) }}">Buka Program/ Aktiviti</a></div>
<div class="bid-section-heading"><h2>OS tersedia</h2><p class="small">{{ $cards->count() }} OS</p></div>
@if(! $bid->diaktifkan)<p class="notice">Bidaan belum diaktifkan. Pengaktifan OS belum tersedia.</p>@endif
@if($cards->isEmpty())
<section class="empty-state"><h2>Tiada OS tersedia dalam skop anda.</h2><p>Hubungi pentadbir untuk menyemak kebenaran OS bagi Aktiviti Kewangan ini.</p></section>
@else
<div class="bid-catalogue os-catalogue">
@foreach($cards as $card)
@php($estimate=$card['estimate'])
<article id="os-{{ $card['os']->id }}" @class(['bid-card','inactive'=>!$estimate?->diaktifkan])>
<div class="bid-card-heading"><div><p class="os-reference">OS{{ $card['os']->kod_objek }}</p><h3>{{ $card['label'] ?? $card['os']->butiran_perbelanjaan }}</h3></div><span class="status-pill {{ $estimate?->diaktifkan ? 'positive' : '' }}">{{ $estimate?->diaktifkan ? 'Diaktifkan' : 'Belum Aktif' }}</span></div>
@if($estimate?->diaktifkan)<p class="small">{{ app(\App\Services\KatalogDefinisiAnggaran::class)->untuk($card['os'],$bid) !== null ? 'Sediakan butiran melalui jadual draf.' : 'Penyediaan butiran OS ini belum dibuka.' }}</p><div class="bid-card-actions"><a class="button" href="{{ route('estimates.show',[$bid->id,$estimate->id]) }}"><x-abm-icon name="chevron"/>Buka<span class="sr-only"> OS{{ $card['os']->kod_objek }} {{ $card['os']->butiran_perbelanjaan }}</span></a></div>
@elseif($card['activation']['allowed'])
<div class="bid-card-actions"><a class="button primary" href="{{ route('bids.os-form',[$bid->id,$card['os']->id]) }}"><x-abm-icon name="plus"/>Aktifkan OS<span class="sr-only"> OS{{ $card['os']->kod_objek }} {{ $card['os']->butiran_perbelanjaan }}</span></a></div>
@if($card['activation']['needs_reason'])<p class="small">Alasan pintasan Superadmin diperlukan.</p>@endif
@else<p class="small">{{ $card['activation']['explanation'] }}</p>@endif
@if(isset($card['previous']) && $card['previous']->isNotEmpty())
<details class="os-previous"><summary>Rekod sedia ada</summary><p class="small">Rekod Utiliti Pukal terdahulu dikekalkan. Penyediaan baharu menggunakan Perhubungan dan Utiliti.</p>
@foreach($card['previous'] as $previous)@if($previous['estimate']->diaktifkan)<a class="button" href="{{ route('estimates.show',[$bid->id,$previous['estimate']->id]) }}">Buka rekod Utiliti Pukal</a>@else<p class="small">Rekod Utiliti Pukal belum aktif. Rekod dikekalkan.</p>@endif@endforeach
</details>
@endif
</article>
@endforeach
</div>
@endif
@endsection
