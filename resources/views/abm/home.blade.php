@extends('abm.layout')
@section('title', 'Utama')
@section('content')
<p class="eyebrow">Ruang kerja</p><h1>Utama</h1>
<p class="intro">Selamat datang, {{ auth()->user()->name }}.</p>
<section class="section"><h2>Penyediaan anggaran</h2><p>Penyediaan Bidaan dan Anggaran belum dibuka.</p></section>
@if (auth()->user()->penugasanPeranan()->where('peranan', 'superadmin')->exists())
<section class="section"><h2>Rujukan pentadbiran</h2><p>Semak data organisasi dan katalog OS yang digunakan oleh sistem.</p>
<div class="link-list">
@foreach (['ptj' => ['PTJ', 'Senarai pusat tanggungjawab'], 'aktiviti' => ['Aktiviti Kewangan', 'Aktiviti dan bilangan kod rujukan'], 'tempat-bertugas' => ['Tempat Bertugas', 'Sektor dan sekolah mengikut PTJ'], 'os' => ['OS', 'Kod objek, butiran dan status rujukan']] as $key => [$label, $description])
<a href="{{ route('references', $key) }}"><span>{{ $label }}<small>{{ $description }}</small></span><span aria-hidden="true">→</span></a>
@endforeach
</div></section>
@endif
@endsection
