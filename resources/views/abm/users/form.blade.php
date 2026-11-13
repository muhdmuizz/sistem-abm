@extends('abm.layout')
@section('title', $account->exists ? 'Pinda Pengguna' : 'Tambah Pengguna')
@section('content')
@php($roleLabels = ['superadmin'=>'Superadmin','ketua_bidaan'=>'Ketua Bidaan','penyelia_bidaan'=>'Penyelia Bidaan','penyedia_bidaan'=>'Penyedia Bidaan','penyedia_anggaran'=>'Penyedia Anggaran','pemantau_bidaan'=>'Pemantau Bidaan'])
@php($formRoles = session()->hasOldInput() ? old('roles', []) : $roles)
<div class="page-heading"><div><p class="eyebrow">Pengguna & Peranan</p><h1>{{ $account->exists ? 'Pinda Pengguna' : 'Tambah Pengguna' }}</h1></div><a class="button" href="{{ route('users.index') }}">Kembali</a></div>
@if(session('status'))<p class="notice" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="notice error" role="alert"><strong>Perubahan belum disimpan.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="post" action="{{ $account->exists ? route('users.update', $account) : route('users.store') }}" class="access-form" data-track-unsaved data-confirm="Simpan perubahan untuk {{ $account->exists ? $account->email : 'pengguna baharu' }}? Sesi ABM terdahulu pengguna ini akan ditamatkan.">
@csrf @if($account->exists) @method('PUT') @endif
<input type="hidden" name="access_revision" value="{{ old('access_revision', $account->access_revision ?? 0) }}">
<section class="form-section"><h2>Identiti & Organisasi</h2><div class="form-grid">
<div><label for="name">Nama <span class="required">*</span></label><input id="name" name="name" required maxlength="255" value="{{ old('name', $account->name) }}">@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
<div><label for="email">E-mel MyGovUC <span class="required">*</span></label><input id="email" name="email" type="email" required maxlength="255" value="{{ old('email', $account->email) }}">@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
<div><label for="identity-ptj">PTJ <span class="required">*</span></label><input type="search" class="selector-search" data-search-select="identity-ptj" aria-label="Cari PTJ pengguna" placeholder="Cari PTJ"><select id="identity-ptj" name="ptj_id" required><option value="">Pilih PTJ</option>@foreach($ptjs as $ptj)<option value="{{ $ptj->id }}" @selected(old('ptj_id', $account->ptj_id) == $ptj->id)>{{ $ptj->nama }}</option>@endforeach</select>@error('ptj_id')<p class="field-error">{{ $message }}</p>@enderror</div>
<div><label for="identity-workplace">Tempat Bertugas</label><input type="search" class="selector-search" data-search-select="identity-workplace" aria-label="Cari Tempat Bertugas pengguna" placeholder="Cari Tempat Bertugas"><select id="identity-workplace" name="tempat_bertugas_id"><option value="">Belum ditetapkan</option>@foreach($workplaces as $place)<option value="{{ $place->id }}" data-parent="{{ $place->ptj_id }}" @selected(old('tempat_bertugas_id', $account->tempat_bertugas_id) == $place->id)>{{ $place->nama }} — {{ $place->ptj->nama }}</option>@endforeach</select>@error('tempat_bertugas_id')<p class="field-error">{{ $message }}</p>@enderror</div>
</div>
<input type="hidden" name="is_active" value="0"><label class="check-label account-active"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $account->is_active))>Akaun Aktif</label>
<p class="small">Log masuk memerlukan akaun aktif, identiti Google yang sah dan sekurang-kurangnya satu penugasan. Tiada kata laluan ABM diperlukan.</p>
</section>
<section class="form-section"><h2>Peranan & Skop Penugasan</h2><p class="small">Tandakan peranan dan pilih setiap penugasan secara berasingan. Penugasan tidak mengubah kebenaran OS di peringkat PTJ.</p>
@foreach($roleCases as $role)
@php($key = $role->value)
@php($group = $formRoles[$key] ?? [])
<details class="role-group" data-role="{{ $key }}" @if($group['enabled'] ?? false) open @endif>
<summary><span>{{ $roleLabels[$key] }}</span><span class="small assignment-count"></span></summary>
<div class="role-content"><label class="check-label"><input type="checkbox" class="role-enabled" name="roles[{{ $key }}][enabled]" value="1" @checked($group['enabled'] ?? false)>Berikan peranan {{ $roleLabels[$key] }}</label>
@if($key === 'superadmin')
<p class="small">Seluruh sistem, semua PTJ dan Aktiviti Kewangan. Semua tindakan kekal diaudit.</p>
@else
@if(in_array($key, ['ketua_bidaan','penyelia_bidaan']))<p class="small">Semua PTJ dalam setiap Aktiviti Kewangan yang dipilih. Ketua Bidaan hanya untuk pengguna JPNT.</p>@endif
@if($key === 'pemantau_bidaan')<p class="small">Setiap baris: seluruh JPNT, satu PTJ untuk semua aktiviti, atau satu aktiviti untuk semua PTJ. Jangan pilih kedua-dua PTJ dan aktiviti dalam baris yang sama.</p>@endif
<div class="assignment-rows">@foreach($group['rows'] ?? [] as $index => $row)@include('abm.users.assignment-row')@endforeach</div>
<button class="button add-assignment" type="button"><x-abm-icon name="plus"/>Tambah Penugasan</button>
<template class="assignment-template">@include('abm.users.assignment-row', ['index' => 'NEWROW', 'row' => ['selected' => '1']])</template>
@endif
</div></details>
@endforeach
@error('roles')<p class="field-error">{{ $message }}</p>@enderror
</section>
<section class="form-section"><label for="reason">Alasan perubahan <span class="required">*</span></label><textarea id="reason" name="reason" required maxlength="1000" rows="3">{{ old('reason') }}</textarea>
<p class="small">Perubahan direkod dalam jejak audit. Menyimpan perubahan menamatkan sesi ABM terdahulu pengguna ini.</p>
<div class="form-actions"><button class="button primary" type="submit"><x-abm-icon name="save"/>Simpan</button><a class="button" href="{{ route('users.index') }}">Batal</a></div></section>
</form>
@if($account->exists)
<section class="form-section revoke-section"><h2>Sesi Pengguna</h2><p class="small">Tamatkan semua sesi ABM tanpa menyahaktifkan akaun atau melog keluar akaun Google.</p>
<form method="post" action="{{ route('users.revoke', $account) }}" data-confirm="Tamatkan semua sesi ABM untuk {{ $account->email }}?">@csrf<input type="hidden" name="access_revision" value="{{ $account->access_revision }}"><label for="revoke-reason">Alasan penamatan sesi <span class="required">*</span></label><input id="revoke-reason" name="reason" required maxlength="1000"><button class="button danger" type="submit"><x-abm-icon name="logout"/>Tamatkan Semua Sesi</button></form></section>
@endif
@endsection
