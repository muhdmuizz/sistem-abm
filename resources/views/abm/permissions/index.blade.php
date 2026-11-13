@extends('abm.layout')
@section('title', 'Pengurusan Aktiviti Kewangan')
@section('content')
<p class="eyebrow">Pentadbiran</p><h1>Pengurusan Aktiviti Kewangan</h1>
<p class="intro">Tetapkan akses Aktiviti Kewangan dan OS melalui lalai kategori atau tetapan khusus setiap PTJ.</p>
<section class="section"><h2>Lalai Kategori PTJ</h2><div class="category-links">
@foreach($categories as $key => $label)<a class="button" href="{{ route('permissions.edit', ['kategori', $key]) }}">{{ $label }}<x-abm-icon name="chevron"/></a>@endforeach
</div></section>
<section class="section"><h2>Tetapan Individu PTJ</h2>
<form class="search-form" method="get"><label for="permission-search">Cari nama atau kod PTJ</label><div class="inline-controls"><input id="permission-search" type="search" name="q" value="{{ $search }}" maxlength="255"><button class="button" type="submit">Cari</button><a class="button subtle" href="{{ route('permissions.index') }}">Set Semula</a></div></form>
<p class="small">{{ $ptjs->total() }} PTJ</p>
<div class="data-table"><table><thead><tr><th>PTJ</th><th>Kategori</th><th>Tetapan</th><th>Tindakan</th></tr></thead><tbody>
@forelse($ptjs as $ptj)<tr><td data-label="PTJ"><div>{{ $ptj->nama }}<br><span class="small">{{ $ptj->kod_ptj }}</span></div></td><td data-label="Kategori">{{ $ptj->kategori }}</td>
<td data-label="Tetapan"><details class="scope-details"><summary>{{ ($overrides[$ptj->id] ?? collect())->count() }} tetapan khusus</summary><p class="small">Setiap tetapan tanpa nilai khusus mengikut lalai {{ $ptj->kategori }}. Aktiviti dan setiap OS mewarisi secara berasingan.</p></details></td>
<td data-label="Tindakan"><a class="button icon-button" href="{{ route('permissions.edit', ['ptj', $ptj->id]) }}" aria-label="Urus akses {{ $ptj->nama }}" title="Urus akses"><x-abm-icon name="pencil"/></a></td></tr>
@empty<tr><td colspan="4">Tiada PTJ ditemui.</td></tr>@endforelse
</tbody></table></div>
<div class="pagination"><span>Halaman {{ $ptjs->currentPage() }} daripada {{ $ptjs->lastPage() }}</span><div>@if($ptjs->previousPageUrl())<a href="{{ $ptjs->previousPageUrl() }}">Sebelumnya</a>@endif @if($ptjs->nextPageUrl())<a href="{{ $ptjs->nextPageUrl() }}">Seterusnya</a>@endif</div></div>
</section>
@endsection
