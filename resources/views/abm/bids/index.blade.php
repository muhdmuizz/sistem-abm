@extends('abm.layout')
@section('title', 'Bida Peruntukan')
@section('cycle-context', $cycle ? 'Kitaran ABM '.$cycle->tahun : 'Bida Peruntukan')
@section('content')
<div class="page-heading"><div><h1>Bida Peruntukan</h1><p class="intro">Paparkan Bidaan mengikut Kitaran ABM dan PTJ.</p></div></div>
@include('abm.bids.feedback')
@if($cycles->isEmpty())
<section class="empty-state" aria-label="Kitaran belum tersedia"><h2>Belum ada Kitaran ABM.</h2><p>Pentadbir perlu menambah Kitaran sebelum Bidaan boleh disediakan.</p>
@if(app(\App\Services\RuangBidaPeruntukan::class)->admin(auth()->user()))<a class="button" href="{{ route('cycles.create') }}"><x-abm-icon name="plus"/>Tambah Kitaran</a>@endif</section>
@elseif($ptjs->isEmpty())
<section class="empty-state"><h2>Tiada PTJ dalam skop anda.</h2><p>Hubungi pentadbir untuk menyemak penugasan peranan.</p></section>
@else
<form method="get" action="{{ route('bids.index') }}" class="workspace-context" aria-label="Pilihan Bidaan">
<div><label for="bid-cycle">Kitaran ABM <span class="required">*</span></label><select id="bid-cycle" name="kitaran_id" required><option value="">Pilih Kitaran</option>@foreach($cycles as $option)<option value="{{ $option->id }}" @selected($cycle?->id === $option->id)>{{ $option->tahun }}</option>@endforeach</select></div>
<div><label for="bid-ptj">PTJ <span class="required">*</span></label><div data-ptj-combobox><select id="bid-ptj" name="ptj_id" required><option value="">Pilih PTJ</option>@foreach($ptjs as $option)<option value="{{ $option->id }}" @selected($ptj?->id === $option->id)>{{ $option->nama }}</option>@endforeach</select></div></div>
<div class="context-actions"><button class="button primary" type="submit">Papar Bidaan</button><a class="button subtle" href="{{ route('bids.index') }}">Set Semula</a></div>
</form>
@if(! $cycle || ! $ptj)
<section class="empty-state"><h2>Pilih Kitaran ABM dan PTJ.</h2><p>Aktiviti Kewangan yang dibenarkan akan dipaparkan di sini.</p></section>
@elseif($cards->isEmpty())
<section class="empty-state"><h2>Tiada Aktiviti Kewangan dibenarkan.</h2><p>Semak penugasan dan kebenaran pusat bagi {{ $ptj->nama }}.</p></section>
@else
<div class="bid-section-heading"><h2>Aktiviti Kewangan</h2><p class="small">{{ $ptj->nama }} · Kitaran {{ $cycle->tahun }}</p></div>
<div class="bid-catalogue">
@foreach($cards as $card)
@php($bid=$card['bid'])
<article @class(['bid-card','inactive'=>!$bid?->diaktifkan])>
<div class="bid-card-heading"><h3>{{ $card['activity']->nama }}</h3><span class="status-pill {{ $bid?->diaktifkan ? 'positive' : '' }}">{{ $bid?->diaktifkan ? 'Diaktifkan' : 'Belum Aktif' }}</span></div>
@if($bid?->diaktifkan)<p class="bid-state">{{ \App\Services\RuangBidaPeruntukan::STATUS[$bid->status->value] }}</p>
<div class="bid-card-actions"><a class="button" href="{{ route('bids.show',$bid->id) }}">Buka Bidaan<x-abm-icon name="chevron"/></a></div>
@elseif($card['activation']['allowed'])
<p class="small">Aktifkan Bidaan sebelum menyediakan OS.</p>
<div class="bid-card-actions"><a class="button primary" href="{{ route('bids.activate-form',[$cycle->id,$ptj->id,$card['activity']->id]) }}"><x-abm-icon name="plus"/>Aktifkan Bidaan</a></div>
@if($card['activation']['needs_reason'])<p class="small">Alasan pintasan Superadmin diperlukan.</p>@endif
@else<p class="small">{{ $card['activation']['explanation'] }}</p>@endif
</article>
@endforeach
</div>
@endif
@endif
@endsection
