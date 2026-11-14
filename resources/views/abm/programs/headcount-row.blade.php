@php
$row = is_array($row) ? $row : [];
$cell = fn($key) => is_scalar($row[$key] ?? null) ? (string)$row[$key] : '';
$prefix = 'maklumat['.$group.']['.$index.']';
$identity = $group.'-'.$index;
@endphp
<div class="program-person-row" data-person-row>
<label for="{{ $identity }}-category">Kategori<select id="{{ $identity }}-category" name="{{ $prefix }}[kategori]" data-person-category required><option value="">Pilih kategori</option>@foreach($group === 'jemputan' ? ['Pegawai','AKP'] : ['Pegawai','Guru','AKP','Murid Rendah','Murid Menengah','Lain-lain'] as $choice)<option @selected($cell('kategori')===$choice)>{{ $choice }}</option>@endforeach</select></label>
<label for="{{ $identity }}-class"><span data-class-label>Gred/ Tahun/ Tingkatan</span><input id="{{ $identity }}-class" name="{{ $prefix }}[klasifikasi]" type="number" step="1" min="1" value="{{ $cell('klasifikasi') }}" data-person-class><span class="small" data-class-help>Ikut kategori yang dipilih.</span></label>
<label for="{{ $identity }}-note" data-note-label>Catatan (Lain-lain)<input id="{{ $identity }}-note" name="{{ $prefix }}[catatan]" value="{{ $cell('catatan') }}" data-person-note></label>
<label for="{{ $identity }}-count">Bilangan<input id="{{ $identity }}-count" name="{{ $prefix }}[bilangan]" type="number" min="1" step="1" value="{{ $cell('bilangan') }}" data-person-count required></label>
<button type="button" class="button program-remove" data-remove-person aria-label="Padam baris {{ $group === 'jemputan' ? 'Jemputan/ Urus Setia' : 'Peserta' }}">Padam</button>
</div>
