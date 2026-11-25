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
@if($payrollCards->isNotEmpty())
    <article class="bid-card">
        <div class="bid-card-heading">
            <div>
                <p class="os-reference">{{ $payrollCards->pluck('os.kod_objek')->unique()->map(fn ($code) => 'OS'.$code)->implode(' · ') }}</p>
                <h3>Gaji &amp; Emolumen</h3>
            </div>
        </div>
        <div class="bid-card-actions">
            <a class="button" href="{{ route('gaji.preview', $bid->id) }}">Buka Pratonton<x-abm-icon name="chevron"/></a>
        </div>
        <details>
            <summary>Status dan tindakan OS</summary>
            @foreach($payrollCards as $payrollCard)
                <div class="section" id="os-{{ $payrollCard['os']->id }}">
                    @include('abm.bids.os-card-details', ['card' => $payrollCard])
                </div>
            @endforeach
        </details>
    </article>
@endif
@foreach($otherCards as $card)
@php($estimate=$card['estimate'])
<article id="os-{{ $card['os']->id }}" @class(['bid-card','inactive'=>!$estimate?->diaktifkan])>
@include('abm.bids.os-card-details', ['card' => $card])
</article>
@endforeach
</div>
@endif
@endsection
