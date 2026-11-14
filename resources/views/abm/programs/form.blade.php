@extends('abm.layout')
@section('title', ($program ? 'Program/ Aktiviti' : 'Tambah Program/ Aktiviti'))
@section('cycle-context', 'Kitaran ABM '.$bid->kitaran->tahun)
@section('content')
<link rel="stylesheet" href="{{ asset('programs.css') }}">
<script src="{{ asset('programs.js') }}" defer></script>
@php
$scalar = fn($value) => is_scalar($value) ? (string)$value : '';
$value = fn($field) => $scalar(old('maklumat.'.$field, in_array($field,['tarikh_mula','tarikh_tamat']) ? $program?->{$field}?->toDateString() : $program?->{$field}));
$workplace = $scalar(old('tempat_bertugas_id',$program?->tempat_bertugas_id ?? ($places->count()===1 ? $places->first()->id : null)));
$selectedComponents = old('maklumat.komponen',$program?->komponen->pluck('komponen')->all() ?? []);
$selectedComponents = is_array($selectedComponents) ? $selectedComponents : [];
@endphp
<div class="page-heading"><div><p class="eyebrow">Bida Peruntukan</p><h1>{{ $program ? 'Program/ Aktiviti' : 'Tambah Program/ Aktiviti' }}</h1><p class="intro">{{ $bid->aktivitiKewangan->nama }} · {{ $bid->ptj->nama }} · {{ $bid->kitaran->tahun }}</p></div><a class="button" href="{{ route('programs.index',$bid->id) }}">Kembali ke Senarai</a></div>
@if(session('status'))<p class="notice" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="notice error" role="alert" tabindex="-1" data-program-errors data-messages="{{ json_encode($errors->getMessages()) }}"><h2>Draf belum disimpan</h2><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><a href="{{ $program ? route('programs.edit',[$bid->id,$program->id]) : route('programs.create',$bid->id) }}">Muat semula halaman</a><p>Salin perubahan anda sebelum memuat semula.</p></div>@endif
@if(!$editable)<p class="notice">Paparan sahaja. Penyediaan tidak tersedia untuk skop atau status semasa.</p>@else<p class="small">Objektif diperlukan untuk menyimpan draf. Maklumat lain boleh dilengkapkan kemudian. Penyediaan butiran anggaran belum dibuka.</p>@endif
<nav class="program-section-nav" aria-label="Bahagian Program/ Aktiviti"><a href="#program-objectives">A. Objektif</a><a href="#program-details">B. Maklumat</a><a href="#program-implementation">C. Pelaksanaan</a><a href="#program-headcounts">D. Bilangan</a></nav>
<form method="post" action="{{ $program ? route('programs.update',[$bid->id,$program->id]) : route('programs.store',$bid->id) }}" class="program-form" data-program-form data-track-unsaved>
@csrf @if($program) @method('PUT') @endif
<input type="hidden" name="semakan_bidaan" value="{{ $scalar(old('semakan_bidaan',$bid->semakan)) }}">
@if($program)<input type="hidden" name="semakan_program" value="{{ $scalar(old('semakan_program',$program->semakan)) }}">@endif
<fieldset @disabled(!$editable) class="program-fields">
<section class="program-section"><h2>Konteks</h2>@if($editable)<div class="program-choice"><label for="program-place">Tempat Bertugas <span class="program-required" aria-hidden="true">*</span></label><input type="search" aria-label="Cari Tempat Bertugas" placeholder="Cari Tempat Bertugas" data-program-search="program-place"><select id="program-place" name="tempat_bertugas_id" required><option value="">Pilih Tempat Bertugas</option>@foreach($places as $place)<option value="{{ $place->id }}" @selected($workplace===(string)$place->id)>{{ $place->nama }}</option>@endforeach</select></div>@else<p>{{ $program?->tempatBertugas->nama }}</p>@endif</section>
<section class="program-section" id="program-objectives"><h2>A. Objektif</h2><div class="program-objectives">
<label for="program-teras">Teras Strategik <span class="small">(wajib) <span class="program-required" aria-hidden="true">*</span></span><select id="program-teras" aria-describedby="program-teras-description" name="maklumat[teras_strategik_id]" required><option value="">Pilih Teras Strategik</option>@foreach($objectives as $teras)<option value="{{ $teras->id }}" @selected($value('teras_strategik_id')===(string)$teras->id)>{{ $teras->nombor }} — {{ $teras->tajuk }}</option>@endforeach</select><span class="small program-objective-description" id="program-teras-description" data-objective-description="program-teras"></span></label>
<label for="program-strategy">Strategi <span class="small">(wajib) <span class="program-required" aria-hidden="true">*</span></span><select id="program-strategy" aria-describedby="program-strategy-description" name="maklumat[strategi_id]" required><option value="">Pilih Strategi</option>@foreach($objectives as $teras)@foreach($teras->strategi as $strategy)<option data-parent="{{ $teras->id }}" value="{{ $strategy->id }}" @selected($value('strategi_id')===(string)$strategy->id)>{{ $strategy->nombor }} — {{ $strategy->tajuk }}</option>@endforeach @endforeach</select><span class="small program-objective-description" id="program-strategy-description" data-objective-description="program-strategy"></span></label>
<label for="program-initiative">Prakarsa <span class="small">(wajib) <span class="program-required" aria-hidden="true">*</span></span><select id="program-initiative" aria-describedby="program-initiative-description" name="maklumat[prakarsa_id]" required><option value="">Pilih Prakarsa</option>@foreach($objectives as $teras)@foreach($teras->strategi as $strategy)@foreach($strategy->prakarsa as $initiative)<option data-parent="{{ $strategy->id }}" value="{{ $initiative->id }}" @selected($value('prakarsa_id')===(string)$initiative->id)>{{ $initiative->nombor }} — {{ $initiative->tajuk }}</option>@endforeach @endforeach @endforeach</select><span class="small program-objective-description" id="program-initiative-description" data-objective-description="program-initiative"></span></label>
</div></section>
<section class="program-section" id="program-details"><h2>B. Maklumat Program/ Aktiviti</h2><label for="program-name">Nama Program/ Aktiviti<input id="program-name" name="maklumat[nama]" value="{{ $value('nama') }}"></label><div class="program-grid">
@foreach(['jenis'=>'Jenis Program/ Aktiviti','kategori'=>'Kategori Program/ Aktiviti'] as $field=>$label)<label for="program-{{ $field }}">{{ $label }}<select id="program-{{ $field }}" name="maklumat[{{ $field }}]"><option value="">Pilih {{ $label }}</option>@foreach($field==='jenis' ? \App\Services\DrafProgramAktiviti::JENIS : \App\Services\DrafProgramAktiviti::KATEGORI as $choice)<option @selected($value($field)===$choice)>{{ $choice }}</option>@endforeach</select></label>@endforeach
</div></section>
<section class="program-section" id="program-implementation"><h2>C. Pelaksanaan</h2><div class="program-grid">
@foreach(['peringkat'=>'Peringkat','kaedah'=>'Kaedah Pelaksanaan'] as $field=>$label)<label for="program-{{ $field }}">{{ $label }}<select id="program-{{ $field }}" name="maklumat[{{ $field }}]"><option value="">Pilih {{ $label }}</option>@foreach($field==='peringkat' ? \App\Services\DrafProgramAktiviti::PERINGKAT : \App\Services\DrafProgramAktiviti::KAEDAH as $choice)<option @selected($value($field)===$choice)>{{ $choice }}</option>@endforeach</select></label>@endforeach
@foreach(['tarikh_mula'=>'Tarikh Mula','tarikh_tamat'=>'Tarikh Tamat'] as $field=>$label)<label for="program-{{ $field }}">{{ $label }}<input type="date" id="program-{{ $field }}" name="maklumat[{{ $field }}]" min="{{ $bid->kitaran->tahun }}-01-01" max="{{ $bid->kitaran->tahun }}-12-31" value="{{ $value($field) }}"></label>@endforeach
</div><p class="small">Tarikh Program/ Aktiviti mesti dalam tahun {{ $bid->kitaran->tahun }}.</p><fieldset class="program-package" data-program-package><legend>Komponen Pakej — pilih sekurang-kurangnya satu</legend>@foreach(\App\Services\DrafProgramAktiviti::KOMPONEN as $choice)<label><input type="checkbox" name="maklumat[komponen][]" value="{{ $choice }}" @checked(in_array($choice,$selectedComponents,true))> {{ $choice }}</label>@endforeach</fieldset>
</section>
<section class="program-section" id="program-headcounts"><h2>D. Bilangan Jemputan/ Urus Setia dan Peserta</h2><p class="small">Jumlah dikira daripada baris. Setiap baris memerlukan klasifikasi dan bilangan bulat positif. Kedua-dua kumpulan perlu dilengkapkan sebelum maklumat program lengkap.</p>
@foreach(['jemputan'=>'Jemputan/ Urus Setia','peserta'=>'Peserta'] as $group=>$label)
@php
$rows = old('maklumat.'.$group,$program?->bilangan->where('kumpulan',$group)->sortBy('turutan')->map(fn($r)=>$r->only(['kategori','klasifikasi','catatan','bilangan']))->values()->all() ?? []);
$rows = is_array($rows) ? array_values($rows) : [];
@endphp
<div class="program-people" data-person-group="{{ $group }}"><div class="bid-section-heading"><h3>{{ $label }}</h3><p>Jumlah: <output data-person-total>{{ $program?->jumlah($group) ?? '0' }}</output></p></div><div data-person-rows>@foreach($rows as $index=>$row)@include('abm.programs.headcount-row')@endforeach</div><p class="small" data-no-person>Belum ada baris.</p>@if($editable)<button type="button" class="button" data-add-person>Tambah {{ $label }}</button>@endif<template>@include('abm.programs.headcount-row',['index'=>'NEWROW','row'=>[]])</template></div>
@endforeach
</section>
@if($editable)<section class="program-section">@if($needsReason)<label for="program-reason">Alasan pintasan Superadmin (wajib)<textarea id="program-reason" name="reason" required maxlength="1000">{{ $scalar(old('reason')) }}</textarea></label>@endif<div class="program-save"><button class="button primary" type="submit"><x-abm-icon name="save"/>Simpan Draf</button><p class="small" role="status" data-program-save-status>{{ $errors->any() ? 'Perubahan belum disimpan.' : 'Tiada perubahan belum disimpan.' }}</p></div></section>@endif
</fieldset></form>
@endsection
