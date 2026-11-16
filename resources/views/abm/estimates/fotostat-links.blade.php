@extends('abm.layout')
@section('title','Pautan Bacaan Meter')
@section('content')
<link rel="stylesheet" href="{{ asset('anggaran-grid.css') }}"><script src="{{ asset('abm-money.js') }}" defer></script><script src="{{ asset('fotostat-review.js') }}" defer></script><link rel="stylesheet" href="{{ asset('fotostat-review.css') }}">
<div class="page-heading"><div><p class="eyebrow">OS24000/ OS29000</p><h1>Pautan Bacaan Meter</h1><p class="intro">{{ $bid->ptj->nama }} · {{ $bid->aktivitiKewangan->nama }} · {{ $bid->kitaran->tahun }}</p></div><a class="button" href="{{ route('estimates.editor',[$bid->id,$rental->id]) }}">Kembali ke Sewaan</a></div>
@if(session('status'))<p class="notice success" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="notice error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<p class="notice">Pilih rekod sedia ada secara nyata. Nilai salinan, kadar sesalinan dan bulan meter dikekalkan. Tempat Bertugas, Jenis Cetakan, Lokasi dan kedua-dua tempoh kontrak akan mengikut sewaan.</p>
@if($rentals->isEmpty() || $meters->isEmpty())<div class="empty-state">Tiada pasangan rekod tersedia untuk dipautkan. Rekod meter yang sudah dipautkan tidak disenaraikan.</div>
@else
<form method="get" class="panel">
<label>Butiran Sewaan<select name="sewaan_id" required><option value="">Pilih Butiran Sewaan</option>@foreach($rentals as $row)<option value="{{ $row->id }}" @selected($r?->id===$row->id)>#{{ $row->id }} · {{ $row->tempatBertugas->nama }} · {{ $row->data_sumber->kategori1 ?? '—' }} · {{ $row->data_sumber->lokasi ?? '—' }}</option>@endforeach</select></label>
<label>Bacaan Meter Belum Dipautkan<select name="meter_id" required><option value="">Pilih Bacaan Meter</option>@foreach($meters as $row)<option value="{{ $row->id }}" @selected($m?->id===$row->id)>#{{ $row->id }} · {{ $row->tempatBertugas->nama }} · {{ $row->data_sumber->kategori1 ?? '—' }} · {{ $row->data_sumber->lokasi ?? '—' }}</option>@endforeach</select></label>
<button class="button" type="submit">Semak Pautan</button></form>
@endif
@if($r && $m)
<section class="panel fotostat-review"><h2>Semak Perubahan Butiran Bersama</h2>
<div class="anggaran-grid-scroll" tabindex="0" role="region" aria-label="Semakan butiran bersama"><table class="anggaran-grid" style="min-width:760px"><thead><tr><th>Butiran</th><th>Meter Semasa</th><th>Mengikut Sewaan #{{ $r->id }}</th></tr></thead><tbody>
<tr><th>Tempat Bertugas</th><td>{{ $m->tempatBertugas->nama }}</td><td>{{ $r->tempatBertugas->nama }}</td></tr>
@foreach(\App\Services\PautanFotostat::SHARED as $field)<tr><th>{{ ['kategori1'=>'Jenis Cetakan','lokasi'=>'Lokasi Mesin Fotostat','tarikh_mula'=>'Tarikh Mula Kontrak Semasa','tarikh_tamat'=>'Tarikh Tamat Kontrak Semasa','tarikh_mula_kontrak'=>'Tarikh Mula Kontrak Keseluruhan','tarikh_tamat_kontrak'=>'Tarikh Tamat Kontrak Keseluruhan'][$field] }}</th><td>{{ $m->data_sumber->$field ?? '—' }}</td><td>{{ $r->data_sumber->$field ?? '—' }}</td></tr>@endforeach
</tbody></table></div>
<h2>Nilai Meter Dikekalkan</h2><dl class="bid-context-facts"><div><dt>Bil. Salinan</dt><dd>{{ $m->data_sumber->bil_unit ?? '—' }}</dd></div><div><dt>Kadar Sesalinan (RM)</dt><dd class="grid-numeric" data-review-money="{{ $m->data_sumber->kadar ?? '' }}">{{ $m->data_sumber->kadar ?? '—' }}</dd></div><div><dt>Bilangan Bulan</dt><dd>{{ $m->data_sumber->bil_bulan ?? '—' }}</dd></div></dl>
@if($old)<p class="notice">Pautan semasa kepada meter #{{ $old->id }} akan diganti dan draf meter tersebut dipadam hanya jika salinan, kadar dan bulannya masih kosong. Rekod berisi tidak boleh diganti.</p>@endif
<form method="post" action="{{ route('fotostat.attach',$bid->id) }}">@csrf
<input type="hidden" name="sewaan_id" value="{{ $r->id }}"><input type="hidden" name="meter_id" value="{{ $m->id }}"><input type="hidden" name="semakan_bidaan" value="{{ $bid->semakan }}"><input type="hidden" name="semakan_sewaan" value="{{ $r->semakan }}"><input type="hidden" name="semakan_meter" value="{{ $m->semakan }}">
@if($needsReason)<label>Alasan pintasan Superadmin<textarea name="reason" required maxlength="1000"></textarea></label>@endif
<label><input type="checkbox" name="sahkan" value="1" required> Saya mengesahkan perubahan butiran bersama dan penggantian pautan yang dipaparkan.</label><button class="button primary" type="submit">Sahkan Pautan</button>
</form></section>@endif
@endsection
