@extends('abm.layout')
@section('title', 'Tambah Kitaran')
@section('cycle-context', 'Pentadbiran Kitaran ABM')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Kitaran ABM</p><h1>Tambah Kitaran</h1></div><a class="button" href="{{ route('cycles.index') }}">Kembali</a></div>
@if($errors->any())<div class="notice error" role="alert">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>@endif
<form method="post" action="{{ route('cycles.store') }}" class="form-section compact-form" data-track-unsaved>@csrf
<label for="cycle-year">Tahun Kitaran <span class="required">*</span></label><input id="cycle-year" name="tahun" type="number" min="1" max="9999" required value="{{ is_scalar(old('tahun')) ? old('tahun') : '' }}">
<div class="form-actions"><button class="button primary" type="submit"><x-abm-icon name="save"/>Simpan Kitaran</button></div></form>
@endsection
