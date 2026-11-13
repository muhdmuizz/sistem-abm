@php($prefix = "roles[$key][rows][$index]")
<div class="assignment-row" data-assignment-row>
<label class="check-label"><input type="checkbox" name="{{ $prefix }}[selected]" value="1" @checked($row['selected'] ?? false)>Gunakan penugasan ini</label>
<div class="form-grid">
@if(in_array($key, ['penyedia_bidaan','penyedia_anggaran','pemantau_bidaan']))
<div><label for="scope-ptj-{{ $key }}-{{ $index }}">PTJ</label><input type="search" class="selector-search" data-search-select="scope-ptj-{{ $key }}-{{ $index }}" aria-label="Cari pilihan PTJ penugasan" placeholder="Cari PTJ"><select id="scope-ptj-{{ $key }}-{{ $index }}" name="{{ $prefix }}[ptj_id]"><option value="">{{ $key === 'pemantau_bidaan' ? 'Semua PTJ' : 'Pilih PTJ' }}</option>@foreach($ptjs as $ptj)<option value="{{ $ptj->id }}" @selected(($row['ptj_id'] ?? '') == $ptj->id)>{{ $ptj->nama }}</option>@endforeach</select></div>
@endif
<div><label for="scope-activity-{{ $key }}-{{ $index }}">Aktiviti Kewangan</label><select id="scope-activity-{{ $key }}-{{ $index }}" name="{{ $prefix }}[aktiviti_kewangan_id]"><option value="">{{ $key === 'pemantau_bidaan' ? 'Semua Aktiviti Kewangan' : 'Pilih Aktiviti Kewangan' }}</option>@foreach($activities as $activity)<option value="{{ $activity->id }}" @selected(($row['aktiviti_kewangan_id'] ?? '') == $activity->id)>{{ $activity->nama }}</option>@endforeach</select></div>
@if($key === 'penyedia_anggaran')
<div><label for="scope-workplace-{{ $key }}-{{ $index }}">Skop Tempat Bertugas</label><select id="scope-workplace-{{ $key }}-{{ $index }}" name="{{ $prefix }}[tempat_bertugas_id]" data-own-workplace><option value="">Semua di bawah PTJ penugasan</option>@foreach($workplaces as $place)<option value="{{ $place->id }}" data-parent="{{ $place->ptj_id }}" @selected(($row['tempat_bertugas_id'] ?? '') == $place->id)>{{ $place->nama }} — {{ $place->ptj->nama }}</option>@endforeach</select><p class="small">Skop khusus hanya untuk Tempat Bertugas pengguna sendiri.</p></div>
@endif
</div>
<button class="button danger subtle remove-assignment" type="button" aria-label="Buang baris penugasan"><x-abm-icon name="trash"/>Buang Baris</button>
</div>
