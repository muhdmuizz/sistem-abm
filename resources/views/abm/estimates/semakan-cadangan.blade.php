@extends('abm.layout')
@section('title', 'Semakan Cadangan Item')
@section('content')
<div class="page-heading"><div><h1>Semakan Cadangan Item</h1></div></div>
@if(session('status'))<p class="notice success" role="status">{{ session('status') }}</p>@endif
@if($errors->any())
    <ul class="notice error" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
@endif
@forelse($proposals as $item)
    <article class="panel">
        <h2>{{ $item->nama }}</h2>
        <p>{{ \App\Models\CadanganItemAnggaran::STATUS[$item->status] }} · OS{{ $item->kod_os }} · {{ $item->kategori }}</p>
        <dl>
            <dt>Pemohon</dt><dd>{{ $item->pemohon->name }} · {{ $item->ptj->nama }}</dd>
            <dt>Kadar Cadangan</dt><dd><x-abm-money :amount="$item->kadar_cadangan" /> / {{ $item->unit }}</dd>
            <dt>Justifikasi</dt><dd>{{ $item->justifikasi }}</dd>
        </dl>
        @if($item->status === 'menunggu')
            <form method="post" action="{{ route('item-proposals.decide', $item->id) }}">
                @csrf
                <div class="form-grid">
                    <label>Kadar Diluluskan (RM)<input name="kadar" inputmode="decimal" maxlength="19" value="{{ $item->kadar_cadangan }}"></label>
                    <label>Catatan Keputusan<textarea name="catatan" maxlength="2000" required></textarea></label>
                </div>
                <button class="button primary" type="submit" name="status" value="diluluskan">Luluskan</button>
                <button class="button danger" type="submit" name="status" value="ditolak">Tolak</button>
            </form>
        @else
            @if($item->kadar_diluluskan !== null)<p>Kadar Diluluskan <x-abm-money :amount="$item->kadar_diluluskan" /></p>@endif
            <p>{{ $item->catatan_keputusan }}</p>
        @endif
    </article>
@empty
    <p class="empty-state">Tiada cadangan item.</p>
@endforelse
{{ $proposals->links() }}
@endsection
