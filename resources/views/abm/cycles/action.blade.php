@extends('abm.layout')
@php($labels=['buka'=>'Buka','tutup'=>'Tutup','buka-semula'=>'Buka Semula','lanjutkan'=>'Lanjutkan'])
@section('title', $labels[$action].' Fasa')
@section('cycle-context', 'Kitaran ABM '.$cycle->tahun)
@section('content')
@php($context=$cycle->id.'/'.$phase->id.'/'.$action)
@php($useOld=old('action_context')===$context)
<div class="page-heading"><div><p class="eyebrow">Kitaran {{ $cycle->tahun }}</p><h1>{{ $labels[$action] }} {{ $phase->label() }}</h1></div><a class="button" href="{{ route('cycles.index',['tahun'=>$cycle->tahun]) }}">Kembali</a></div>
@if($errors->any())<div class="notice error" role="alert">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>@endif
<section class="form-section"><h2>Maklumat Fasa</h2><div class="phase-facts"><div><span class="small">Status Semasa</span><p>{{ $phase->dibuka ? 'Dibuka' : 'Ditutup' }}</p></div><div><span class="small">Tempoh</span><p>{{ $phase->tarikh_mula->format('d/m/Y') }} — {{ $phase->tarikh_tamat->format('d/m/Y') }}</p></div><div><span class="small">Skop Pembukaan</span>@include('abm.cycles.scope-summary')</div></div><p class="notice">Tindakan terpakai pada skop Fasa ini. Rekod dan status Bidaan tidak berubah secara automatik.</p>
<form method="post" action="{{ route('phases.execute',[$cycle->id,$phase->id,$action]) }}" data-track-unsaved data-confirm="{{ $labels[$action] }} {{ $phase->label() }} bagi Kitaran {{ $cycle->tahun }}?">@csrf
<input type="hidden" name="action_context" value="{{ $context }}"><input type="hidden" name="semakan" value="{{ $useOld && is_scalar(old('semakan')) ? old('semakan') : $phase->semakan }}">
@if($action==='lanjutkan')<div class="action-field"><label for="extend-end">Tarikh Tamat Baharu <span class="required">*</span></label><input id="extend-end" name="tarikh_tamat" type="date" min="{{ $phase->tarikh_tamat->addDay()->toDateString() }}" max="{{ sprintf('%04d-12-31',$cycle->tahun) }}" value="{{ $useOld && is_scalar(old('tarikh_tamat')) ? old('tarikh_tamat') : '' }}" required></div>@endif
<div class="action-field"><label for="action-reason">Alasan tindakan <span class="required">*</span></label><textarea id="action-reason" name="reason" rows="3" maxlength="1000" required>{{ $useOld && is_scalar(old('reason')) ? old('reason') : '' }}</textarea></div>
<div class="form-actions"><button class="button {{ $action==='tutup' ? 'caution' : 'primary' }}" type="submit"><x-abm-icon :name="$action==='lanjutkan' ? 'calendar' : ($action==='tutup' ? 'lock' : 'unlock')"/>{{ $labels[$action] }} Fasa</button><a class="button" href="{{ route('cycles.index',['tahun'=>$cycle->tahun]) }}">Batal</a></div>
</form></section>
@endsection
