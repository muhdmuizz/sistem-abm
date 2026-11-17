# OS22000 — One Anggaran (OS), two KATEGORI1 paths

**Owner-confirmed initial structure:** 12 November 2026. **Final correction of path selector:** 14 November 2026 (KATEGORI1 restored; prior TUJUAN-selector revision superseded). **Reference correction:** 14 November 2026 based on the owner's newly supplied 126-row `LOKASI PERTUKARAN / BUJANG/ KELUARGA / GRED / KATEGORI TUNTUTAN / KADAR` table. **Status:** Authoritative OS22000 reference, documentation only.

## Scope and mandatory source separation

**OS22000 is ONE Anggaran (OS), with exactly two KATEGORI1 choices in canonical column 11**. KATEGORI1 determines the applicable path; **column 04 — NAMA PROGRAM/ AKTIVITI/ TUJUAN is NOT the path selector**. Never create two OS22000 Anggaran identities from historical catalogue rows.

| KATEGORI1 (column 11) | Confirmed configuration |
| --- | --- |
| **Pertukaran/ Penempatan** | Use the owner's verified 126-row OS22000 Pertukaran rate reference, currently stored under its historical filename [OS22000-pengangkutan-barang-kadar.csv](OS22000-pengangkutan-barang-kadar.csv). **Lokasi Pertukaran uses canonical Excel column 17 (LOKASI URUSAN RASMI), displayed as LOKASI PERTUKARAN for this OS22000 path; it is not a Kategori level.** `Bujang/ Keluarga` and `Kategori Tuntutan` are KATEGORI2 and KATEGORI3. `Gred` remains an independent personnel/rate-lookup attribute. |
| **Pengangkutan Barang** | No KATEGORI2 or subsequent categories. **TUJUAN (column 04)** and **KADAR (column 18)** are mandatory manual inputs; no grade-based rate lookup. Do not populate this path with additional choices from the old 126-row table. Do not populate this path with additional choices from the old 126-row table. |

For **Pertukaran/ Penempatan**, the verified hierarchy is:

| System field | Approved source / values |
| --- | --- |
| KATEGORI1 (column 11) | Pertukaran/ Penempatan |
| KATEGORI2 (column 12) | Bujang/ Keluarga: Bujang; Keluarga |
| KATEGORI3 (column 13) | Kategori Tuntutan: Tambang Pengangkutan Barang; Pemberian Pindahan; Elaun Berpindah; Belanja Pelbagai |
| GRED (separate field) | Source Gred from verified rate rows, including special C; do not invent mappings or missing grade values |

**Approved OS-specific column 17 mapping (14 November 2026):** The canonical 27-column Excel field **17 — LOKASI URUSAN RASMI** is **Active** for `KATEGORI1 = Pertukaran/ Penempatan` and captures source **Lokasi Pertukaran**, with choices `Semenanjung` and `Sabah/ Sarawak/ WP Labuan`. In this path's spreadsheet-like editor, **rename the displayed field label to `LOKASI PERTUKARAN`**. Keep the official Excel column header `LOKASI URUSAN RASMI` unchanged so the 27-column export contract is preserved. This is an OS-specific display label, not a global rename, and does not add another category.

**Rate behaviour in the existing reference:** For Pemberian Pindahan and Elaun Berpindah, use the grade/location/status-matched supplied Kadar. For Tambang Pengangkutan Barang and Belanja Pelbagai, Kadar is mandatory user input where the supplied reference is blank. Preserve all source figures and blank values. The exact formulas and remaining column applicability are not approved by this correction; the KATEGORI1–3 and Lokasi Pertukaran column mappings above are approved. The use of column 04 for Pertukaran/ Penempatan is not determined by the manual-TUJUAN rule specific to Pengangkutan Barang.

### Explicit exclusion: misattributed eight-row data

**Do not use** [OS22000-pertukaran-penempatan-kategori.csv](OS22000-pertukaran-penempatan-kategori.csv), or the eight-row category table in [OS22000-pertukaran-penempatan-kategori.md](OS22000-pertukaran-penempatan-kategori.md), to configure OS22000. Those rows contain `Bertukar Ke Luar Kawasan Pejabat`, `Tidak Berpindah Rumah / Berpindah Rumah`, `Elaun Makan`, `Bayaran Sewa Hotel/ Elaun Lojing`, etc. The owner has explicitly identified them as **OS21000 Tuntutan Perbelanjaan Pertukaran** material, not the OS22000 Pertukaran rate reference. They remain in Git only for provenance, **superseded and non-operative**. Do not append them as KATEGORI2–KATEGORI5.

### Historical filename clarification

The 126-row source file name `OS22000-pengangkutan-barang-kadar.csv` is **historically misleading**. Its actual content (five source columns) was resupplied by the owner on 14 November 2026 as the reference for **OS22000 Pertukaran**. The exact rows and amounts are preserved; **the source filename is not evidence that its rate lookup belongs to KATEGORI1 = Pengangkutan Barang**. The existing [OS22000-pengangkutan-barang-reference.md](OS22000-pengangkutan-barang-reference.md) is similarly a historical file name, and has been marked with corrected provenance.

## Other retained decisions

- Selection of a KATEGORI1 path does not depend on Kod Aktiviti.
- **For KATEGORI1 = Pengangkutan Barang, column 04 — NAMA PROGRAM/ AKTIVITI/ TUJUAN is mandatory manual input.** It is not the selector. Its exact input options/mechanism beyond manual entry are not approved; do not import OS14000 options.
- Jawatan and Gred remain independent shared reference attributes. Do not fabricate Gred 11 where the verified rate reference has none.
- The two OS22000 labels in the legacy master catalogue and mapping records do **not** supersede the one-OS/two-path decision. Reconcile the eventual import carefully without inventing codes.
- No new eligibility, quantities, rounding or formulas are authorised by this correction.

## Implementation boundary

This correction updates OS22000 documentation only. The historical source CSV, existing amount values, catalogue, application code, DB and Excel export are not modified; implementation and testing remain outstanding. **For all future OS22000 work, read this document first.**

## Approved 27-column applicability and calculation — 14 November 2026

The following decisions apply to the **same OS22000 Anggaran**, conditional upon the selected KATEGORI1. The 27 canonical Excel headers remain unchanged. Global column rules are retained; the two branches must not be treated as separate OS identities.

| No. | Canonical column | Pertukaran/ Penempatan | Pengangkutan Barang | Behaviour |
| ---: | --- | --- | --- | --- |
| 01 | OBJEK SEBAGAI | Active | Active | Auto from selected Anggaran |
| 02 | JPNT/ PPD | Active | Active | Auto from authorised Bidaan PTJ |
| 03 | SEKTOR/ SEKOLAH | Active | Active | Controlled Tempat Bertugas choice under authorised PTJ |
| 04 | NAMA PROGRAM/ AKTIVITI/ TUJUAN | Inactive | Active | For Pengangkutan Barang: mandatory manual TUJUAN; not the path selector |
| 05 | TARIKH MULA | Inactive | Inactive | — |
| 06 | TARIKH TAMAT | Inactive | Inactive | — |
| 07 | TEMPOH | Inactive | Inactive | — |
| 08 | NAMA | Active | Inactive | Manual where active |
| 09 | JAWATAN | Active | Inactive | Existing shared Jawatan reference |
| 10 | GRED | Active | Inactive | Existing SSPA grade reference; source's special C must not be guessed |
| 11 | KATEGORI1 | Active | Active | Path: Pertukaran/ Penempatan or Pengangkutan Barang |
| 12 | KATEGORI2 | Active | Inactive | Pertukaran/ Penempatan only: Bujang / Keluarga |
| 13 | KATEGORI3 | Active | Inactive | Pertukaran/ Penempatan only: Tambang Pengangkutan Barang; Pemberian Pindahan; Elaun Berpindah; Belanja Pelbagai |
| 14 | UNIT | Inactive | Inactive | — |
| 15 | GRED PENCERAMAH/ FASILITATOR | Inactive | Inactive | — |
| 16 | BIL. UNIT | Inactive | Inactive | — |
| 17 | LOKASI URUSAN RASMI | Active | Inactive | For Pertukaran/ Penempatan, editor label **LOKASI PERTUKARAN** with Semenanjung; Sabah/ Sarawak/ WP Labuan; canonical Excel header unchanged |
| 18 | KADAR | Active | Active | Pertukaran/ Penempatan: reference lookup or manual by KATEGORI3; Pengangkutan Barang: mandatory manual |
| 19 | JARAK | Inactive | Inactive | — |
| 20 | KEKERAPAN | Inactive | Inactive | — |
| 21 | JAM | Inactive | Inactive | — |
| 22 | BILANGAN HARI | Inactive | Inactive | — |
| 23 | BILANGAN BULAN | Inactive | Inactive | — |
| 24 | ANGGARAN JUMLAH SETAHUN | Active | Active | **KADAR × 1**, both branches |
| 25 | KATEGORI SUMBER PERUNTUKAN | Active | Active | Auto from Bidaan Aktiviti Kewangan |
| 26 | KATEGORI PROGRAM | Deferred | Deferred | Program/ Aktiviti pathway |
| 27 | KATEGORI PESERTA | Deferred | Deferred | Program/ Aktiviti pathway |

**Applicability reconciliation:**
- Pertukaran/ Penempatan: **13 Active + 12 Inactive + 2 Deferred = 27**.
- Pengangkutan Barang: **8 Active + 17 Inactive + 2 Deferred = 27**.

### Approved Kadar modes and exact calculation

| KATEGORI1 | KATEGORI3, where applicable | KADAR mode | ANGGARAN JUMLAH SETAHUN |
| --- | --- | --- | --- |
| Pertukaran/ Penempatan | Tambang Pengangkutan Barang | Mandatory user-entered KADAR (blank reference) | KADAR × 1 |
| Pertukaran/ Penempatan | Pemberian Pindahan | Prescribed lookup by Lokasi Pertukaran, Bujang/ Keluarga and applicable Gred | KADAR × 1 |
| Pertukaran/ Penempatan | Elaun Berpindah | Prescribed lookup by Lokasi Pertukaran, Bujang/ Keluarga and applicable Gred | KADAR × 1 |
| Pertukaran/ Penempatan | Belanja Pelbagai | Mandatory user-entered KADAR (blank reference) | KADAR × 1 |
| Pengangkutan Barang | Not applicable | Mandatory user-entered KADAR; no grade-based lookup | KADAR × 1 |

All applicable KADAR values are mandatory. Blank source entries must not be treated as zero or arbitrary defaults. No additional quantities, frequencies, durations or JARAK multipliers are authorised. Do not alter source rate values or introduce eligibility/rounding rules.

**Status:** Owner-approved design specification committed to GitHub. This does not claim implementation, migrations, UI updates or end-to-end tests.

## Batch 040 checkpoint — 17 November 2026

[PR #91](https://github.com/muhdmuizz/sistem-abm/pull/91) merged at `a45ec936cb67d565e72446729830fd12f96047f2`. OS22000 now uses one retained catalogue identity and two Kategori1 paths: Pertukaran/ Penempatan (family, claim, Lokasi Pertukaran and personnel/reference lookup) and Pengangkutan Barang (manual Tujuan/Kadar). Both exact draft previews = Kadar × 1; inactive fields are cleared/rejected. No missing grade 11, special C/VU7 mapping or absent location is fabricated. Existing source rates and canonical export headers remain unchanged.

Targeted migration removes only the obsolete OS22000 identity's development graph/overrides, retains current/unrelated records and mapping provenance/IDs, increments affected bid revisions and aborts atomically for non-draft Bidaan. Retains all seven existing codes. Catalogue **31 (29 eligible/two excluded)**; mappings **105 (103 eligible)**. Ordinary migration and idempotent OsReferenceSeeder import are required locally; no broad reset or financial seed.

Tested `457cc94cd01d40280edea8fac2a77936d0d01d13`; [CI 44038128200](https://github.com/muhdmuizz/sistem-abm/actions/runs/44038128200), job 133885875932: **537 tests / 5,435 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/exact-money/Chromium passed. Thirteen OS22000 and one existing-editor capture reviewed from 446 PNGs (20 OS22000); Batch 040 records resolved fixture/count failures and review limits.

Local sync/migration/import/financial/device acceptance remains pending; earlier unconfirmed statuses unchanged. Use Updates separately from Server; no routine server restart. Official outputs/totals/completeness/submission, programme inheritance and export remain deferred. AD-008 stays Draft; Steps 02/03 in progress. Complete remaining OS editors before consolidated UI refinement.
