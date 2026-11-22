@extends('abm.layout')
@section('title', 'Cadangan Item Baharu')
@section('content')
<div class="page-heading">
    <div><h1>Cadangan Item Baharu</h1><p class="intro">OS{{ $os->kod_objek }} · {{ $os->butiran_perbelanjaan }} · {{ $bid->ptj->nama }}</p></div>
    <a class="button" href="{{ route('estimates.editor', [$bid->id, $estimate->id]) }}">Kembali ke Jadual</a>
</div>
@if($errors->any())
    <ul class="notice error" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
@endif
<form class="panel" method="post" action="{{ route('estimates.item-proposals.store', [$bid->id, $estimate->id]) }}">
    @csrf
    <div class="form-grid">
        <label>{{ $os->kod_objek === '26000' ? 'Subkategori Bukan Pukal' : 'Kategori' }}
            <select name="kategori" required><option value="">Pilih kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(old('kategori') === $category)>{{ $category }}</option>
                @endforeach
            </select>
        </label>
        <label>Nama Item<input name="nama" maxlength="255" required value="{{ old('nama') }}"></label>
        @if($os->kod_objek === '26000')
            <label>Unit<input name="unit" maxlength="40" required value="{{ old('unit') }}"></label>
        @endif
        <label>Kadar Cadangan (RM)<input name="kadar" inputmode="decimal" maxlength="19" required value="{{ old('kadar') }}"></label>
        <label>Justifikasi<textarea name="justifikasi" maxlength="2000" required>{{ old('justifikasi') }}</textarea></label>
        @if($needsReason)<label>Alasan Pintasan Superadmin<textarea name="reason" maxlength="1000" required>{{ old('reason') }}</textarea></label>@endif
    </div>
    <p class="small">Item yang menunggu semakan boleh digunakan oleh anda dalam Draf Anggaran. Gantikan atau keluarkan item yang ditolak sebelum penghantaran.</p>
    <button class="button primary" type="submit">Hantar Cadangan</button>
</form>
@if($proposals->total())
    <section class="panel" aria-labelledby="cadangan-saya">
        <h2 id="cadangan-saya">Cadangan Saya</h2>
        @foreach($proposals as $item)
            <article>
                <h3>{{ $item->nama }}</h3>
                <p>{{ \App\Models\CadanganItemAnggaran::STATUS[$item->status] }} · {{ $item->kategori }}</p>
                @if($item->kadar_diluluskan !== null)<p>Kadar Diluluskan RM{{ $item->kadar_diluluskan }}</p>@endif
                @if($item->catatan_keputusan)<p>{{ $item->catatan_keputusan }}</p>@endif
            </article>
        @endforeach
        {{ $proposals->links() }}
    </section>
@endif
@endsection
