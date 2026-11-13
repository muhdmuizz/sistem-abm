@php($isParent = $field === 'activity')
@php($prefix = $isParent ? 'activity' : "settings[$osId]")
@php($configured = $useOld ? in_array(data_get($oldValues, 'configured', 0), [1, '1', true], true) : $setting['configured'])
@php($allowed = $useOld ? in_array(data_get($oldValues, 'allowed', 0), [1, '1', true], true) : $setting['allowed'] === true)
<div class="permission-setting" data-permission-setting data-parent-setting="{{ $isParent ? 'yes' : 'no' }}" data-inherited="{{ $setting['inherited'] === null ? 'unset' : ($setting['inherited'] ? 'yes' : 'no') }}">
@if($target['kind'] === 'kategori' && $isParent)
<input type="hidden" name="{{ $prefix }}[configured]" value="1">
@else
<input type="hidden" name="{{ $prefix }}[configured]" value="0">
<label class="check-label"><input type="checkbox" class="permission-configured" name="{{ $prefix }}[configured]" value="1" @checked($configured)>{{ $target['kind'] === 'ptj' ? 'Tetapan khusus' : 'Tetapkan OS' }}</label>
@endif
<input type="hidden" name="{{ $prefix }}[allowed]" value="0">
<label class="check-label"><input type="checkbox" class="permission-allowed" name="{{ $prefix }}[allowed]" value="1" @checked($allowed) @disabled(! $configured && !($target['kind'] === 'kategori' && $isParent))>Benarkan{{ $isParent ? ' Aktiviti Kewangan' : '' }}</label>
<span class="small permission-source">@if($target['kind'] === 'ptj')Lalai kategori: {{ $setting['inherited'] === null ? 'Belum ditetapkan' : ($setting['inherited'] ? 'Benarkan' : 'Sekat') }}@else{{ $setting['configured'] ? 'Nilai kategori disimpan' : 'Belum ditetapkan' }}@endif</span>
</div>
