@extends('abm.layout')
@section('title', $title)
@section('content')
<p class="eyebrow">Pentadbiran/ Rujukan</p><h1>{{ $title }}</h1>
<p class="intro">{{ number_format($records->total()) }} rekod rujukan. Paparan baca sahaja.</p>
@if($section === 'os')<p class="notice">Status rujukan tidak memberikan kebenaran akses OS. POL dan AMal dikecualikan daripada pelaksanaan.</p>@endif
<div class="data-table"><table><caption class="sr-only">Senarai {{ $title }}</caption><thead><tr>@foreach($headers as $header)<th scope="col">{{ $header }}</th>@endforeach</tr></thead><tbody>
@forelse($rows as $row)<tr>@foreach($row as $cell)<td data-label="{{ $headers[$loop->index] }}">{{ $cell }}</td>@endforeach</tr>
@empty<tr><td class="table-empty" colspan="{{ count($headers) }}">Tiada rekod rujukan tersedia.</td></tr>@endforelse
</tbody></table></div>
@if($records->hasPages())<nav class="pagination" aria-label="Navigasi halaman">
@if($records->onFirstPage())<span>Sebelumnya</span>@else<a href="{{ $records->previousPageUrl() }}">Sebelumnya</a>@endif
<span>Halaman {{ $records->currentPage() }} daripada {{ $records->lastPage() }}</span>
@if($records->hasMorePages())<a href="{{ $records->nextPageUrl() }}">Seterusnya</a>@else<span>Seterusnya</span>@endif
</nav>@endif
@endsection
