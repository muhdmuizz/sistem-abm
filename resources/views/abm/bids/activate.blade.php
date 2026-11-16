@extends('abm.layout')
@section('title', $os ? 'Aktifkan OS' : 'Aktifkan Bidaan')
@section('cycle-context', 'Kitaran ABM '.$cycle->tahun)
@section('content')
@if($os && app(\App\Services\PautanFotostat::class)->rental($os))<p class="notice">OS24000 Sewaan Mesin Fotostat dan Riso dan OS29000 Bacaan Meter akan diaktifkan bersama. Draf sedia ada dikekalkan.</p>@endif
<div class="page-heading"><div><p class="eyebrow">Bida Peruntukan</p><h1>{{ $os ? 'Aktifkan OS' : 'Aktifkan Bidaan' }}</h1></div><a class="button" href="{{ $backUrl }}">Kembali</a></div>
@include('abm.bids.feedback')
<section class="activation-panel">
<h2>{{ $activity->nama }}</h2>
<dl class="bid-context-facts"><div><dt>Kitaran ABM</dt><dd>{{ $cycle->tahun }}</dd></div><div><dt>PTJ</dt><dd>{{ $ptj->nama }}</dd></div>@if($os)<div><dt>OS</dt><dd>OS{{ $os->kod_objek }} — {{ $os->butiran_perbelanjaan }}</dd></div>@endif</dl>
<p class="activation-explanation">{{ $os ? 'OS akan ditambahkan pada Bidaan ini. Penyediaan butiran OS belum dibuka.' : 'Pengaktifan membolehkan Penyedia Anggaran mengaktifkan OS yang dibenarkan dalam Bidaan ini.' }}</p>
@if($activation['needs_reason'])<div class="notice caution"><p>Tindakan ini menggunakan pintasan Superadmin dan akan direkod dalam jejak audit.</p>@if(! $activation['central'])<p>Kebenaran pusat tidak membenarkan tindakan ini.</p>@endif @if(! $activation['open'])<p>Tiada Fasa dibuka dalam tempoh bagi konteks ini.</p>@endif</div>@endif
<form method="post" action="{{ $postUrl }}" data-track-unsaved>@csrf
<input type="hidden" name="activation_context" value="{{ $context }}"><input type="hidden" name="semakan" value="{{ $bid?->semakan ?? 0 }}">
@if($activation['needs_reason'])
<div class="action-field"><label for="activation-reason">Alasan pintasan <span class="required">*</span></label><textarea id="activation-reason" name="reason" rows="3" maxlength="1000" required @if($errors->has('reason')) aria-invalid="true" aria-describedby="activation-reason-error" @endif>{{ old('activation_context') === $context && is_scalar(old('reason')) ? old('reason') : '' }}</textarea>
@if($errors->has('reason'))<p id="activation-reason-error" class="field-error">{{ $errors->first('reason') }}</p>@endif</div>
@endif
<div class="form-actions"><button class="button primary" type="submit"><x-abm-icon name="plus"/>{{ $os ? 'Aktifkan OS' : 'Aktifkan Bidaan' }}</button><a class="button" href="{{ $backUrl }}">Batal</a></div>
</form>
</section>
@endsection
