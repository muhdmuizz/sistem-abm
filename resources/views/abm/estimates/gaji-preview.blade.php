@extends('abm.layout')
@section('title', 'Gaji & Emolumen')
@section('content')
<link rel="stylesheet" href="{{ asset('anggaran-grid.css') }}">
<script src="{{ asset('abm-money.js') }}" defer></script>
<script src="{{ asset('gaji-preview.js') }}" defer></script>

<div class="page-heading">
    <div>
        <h1>Gaji &amp; Emolumen</h1>
        <p class="intro">Pratonton · Pejabat JPNT · Kitaran {{ $year }}</p>
    </div>
</div>
<p class="notice">Gunakan data contoh sahaja. Nama, gred dan kadar dalam pratonton ini tidak disimpan.</p>

<div data-gaji-preview data-year="{{ $year }}">
    <div class="grid-toolbar" role="tablist" aria-label="Gaji dan Emolumen">
        <button type="button" class="button primary" id="gaji-jik-tab" role="tab" aria-selected="true" aria-controls="gaji-jik-panel">JIK</button>
        <button type="button" class="button" id="gaji-wizard-tab" role="tab" aria-selected="false" aria-controls="gaji-wizard-panel" tabindex="-1">Wizard</button>
    </div>
    <section class="section" id="gaji-jik-panel" role="tabpanel" aria-labelledby="gaji-jik-tab" tabindex="0">
        <h2 id="jik-title">Jawatan, Isi, Kosong (JIK)</h2>
        <form data-jik>
            <div class="anggaran-grid-scroll" tabindex="0" role="region" aria-label="Jadual JIK">
                <table class="anggaran-grid">
                    <thead><tr><th scope="col">Jawatan</th><th scope="col">Bil. Perjawatan</th><th scope="col">Pengisian</th><th scope="col">Kekosongan</th></tr></thead>
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
        <h2 id="wizard-title">Wizard Gaji &amp; Emolumen</h2>
        <div class="grid-toolbar">
            <label>Ketua Jabatan <select data-ketua><option value="">Pilih pegawai</option></select></label>
        </div>
        <h3>OS11000 — Gaji dan Upahan</h3>
        <div class="anggaran-grid-scroll" tabindex="0" role="region" aria-label="Jadual OS11000">
            <table class="anggaran-grid" style="min-width: 1550px">
                <thead><tr>
                    @foreach (['Jawatan', 'Status', 'Nama Pegawai', 'Gred', 'TPG '.$year, 'Gaji Asal (RM)', 'Bil. Bulan (Gaji Asal)', 'Jumlah Kecil (Gaji Asal) (RM)', 'KGT (RM)', 'Gaji Baharu (RM)', 'Bil. Bulan (Gaji Baharu)', 'Jumlah Kecil (Gaji Baharu) (RM)', 'Jumlah OS11000 (RM)'] as $label)
                        <th scope="col">{{ $label }}</th>
                    @endforeach
                </tr></thead>
                <tbody data-wizard-rows></tbody>
            </table>
        </div>
        <p class="notice">Baris Kosong menggunakan Gaji Median × 3 bulan. OS12000 dan OS13000 menunggu penetapan butiran.</p>
    </section>

    <template data-jik-template><tr>
        <td><input aria-label="Jawatan" value="Jawatan Contoh" required maxlength="100"></td>
        <td><input aria-label="Bil. Perjawatan" type="number" min="1" max="100" value="3" required></td>
        <td><input aria-label="Pengisian" type="number" min="0" value="2" required></td>
        <td><output aria-label="Kekosongan">1</output></td>
    </tr></template>
    <template data-wizard-template><tr>
        <th scope="row" data-position></th>
        <td><strong data-status></strong></td>
        <td><input aria-label="Nama Pegawai" maxlength="100"></td>
        <td><select aria-label="Gred contoh"><option value="">Pilih gred</option><option value="A">Gred Contoh A</option><option value="B">Gred Contoh B</option></select></td>
        <td><select aria-label="Tarikh Pergerakan Gaji"><option value="1">01/01</option><option value="4">01/04</option><option value="7">01/07</option><option value="10">01/10</option></select></td>
        <td><input aria-label="Gaji Asal (RM)" inputmode="decimal" maxlength="16" placeholder="0.00"></td>
        @foreach (['asal-months', 'asal-subtotal', 'kgt', 'baharu', 'baharu-months', 'baharu-subtotal', 'total'] as $field)
            <td class="grid-numeric" data-value="{{ $field }}">—</td>
        @endforeach
    </tr></template>
</div>
@endsection
