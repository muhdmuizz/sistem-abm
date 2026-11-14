@extends('abm.layout')
@section('title', $phase ? 'Pinda Fasa' : 'Tambah Fasa')
@section('cycle-context', 'Kitaran ABM '.$cycle->tahun)
@section('content')
@php($context = $cycle->id.'/'.($phase?->id ?? 'new'))
@php($useOld = old('phase_context') === $context)
@php($value = fn($key, $default = '') => $useOld && is_scalar(old($key)) ? old($key) : $default)
@php($selectedScope = $value('jenis_skop', $phase?->jenis_skop ?? ''))
@php($selectedPtjs = $useOld && is_array(old('ptj_ids')) ? old('ptj_ids') : ($useOld ? [] : ($phase?->skop->pluck('ptj_id')->filter()->all() ?? [])))
@php($selectedActivities = $useOld && is_array(old('aktiviti_ids')) ? old('aktiviti_ids') : ($useOld ? [] : ($phase?->skop->pluck('aktiviti_kewangan_id')->filter()->all() ?? [])))
@php($pairs = $useOld && is_array(old('pasangan')) ? old('pasangan') : ($useOld ? [] : ($phase && $phase->jenis_skop === 'gabungan' ? $phase->skop->map(fn($s) => ['ptj_id'=>$s->ptj_id,'aktiviti_kewangan_id'=>$s->aktiviti_kewangan_id])->all() : [])))
<script src="{{ asset('fasa.js') }}" defer></script>
<div class="page-heading"><div><p class="eyebrow">Kitaran {{ $cycle->tahun }}</p><h1>{{ $phase ? 'Pinda '.$phase->label() : 'Tambah Fasa' }}</h1></div><a class="button" href="{{ route('cycles.index', ['tahun'=>$cycle->tahun]) }}">Kembali</a></div>
@if($errors->any())<div class="notice error" role="alert"><strong>Fasa belum disimpan.</strong><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
<form method="post" action="{{ $phase ? route('phases.update', [$cycle->id,$phase->id]) : route('phases.store',$cycle->id) }}" class="form-section" data-track-unsaved data-confirm="Simpan maklumat Fasa bagi Kitaran {{ $cycle->tahun }}?">
@csrf @if($phase) @method('PUT') @endif<input type="hidden" name="phase_context" value="{{ $context }}"><input type="hidden" name="semakan" value="{{ $value('semakan', $phase?->semakan ?? 0) }}">
<p class="small">Tahun Kitaran ialah tahun anggaran belanjawan. Tempoh Fasa boleh bermula lebih awal dan merentasi tahun.</p>
<div class="form-grid">
<div><label for="phase-kind">Jenis Fasa <span class="required">*</span></label>@if($phase)<p>{{ $phase->label() }}</p><input type="hidden" name="jenis" value="{{ $phase->jenis }}">@else<select id="phase-kind" name="jenis" required><option value="">Pilih Jenis Fasa</option><option value="bidaan_awal" @selected($value('jenis')==='bidaan_awal')>Bidaan Awal</option><option value="pindaan" @selected($value('jenis')==='pindaan')>Pindaan — nombor ditentukan secara automatik</option></select>@endif</div>
<div><label for="phase-scope">Skop Pembukaan <span class="required">*</span></label><select id="phase-scope" name="jenis_skop" required><option value="">Pilih Skop Pembukaan</option>@foreach($scopeTypes as $key=>$label)<option value="{{ $key }}" @selected($selectedScope===$key)>{{ $label }}</option>@endforeach</select></div>
<div><label for="phase-start">Tarikh Mula <span class="required">*</span></label><input id="phase-start" name="tarikh_mula" type="date" value="{{ $value('tarikh_mula',$phase?->tarikh_mula->toDateString() ?? '') }}" required></div>
<div><label for="phase-end">Tarikh Tamat <span class="required">*</span></label><input id="phase-end" name="tarikh_tamat" type="date" value="{{ $value('tarikh_tamat',$phase?->tarikh_tamat->toDateString() ?? '') }}" required></div>
</div>
<p class="notice">{{ $phase ? 'Pinda mengubah tarikh dan skop. Jenis, nombor Pindaan dan status pembukaan dikekalkan.' : 'Fasa baharu bermula Ditutup. Gunakan tindakan Buka selepas maklumat Fasa disimpan.' }}</p>
<div class="phase-scope-panel" data-scope-mode="global" @if($selectedScope!=='global') hidden @endif><p class="small">Skop meliputi semua PTJ dan Aktiviti Kewangan.</p></div>
<fieldset class="phase-scope-panel" data-scope-mode="ptj" @if($selectedScope!=='ptj') hidden disabled @endif><legend>PTJ Terpilih</legend><input type="search" data-filter-checklist="phase-ptjs" aria-label="Cari PTJ skop" placeholder="Cari PTJ"><div class="scope-checklist" id="phase-ptjs">@foreach($ptjs as $ptj)<label class="check-label"><input type="checkbox" name="ptj_ids[]" value="{{ $ptj->id }}" @checked(in_array($ptj->id,$selectedPtjs))>{{ $ptj->nama }}</label>@endforeach</div><p class="small scope-selection-count"></p></fieldset>
<fieldset class="phase-scope-panel" data-scope-mode="aktiviti" @if($selectedScope!=='aktiviti') hidden disabled @endif><legend>Aktiviti Kewangan Terpilih</legend><div class="scope-checklist">@foreach($activities as $activity)<label class="check-label"><input type="checkbox" name="aktiviti_ids[]" value="{{ $activity->id }}" @checked(in_array($activity->id,$selectedActivities))>{{ $activity->nama }}</label>@endforeach</div><p class="small scope-selection-count"></p></fieldset>
<fieldset class="phase-scope-panel" data-scope-mode="gabungan" @if($selectedScope!=='gabungan') hidden disabled @endif><legend>Kombinasi PTJ & Aktiviti Kewangan</legend><p class="small">Setiap baris ialah satu pasangan khusus; pilihan tidak digabung silang.</p><div id="phase-pairs">@foreach($pairs as $index=>$row)@include('abm.cycles.pair-row')@endforeach</div><button class="button add-pair" type="button"><x-abm-icon name="plus"/>Tambah Kombinasi</button><p class="small" id="pair-count"></p><template id="pair-template">@include('abm.cycles.pair-row',['index'=>'NEWPAIR','row'=>[]])</template></fieldset>
@if($phase)<div class="section"><label for="phase-reason">Alasan perubahan <span class="required">*</span></label><textarea id="phase-reason" name="reason" rows="2" maxlength="1000" required>{{ $value('reason') }}</textarea></div>@endif
<div class="form-actions"><button class="button primary" type="submit"><x-abm-icon name="save"/>Simpan Fasa</button><a class="button" href="{{ route('cycles.index',['tahun'=>$cycle->tahun]) }}">Batal</a></div>
</form>
@endsection
