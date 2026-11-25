@extends('abm.layout')
@section('title', 'Gaji & Emolumen')
@section('cycle-context', 'Kitaran ABM '.$year)
@section('content')
<link rel="stylesheet" href="{{ asset('anggaran-grid.css') }}">
<script src="{{ asset('abm-money.js') }}" defer></script>
<script src="{{ asset('gaji-preview.js') }}" defer></script>

<div class="page-heading">
    <div>
        <h1>Gaji &amp; Emolumen</h1>
        <p class="intro">Pratonton · {{ $bid->ptj->nama }} · {{ $bid->aktivitiKewangan->nama }} · Kitaran {{ $year }}</p>
    </div>
    <a class="button" href="{{ route('bids.show', $bid->id) }}">Kembali ke Aktiviti Kewangan</a>
</div>
<p class="notice">Gunakan data contoh sahaja. Draf pratonton disimpan secara automatik untuk akaun anda.</p>

<div data-gaji-preview data-year="{{ $year }}" data-salary-rates="{{ json_encode($salaryRates, JSON_THROW_ON_ERROR) }}"
    data-allowance-rates="{{ json_encode($allowanceRows, JSON_THROW_ON_ERROR) }}"
    data-workplace-district="{{ $workplaceDistrict }}"
    data-draft="{{ json_encode($draft, JSON_THROW_ON_ERROR) }}" data-draft-revision="{{ $draftVersion }}"
    data-editor-account="{{ auth()->id() }}" data-save-url="{{ route('gaji.preview-save', $bid->id) }}" data-csrf="{{ csrf_token() }}">
    <div class="grid-toolbar">
        <p class="small" role="status" data-save-status></p>
        <button class="button" type="button" data-save-retry hidden>Cuba Simpan Semula</button>
    </div>
    <div class="notice" data-editor-lock role="status">Menyemak akses penyuntingan…</div>
    <button type="button" class="button" data-editor-reload hidden>Muat Semula</button>
    @if($workplaces !== [])
        <div class="action-field" data-gaji-workplace-field hidden inert>
            <label for="gaji-workplace">Tempat Bertugas</label>
            <select id="gaji-workplace" data-gaji-workplace>
                <option value="">Tambah Tempat Bertugas di Rumusan</option>
            </select>
        </div>
    @endif
    <div class="grid-toolbar" role="tablist" aria-label="Gaji dan Emolumen">
        <button type="button" class="button primary" id="gaji-summary-tab" role="tab" aria-selected="true" aria-controls="gaji-summary-panel">Rumusan</button>
        <button type="button" class="button" id="gaji-jik-tab" role="tab" aria-selected="false" aria-controls="gaji-jik-panel" tabindex="-1">JIK</button>
        <button type="button" class="button" id="gaji-wizard-tab" role="tab" aria-selected="false" aria-controls="gaji-wizard-panel" tabindex="-1">Terperinci</button>
    </div>
    <section class="section" id="gaji-summary-panel" role="tabpanel" inert aria-labelledby="gaji-summary-tab" tabindex="0">
        <div class="anggaran-grid-scroll" tabindex="0" role="region" aria-label="Rumusan Gaji dan Emolumen">
            <table class="anggaran-grid gaji-summary-grid">
                <colgroup><col style="width: 52px"><col><col span="7" style="width: 125px"></colgroup>
                <thead>
                    <tr>
                        @foreach (['#', 'Tempat Bertugas', 'Bil. Perjawatan', 'Bil. Pengisian', 'Bil. Kekosongan', 'OS11000 (RM)', 'OS12000 (RM)', 'OS13000 (RM)', 'Jumlah (RM)'] as $heading)
                            <th scope="col">{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody data-summary-rows>
                    @foreach ($workplaces === [] ? ['default' => ($bid->ptj->kategori === 'JPNT' ? 'Pejabat JPNT' : $bid->ptj->nama)] : [] as $id => $name)
                        <tr data-payroll-summary="{{ $id }}">
                            <td>{{ $loop->iteration }}</td>
                            <th scope="row">{{ $name }}</th>
                            @foreach (['0', '0', '0', '0.00', '0.00', '0.00', '0.00'] as $value)
                                <td class="grid-numeric" data-summary-value>{{ $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
                @if($workplaces !== [])
                    <tbody>
                        <tr>
                            <td class="gaji-workplace-picker-cell" colspan="9">
                                <details class="gaji-workplace-picker" data-workplace-picker>
                                    <summary>Tambah Tempat Bertugas</summary>
                                    <div class="gaji-workplace-picker-content">
                                        <label class="gaji-workplace-search">
                                            Cari Tempat Bertugas
                                            <input type="search" data-workplace-search autocomplete="off">
                                        </label>
                                        <p class="small" data-workplace-empty role="status" hidden></p>
                                        <div class="gaji-workplace-choices" data-workplace-choices>
                                            <label class="gaji-workplace-select-all">
                                                <input type="checkbox" data-workplace-select-all>
                                                <span data-workplace-select-all-label>Pilih semua hasil carian</span>
                                            </label>
                                            <fieldset class="gaji-workplace-options" data-available-workplace>
                                                <legend class="sr-only">Tempat Bertugas yang tersedia</legend>
                                                @foreach($workplaces as $id => $name)
                                                    <label>
                                                        <input type="checkbox" value="{{ $id }}" data-workplace-name="{{ $name }}">
                                                        <span>{{ $name }}</span>
                                                    </label>
                                                @endforeach
                                            </fieldset>
                                        </div>
                                        <button type="button" class="button primary" data-include-workplace disabled>Tambah Tempat Bertugas</button>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    </tbody>
                @endif
                <tfoot>
                    <tr data-payroll-summary-total>
                        <th scope="row" colspan="2">Jumlah</th>
                        @foreach (['0', '0', '0', '0.00', '0.00', '0.00', '0.00'] as $value)
                            <td class="grid-numeric" data-summary-value>{{ $value }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>
    <section class="section" id="gaji-jik-panel" role="tabpanel" inert aria-labelledby="gaji-jik-tab" tabindex="0" hidden>
        <h2 id="jik-title">Jawatan, Isi, Kosong (JIK)</h2>
        <dl class="jik-totals" aria-label="Jumlah JIK" aria-live="polite">
            <div><dt>Perjawatan</dt><dd data-jik-total="establishment">0</dd></div>
            <div><dt>Pengisian</dt><dd data-jik-total="filled">0</dd></div>
            <div><dt>Kekosongan</dt><dd data-jik-total="vacant">0</dd></div>
        </dl>
        <form data-jik>
            <div class="anggaran-grid-scroll" tabindex="0" role="region" aria-label="Jadual JIK">
                <table class="anggaran-grid jik-grid">
                    <colgroup>
                        <col style="width: 48px">
                        <col>
                        <col span="3" class="jik-count-column">
                        <col class="jik-action-column">
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col" aria-label="Susunan"></th>
                            <th scope="col">Jawatan</th>
                            <th scope="col" class="grid-numeric">Bil. Perjawatan</th>
                            <th scope="col" class="grid-numeric">Bil. Pengisian</th>
                            <th scope="col" class="grid-numeric">Bil. Kekosongan</th>
                            <th scope="col">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody data-jik-rows></tbody>
                </table>
            </div>
            <p class="empty-state" data-jik-empty hidden>Belum ada Jawatan. Klik Tambah Jawatan untuk mula.</p>
            <div class="grid-toolbar">
                <button type="button" class="button" data-jik-add>Tambah Jawatan</button>
                <button class="button primary" type="submit">Jana Baris Wizard</button>
            </div>
            <p class="notice error" role="alert" data-jik-error hidden></p>
        </form>
    </section>

    <section class="section" id="gaji-wizard-panel" role="tabpanel" inert aria-labelledby="gaji-wizard-tab" tabindex="0" hidden>
        <div class="gaji-wizard-heading">
            <h2>Butiran Gaji &amp; Emolumen</h2>
            <label>Ketua Jabatan
                <select data-ketua><option value="">Pilih pegawai</option></select>
            </label>
        </div>
        <div class="grid-toolbar" data-incomplete-rows hidden>
            <p role="status" data-incomplete-count></p>
            <label>Semak baris
                <select data-incomplete-picker></select>
            </label>
        </div>
        <div class="anggaran-grid-scroll" tabindex="0" role="region" aria-label="Jadual Gaji dan Emolumen">
            <table class="anggaran-grid gaji-wizard-grid">
                <colgroup>
                    @foreach (['number', 'position', 'officer', 'grade'] as $column)
                        <col style="width: var(--gaji-{{ $column }}-width)">
                    @endforeach
                    <col style="width: 105px">
                </colgroup>
                <colgroup><col class="gaji-amount-column"><col style="width: 85px"><col class="gaji-amount-column"></colgroup>
                <colgroup><col class="gaji-amount-column"></colgroup>
                <colgroup><col class="gaji-amount-column"><col style="width: 85px"><col class="gaji-amount-column"></colgroup>
                <colgroup><col class="gaji-amount-column"></colgroup>
                <colgroup><col span="4" class="gaji-amount-column"><col style="width: 260px"><col class="gaji-amount-column"><col style="width: 260px"><col class="gaji-amount-column"><col style="width: 300px"><col class="gaji-amount-column"></colgroup>
                <colgroup><col class="gaji-amount-column"></colgroup>
                <colgroup><col span="2" class="gaji-amount-column"></colgroup>
                <colgroup><col class="gaji-amount-column"></colgroup>
                <thead>
                    <tr>
                        @foreach (['number' => '#', 'position' => 'Jawatan', 'officer' => 'Nama Pegawai', 'grade' => 'Gred'] as $column => $label)
                            <th scope="col" rowspan="2" class="gaji-frozen gaji-frozen-{{ $column }}">{{ $label }}</th>
                        @endforeach
                        <th scope="col" rowspan="2">TPG {{ $year }}</th>
                        <th scope="colgroup" colspan="3" class="gaji-group-start">Gaji Asal</th>
                        <th scope="col" rowspan="2" class="grid-numeric gaji-group-start">KGT (RM)</th>
                        <th scope="colgroup" colspan="3" class="gaji-group-start">Gaji Baharu</th>
                        <th scope="col" rowspan="2" class="grid-numeric gaji-group-start gaji-total">Jumlah OS11000 (RM)</th>
                        <th scope="colgroup" colspan="10" class="gaji-group-start">OS12000 — Elaun/ Imbuhan Tetap</th>
                        <th scope="col" rowspan="2" class="grid-numeric gaji-group-start gaji-total">Jumlah OS12000 (RM)</th>
                        <th scope="colgroup" colspan="2" class="gaji-group-start">OS13000 — Sumbangan Berkanun</th>
                        <th scope="col" rowspan="2" class="grid-numeric gaji-group-start gaji-total">Jumlah Setahun (RM)</th>
                    </tr>
                    <tr>
                        @foreach (['Gaji Asal', 'Gaji Baharu'] as $salaryGroup)
                            <th scope="col" class="grid-numeric gaji-group-start" aria-label="{{ $salaryGroup }} — Kadar (RM)">Kadar (RM)</th>
                            <th scope="col" class="grid-numeric" aria-label="{{ $salaryGroup }} — Bil. Bulan">Bil. Bulan</th>
                            <th scope="col" class="grid-numeric" aria-label="{{ $salaryGroup }} — Jumlah Kecil (RM)">Jumlah Kecil (RM)</th>
                        @endforeach
                        @foreach (['ITJU', 'ITK @ ITKA', 'ITP @ BIPW', 'BISH @ BIW'] as $allowance)
                            <th scope="col" class="grid-numeric gaji-group-start">{{ $allowance }}/ Bulan</th>
                            <th scope="col" class="grid-numeric">{{ $allowance }}/ Tahun</th>
                        @endforeach
                        <th scope="col" class="grid-numeric gaji-group-start">Elaun-elaun Lain/ Bulan</th>
                        <th scope="col" class="grid-numeric">Elaun-elaun Lain/ Tahun</th>
                        <th scope="col" class="grid-numeric gaji-group-start">OS13000/ Bulan</th>
                        <th scope="col" class="grid-numeric gaji-total">OS13000/ Tahun</th>
                    </tr>
                </thead>
                <tbody data-wizard-rows></tbody>
            </table>
        </div>
        <p class="empty-state" data-wizard-empty hidden>Belum ada butiran. Jana baris daripada tab JIK.</p>
        <p class="small">Bilangan bulan: 12 untuk Isi dan 3 untuk Kosong. Gaji OS11000 bagi jawatan Kosong menggunakan Gaji Median.</p>
    </section>

    <template data-summary-workplace-template>
        <tr data-payroll-summary>
            <td data-summary-number></td>
            <th scope="row">
                <div class="gaji-workplace-row">
                    <div class="gaji-workplace-order">
                        <button type="button" class="gaji-drag-handle" data-gaji-drag aria-label="Susun Tempat Bertugas" aria-keyshortcuts="ArrowUp ArrowDown" title="Seret atau gunakan kekunci anak panah atas dan bawah">⠿</button>
                        <span data-summary-name></span>
                    </div>
                    <button type="button" class="button danger" data-exclude-workplace>Keluarkan</button>
                </div>
            </th>
            @foreach (range(1, 7) as $column)
                <td class="grid-numeric" data-summary-value></td>
            @endforeach
        </tr>
    </template>
    <datalist id="jik-jawatan-options">
        @foreach (array_keys($salaryRates) as $namaJawatan)
            <option value="{{ $namaJawatan }}"></option>
        @endforeach
    </datalist>
    <template data-jik-template><tr>
        <td><button type="button" class="gaji-drag-handle" data-gaji-drag aria-label="Susun baris Jawatan" aria-keyshortcuts="ArrowUp ArrowDown" title="Seret atau gunakan kekunci anak panah atas dan bawah">⠿</button></td>
        <td>
            <input aria-label="Jawatan" role="combobox" aria-autocomplete="list" aria-expanded="false"
                placeholder="Pilih Jawatan" autocomplete="off" required>
            <ul class="jik-jawatan-suggestions" role="listbox" aria-label="Cadangan Jawatan" hidden></ul>
        </td>
        <td><input aria-label="Bil. Perjawatan" type="number" min="0" max="100" value="0" required></td>
        <td><input aria-label="Bil. Pengisian" type="number" min="0" value="0" required></td>
        <td class="grid-numeric"><output aria-label="Bil. Kekosongan">0</output></td>
        <td><button type="button" class="button danger" data-jik-remove>Padam</button></td>
    </tr></template>
    <template data-other-allowance-template>
        <div class="gaji-other-item" data-other-item>
            <label>Nama Elaun<input data-other-name maxlength="120"></label>
            <label>Kadar/ Bulan (RM)<input data-other-monthly inputmode="decimal" maxlength="16" placeholder="0.00"></label>
            <div class="gaji-other-item-footer">
                <span>Jumlah/ Tahun: <output data-other-item-annual>—</output></span>
                <button type="button" class="button danger" data-other-remove>Padam Elaun</button>
            </div>
        </div>
    </template>
    <template data-wizard-template><tr>
        <th scope="row" class="gaji-frozen gaji-frozen-number" data-row-number></th>
        <td class="gaji-frozen gaji-frozen-position" data-position></td>
        <td class="gaji-frozen gaji-frozen-officer">
            <input aria-label="Nama Pegawai" maxlength="100">
            <strong class="gaji-position-status" data-vacancy hidden>Kosong</strong>
        </td>
        <td class="gaji-frozen gaji-frozen-grade"><select aria-label="Gred SSPA"><option value="">Pilih gred</option></select></td>
        <td><select aria-label="Tarikh Pergerakan Gaji"><option value="">Pilih TPG</option><option value="1">01/01</option><option value="4">01/04</option><option value="7">01/07</option><option value="10">01/10</option></select></td>
        <td class="gaji-group-start"><input aria-label="Gaji Asal (RM)" inputmode="decimal" maxlength="16" placeholder="0.00"></td>
        @foreach (['asal-months', 'asal-subtotal', 'kgt', 'baharu', 'baharu-months', 'baharu-subtotal', 'total'] as $field)
            <td @class(['grid-numeric', 'gaji-group-start' => in_array($field, ['kgt', 'baharu', 'total'], true), 'gaji-total' => $field === 'total']) data-value="{{ $field }}">—</td>
        @endforeach
        @foreach (['ITJU', 'ITK @ ITKA', 'ITP @ BIPW', 'BISH @ BIW'] as $allowance)
            <td class="gaji-group-start">
                @if ($allowance === 'ITP @ BIPW')
                    <div class="gaji-housing">
                        <select data-housing-allowance aria-label="Jenis dan kawasan imbuhan perumahan">
                            <option value="">Pilih imbuhan</option>
                            <option value="ITP">ITP</option>
                            <option value="BIPW">BIPW</option>
                        </select>
                        <output aria-label="Kadar perumahan sebulan (RM)" data-housing-rate>—</output>
                    </div>
                @elseif ($allowance === 'BISH @ BIW')
                    <div class="gaji-housing">
                        <select data-living-allowance aria-label="Jenis dan kawasan imbuhan sara hidup">
                            <option value="">Pilih imbuhan</option>
                            <option value="BISH">BISH</option>
                            <option value="BIW">BIW</option>
                        </select>
                        <output aria-label="Kadar imbuhan sara hidup sebulan (RM)" data-living-rate>—</output>
                    </div>
                @endif
                <input @if (in_array($allowance, ['ITP @ BIPW', 'BISH @ BIW'], true)) hidden @endif data-allowance-monthly aria-label="{{ $allowance }} sebulan (RM)" inputmode="decimal" maxlength="16" placeholder="0.00">
            </td>
            <td class="grid-numeric" data-annual>—</td>
        @endforeach
        <td class="gaji-group-start gaji-other-cell">
            <output class="grid-numeric" aria-label="Elaun-elaun Lain sebulan (RM)" data-other-month-total>—</output>
            <details data-other-allowances>
                <summary>Elaun-elaun Lain</summary>
                <div data-other-items></div>
                <button type="button" class="button" data-other-add>Tambah Elaun</button>
            </details>
        </td>
        <td class="gaji-other-year-cell grid-numeric">
            <output aria-label="Elaun-elaun Lain setahun (RM)" data-other-year-total>—</output>
        </td>
        <td class="grid-numeric gaji-group-start gaji-total" data-allowance-total>—</td>
        <td class="gaji-group-start">
            <input data-os13000-monthly aria-label="OS13000 sebulan (RM)" inputmode="decimal" maxlength="16" placeholder="0.00">
        </td>
        <td class="grid-numeric gaji-total" data-os13000-annual>—</td>
        <td class="grid-numeric gaji-group-start gaji-total" data-payroll-total>—</td>
    </tr></template>
</div>
@endsection
