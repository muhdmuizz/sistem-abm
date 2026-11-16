@extends('abm.layout')
@section('title','Nyahaktifkan Pasangan Fotostat')
@section('content')
<link rel="stylesheet" href="{{ asset('fotostat-review.css') }}">
<div class="page-heading"><h1>Nyahaktifkan Pasangan Fotostat</h1><a class="button" href="{{ route('estimates.editor',[$bid->id,$rental->id]) }}">Kembali ke Sewaan</a></div>
<section class="panel fotostat-review"><p>Kedua-dua OS akan dinyahaktifkan bersama. Semua butiran sewaan, bacaan meter dan pautannya dikekalkan untuk pengaktifan semula.</p>
<ul><li>OS24000 Sewaan Mesin Fotostat dan Riso</li><li>OS29000 Bacaan Meter Mesin Fotostat</li></ul>
<form method="post" action="{{ route('fotostat.deactivate',$bid->id) }}">@csrf
<input type="hidden" name="semakan_bidaan" value="{{ $bid->semakan }}">
@if($needsReason)<label>Alasan pintasan Superadmin<textarea name="reason" required maxlength="1000"></textarea></label>@endif
<label><input type="checkbox" name="sahkan" value="1" required> Saya mengesahkan kedua-dua OS dinyahaktifkan dan draf dikekalkan.</label>
<button class="button primary" type="submit">Nyahaktifkan Pasangan</button></form></section>
@endsection
