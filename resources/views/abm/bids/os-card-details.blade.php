@php($estimate=$card['estimate'])
<div class="bid-card-heading"><div><p class="os-reference">OS{{ $card['os']->kod_objek }}</p><h3>{{ $card['os']->butiran_perbelanjaan }}</h3></div><span class="status-pill {{ $estimate?->diaktifkan ? 'positive' : '' }}">{{ $estimate?->diaktifkan ? 'Diaktifkan' : 'Belum Aktif' }}</span></div>
@if($estimate?->diaktifkan)<p class="small">{{ app(\App\Services\KatalogDefinisiAnggaran::class)->untuk($card['os'],$bid) !== null ? 'Sediakan butiran melalui jadual draf.' : 'Penyediaan butiran OS ini belum dibuka.' }}</p><div class="bid-card-actions"><a class="button" href="{{ route('estimates.show',[$bid->id,$estimate->id]) }}"><x-abm-icon name="chevron"/>Buka<span class="sr-only"> OS{{ $card['os']->kod_objek }} {{ $card['os']->butiran_perbelanjaan }}</span></a></div>
@elseif($card['activation']['allowed'])
<div class="bid-card-actions"><a class="button primary" href="{{ route('bids.os-form',[$bid->id,$card['os']->id]) }}"><x-abm-icon name="plus"/>Aktifkan OS<span class="sr-only"> OS{{ $card['os']->kod_objek }} {{ $card['os']->butiran_perbelanjaan }}</span></a></div>
@if($card['activation']['needs_reason'])<p class="small">Alasan pintasan Superadmin diperlukan.</p>@endif
@else<p class="small">{{ $card['activation']['explanation'] }}</p>@endif
