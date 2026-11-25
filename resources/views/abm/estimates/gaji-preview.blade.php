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
<p class="notice">Gunakan data contoh sahaja. Nama, gred dan kadar dalam pratonton ini tidak disimpan.</p>

<div data-gaji-preview data-year="{{ $year }}">
    <div class="grid-toolbar" role="tablist" aria-label="Gaji dan Emolumen">
        <button type="button" class="button primary" id="gaji-jik-tab" role="tab" aria-selected="true" aria-controls="gaji-jik-panel">JIK</button>
        <button type="button" class="button" id="gaji-wizard-tab" role="tab" aria-selected="false" aria-controls="gaji-wizard-panel" tabindex="-1">Terperinci</button>
    </div>
    <section class="section" id="gaji-jik-panel" role="tabpanel" aria-labelledby="gaji-jik-tab" tabindex="0">
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
                        <col>
                        <col span="3" class="jik-count-column">
                        <col class="jik-action-column">
                    </colgroup>
                    <thead>
                        <tr>
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
            <div class="grid-toolbar">
                <button type="button" class="button" data-jik-add>Tambah Jawatan</button>
                <button class="button primary" type="submit">Jana Baris Wizard</button>
            </div>
            <p class="notice error" role="alert" data-jik-error hidden></p>
        </form>
    </section>

    <section class="section" id="gaji-wizard-panel" role="tabpanel" aria-labelledby="gaji-wizard-tab" tabindex="0" hidden>
        <div class="gaji-wizard-heading">
            <h2>Butiran Gaji &amp; Emolumen</h2>
            <label>Ketua Jabatan
                <select data-ketua><option value="">Pilih pegawai</option></select>
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
                <colgroup><col span="8" class="gaji-amount-column"><col style="width: 300px"><col class="gaji-amount-column"></colgroup>
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
                        <th scope="col" class="grid-numeric gaji-group-start">Elaun-elaun Lain / Bulan</th>
                        <th scope="col" class="grid-numeric">Elaun-elaun Lain / Tahun</th>
                        <th scope="col" class="grid-numeric gaji-group-start">OS13000 / Bulan</th>
                        <th scope="col" class="grid-numeric gaji-total">OS13000 / Tahun</th>
                    </tr>
                </thead>
                <tbody data-wizard-rows></tbody>
            </table>
        </div>
        <p class="small">Bilangan bulan: 12 untuk Isi dan 3 untuk Kosong. Gaji OS11000 bagi jawatan Kosong menggunakan Gaji Median.</p>
    </section>

    <datalist id="jik-jawatan-options">
        @foreach ($jawatan as $namaJawatan)
            <option value="{{ $namaJawatan }}"></option>
        @endforeach
    </datalist>
    <template data-jik-template><tr>
        <td>
            <input aria-label="Jawatan" role="combobox" aria-autocomplete="list" aria-expanded="false"
                placeholder="Taip nama Jawatan" autocomplete="off" required>
            <ul class="jik-jawatan-suggestions" role="listbox" aria-label="Cadangan Jawatan" hidden></ul>
        </td>
        <td><input aria-label="Bil. Perjawatan" type="number" min="1" max="100" value="3" required></td>
        <td><input aria-label="Bil. Pengisian" type="number" min="0" value="2" required></td>
        <td class="grid-numeric"><output aria-label="Bil. Kekosongan">1</output></td>
        <td><button type="button" class="button danger" data-jik-remove>Padam</button></td>
    </tr></template>
    <template data-other-allowance-template>
        <div class="gaji-other-item" data-other-item>
            <label>Nama Elaun<input data-other-name maxlength="120"></label>
            <label>/ Bulan (RM)<input data-other-monthly inputmode="decimal" maxlength="16" placeholder="0.00"></label>
            <div class="gaji-other-item-footer">
                <span>/ Tahun: <output data-other-item-annual>—</output></span>
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
        <td class="gaji-frozen gaji-frozen-grade"><select aria-label="Gred contoh"><option value="">Pilih gred</option><option value="A">Gred Contoh A</option><option value="B">Gred Contoh B</option></select></td>
        <td><select aria-label="Tarikh Pergerakan Gaji"><option value="1">01/01</option><option value="4">01/04</option><option value="7">01/07</option><option value="10">01/10</option></select></td>
        <td class="gaji-group-start"><input aria-label="Gaji Asal (RM)" inputmode="decimal" maxlength="16" placeholder="0.00"></td>
        @foreach (['asal-months', 'asal-subtotal', 'kgt', 'baharu', 'baharu-months', 'baharu-subtotal', 'total'] as $field)
            <td @class(['grid-numeric', 'gaji-group-start' => in_array($field, ['kgt', 'baharu', 'total'], true), 'gaji-total' => $field === 'total']) data-value="{{ $field }}">—</td>
        @endforeach
        @foreach (['ITJU', 'ITK @ ITKA', 'ITP @ BIPW', 'BISH @ BIW'] as $allowance)
            <td class="gaji-group-start">
                <input data-allowance-monthly aria-label="{{ $allowance }} sebulan (RM)" inputmode="decimal" maxlength="16" placeholder="0.00">
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
