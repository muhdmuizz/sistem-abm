@extends('abm.layout')
@section('title', 'Tetapan Akses — '.$target['label'])
@section('content')
<script src="{{ asset('permissions.js') }}" defer></script>
<div class="page-heading"><div><p class="eyebrow">Pengurusan Aktiviti Kewangan</p><h1>{{ $target['label'] }}</h1><p class="intro">{{ $target['kind'] === 'ptj' ? 'Kategori '.$target['kategori'].' · Tetapan khusus mengatasi lalai kategori bagi setiap pilihan.' : 'Lalai ini digunakan oleh PTJ yang tidak mempunyai tetapan khusus bagi pilihan berkenaan.' }}</p></div><a class="button" href="{{ route('permissions.index') }}">Kembali</a></div>
@if(session('status'))<p class="notice success" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="notice error" role="alert"><strong>Tetapan belum disimpan.</strong><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
<p class="notice">Benarkan Aktiviti Kewangan tidak memilih semua OS. OS yang belum ditetapkan tidak memberikan akses. Menutup aktiviti mengekalkan tetapan OS yang disimpan.</p>
@foreach($states as $state)
@php($activity = $state['activity'])
@php($useOld = old('permission_target') === $target['kind'].':'.$target['key'] && (string) old('permission_activity_id') === (string) $activity->id)
<details class="permission-activity" data-permission-activity @if($useOld) open @endif>
<summary><span>{{ $activity->nama }}</span><span class="small">Disimpan: {{ $state['count'] }} OS dibenarkan</span></summary>
<form method="post" action="{{ route('permissions.save', [$target['kind'], $target['key'], $activity->id]) }}" data-track-unsaved data-confirm="Simpan tetapan {{ $activity->nama }} untuk {{ $target['label'] }}?">
@csrf<input type="hidden" name="permission_target" value="{{ $target['kind'].':'.$target['key'] }}"><input type="hidden" name="permission_activity_id" value="{{ $activity->id }}">
<input type="hidden" name="revision" value="{{ $useOld ? old('revision') : $state['revision'] }}">
<div class="permission-parent"><h2>Akses Aktiviti Kewangan</h2>
@include('abm.permissions.setting', ['field'=>'activity', 'setting'=>$state['parent'], 'oldValues'=>$useOld ? old('activity', []) : []])
@if($target['kind'] === 'ptj')<p class="small">Buang tanda Tetapan khusus untuk kembali mengikut lalai kategori.</p>@endif</div>
<div class="permission-os-heading"><h2>Pilihan OS</h2><span class="small permission-preview" role="status">Pratonton pilihan belum disimpan</span></div>
<p class="small">Setiap baris ialah satu OS berbeza berdasarkan kod dan butiran perbelanjaan.</p>
<div class="permission-os-list">
@forelse($state['rows'] as $row)
@php($osId = $row['os']->id)
<div class="permission-os-row"><div class="permission-os-title"><span class="os-code">OS{{ $row['os']->kod_objek }}</span><span>{{ $row['os']->butiran_perbelanjaan }}</span></div>
@include('abm.permissions.setting', ['field'=>'os', 'setting'=>$row, 'oldValues'=>$useOld ? old('settings.'.$osId, []) : []])
</div>
@empty<p class="notice">Tiada OS yang layak dalam rujukan Aktiviti Kewangan ini.</p>@endforelse
</div>
<div class="permission-save"><label for="permission-reason-{{ $activity->id }}">Alasan perubahan <span class="required">*</span></label><textarea id="permission-reason-{{ $activity->id }}" name="reason" rows="2" maxlength="1000" required>{{ $useOld ? old('reason') : '' }}</textarea>
<div class="form-actions"><button class="button primary" type="submit"><x-abm-icon name="save"/>Simpan Tetapan</button></div></div>
</form></details>
@endforeach
@endsection
