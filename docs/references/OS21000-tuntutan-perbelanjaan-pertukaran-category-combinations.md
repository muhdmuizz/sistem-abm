# OS21000 — Tuntutan Perbelanjaan Pertukaran: Approved category, applicability and Kadar specification

**Approved:** 14 November 2026. **Scope:** This specific Anggaran (OS) with Kod Objek 21000 only. **Status:** Owner-approved specification checkpoint; not evidence of application implementation or passing tests.

## Final field hierarchy and export mapping

The **final system and 27-column export field names** are **TUJUAN → KATEGORI1 → KATEGORI2 → KATEGORI3**. There is **no KATEGORI4 field** in this final configuration. Earlier four-category terminology is superseded and must not be reused.

| Canonical column | Final system field | Permitted values |
| --- | --- | --- |
| 04 — NAMA PROGRAM/ AKTIVITI/ TUJUAN | TUJUAN | Bertukar Ke Luar Kawasan Pejabat |
| 11 — KATEGORI1 | KATEGORI1 | Tidak Berpindah Rumah; Berpindah Rumah |
| 12 — KATEGORI2 | KATEGORI2 | Bujang; Keluarga |
| 13 — KATEGORI3 | KATEGORI3 | Depends on KATEGORI1, as below |

### Eight permitted combinations (exact user-supplied labels)

| TUJUAN | KATEGORI1 | KATEGORI2 | KATEGORI3 |
| --- | --- | --- | --- |
| Bertukar Ke Luar Kawasan Pejabat | Tidak Berpindah Rumah | Bujang | Elaun Perjalanan Kenderaan/ Tambang Pengangkutan Awam |
| Bertukar Ke Luar Kawasan Pejabat | Tidak Berpindah Rumah | Keluarga | Elaun Perjalanan Kenderaan/ Tambang Pengangkutan Awam |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Bujang | Elaun Makan |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Bujang | Bayaran Sewa Hotel/ Elaun Lojing |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Bujang | Tambang Perjalanan |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Keluarga | Elaun Makan |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Keluarga | Bayaran Sewa Hotel/ Elaun Lojing |
| Bertukar Ke Luar Kawasan Pejabat | Berpindah Rumah | Keluarga | Tambang Perjalanan |

## Final 27-column applicability for direct entry

| No. | Canonical field | Status | Approved behaviour |
| ---: | --- | --- | --- |
| 01 | OBJEK SEBAGAI | Active | Auto from selected Anggaran (OS) |
| 02 | JPNT/ PPD | Active | Auto from authorised Bidaan PTJ |
| 03 | SEKTOR/ SEKOLAH | Active | Controlled manual Tempat Bertugas selection within authorised PTJ |
| 04 | NAMA PROGRAM/ AKTIVITI/ TUJUAN | Active | TUJUAN as approved above |
| 05 | TARIKH MULA | Inactive | — |
| 06 | TARIKH TAMAT | Inactive | — |
| 07 | TEMPOH | Inactive | — |
| 08 | NAMA | Active | Manual |
| 09 | JAWATAN | Active | Existing shared Jawatan dropdown |
| 10 | GRED | Active | Existing shared full SSPA Gred dropdown |
| 11 | KATEGORI1 | Active | Tidak Berpindah Rumah / Berpindah Rumah |
| 12 | KATEGORI2 | Active | Bujang / Keluarga |
| 13 | KATEGORI3 | Active | Dependent Jenis Tuntutan per eight combinations |
| 14 | UNIT | Inactive | — |
| 15 | GRED PENCERAMAH/ FASILITATOR | Inactive | — |
| 16 | BIL. UNIT | Inactive | — |
| 17 | LOKASI URUSAN RASMI | Inactive | — |
| 18 | KADAR | Active | Approved OS21000 reference matching and automatic/manual rule below |
| 19 | JARAK | Active | Used only when calculating Elaun Perjalanan Kenderaan |
| 20 | KEKERAPAN | Inactive | — |
| 21 | JAM | Inactive | — |
| 22 | BILANGAN HARI | Inactive | — |
| 23 | BILANGAN BULAN | Inactive | — |
| 24 | ANGGARAN JUMLAH SETAHUN | Active | Calculated using approved rules below |
| 25 | KATEGORI SUMBER PERUNTUKAN | Active | Auto from Bidaan Aktiviti Kewangan |
| 26 | KATEGORI PROGRAM | Deferred | Program/ Aktiviti pathway |
| 27 | KATEGORI PESERTA | Deferred | Program/ Aktiviti pathway |

**Verified totals: 14 Active + 11 Inactive + 2 Deferred = 27.** Applicability does not automatically alter other OS mappings.

## Owner-approved KATEGORI3 → OS21000 Kadar reference matching

Read the **existing** 83-row source unchanged from [OS21000-perjalanan-sara-hidup-revised-kadar.csv](OS21000-perjalanan-sara-hidup-revised-kadar.csv); this is a shared **rate source**, not an instruction to merge different Anggaran (OS).

| Pertukaran KATEGORI3 | Approved match in OS21000 source |
| --- | --- |
| Elaun Perjalanan Kenderaan/ Tambang Pengangkutan Awam | Elaun Perjalanan, Bayaran Tambang Bas dan Teksi |
| Elaun Makan | Makanan dan Minuman |
| Bayaran Sewa Hotel/ Elaun Lojing | Penginapan |
| Tambang Perjalanan | Bayaran Tambang Kereta Api; Bayaran Tambang Kapal/ Bot; Bayaran Kapal Terbang |

The combined claim labels above remain unchanged; these matches do not add KATEGORI3 options or alter reference labels. Distinct components are used for calculation as specified below. Where a category has several source transport modes, the applicable matched rate entry must be selected; no invented rate is permitted.

## Approved KADAR input and calculation rules

1. **When an applicable KADAR value exists in the approved OS21000 source, use automatic reference lookup.**
2. **When its applicable KADAR cell is blank, require manual KADAR entry.** A blank value is not automatically zero. The applicable KADAR is mandatory before the record is complete (also see [shared-mandatory-kadar-rule.md](shared-mandatory-kadar-rule.md)).
3. For **Elaun Perjalanan Kenderaan** within combined KATEGORI3 **Elaun Perjalanan Kenderaan/ Tambang Pengangkutan Awam**, **ANGGARAN JUMLAH SETAHUN = KADAR × JARAK**. JARAK is required for this calculation.
4. For **Tambang Pengangkutan Awam**, **Elaun Makan**, **Bayaran Sewa Hotel/ Elaun Lojing**, and **Tambang Perjalanan**, **ANGGARAN JUMLAH SETAHUN = KADAR × 1**. Do not use JARAK in those calculations.
5. No other multiplicative input (BIL. UNIT, KEKERAPAN, JAM, BILANGAN HARI, BILANGAN BULAN or TEMPOH) is activated by these decisions.
6. Preserve supplied source Kadar values including the recorded `1.00` for `Elaun Perjalanan, Bayaran Tambang Bas dan Teksi`; do **not** silently replace it with an external rate or infer additional vehicle-band policies. The user's approved multiplication rule governs its use where that matched source rate is applicable. No independent policy for units, rounding or eligibility is invented here.

## Scope and verification

- This document captures the owner's approved **27-column applicability, final field hierarchy, exact eight combinations, four reference matches, and calculation modes**.
- General organisational, authorisation, source-to-Butiran, Program/ Aktiviti and export rules continue to apply under AD-004, AD-008 and related decisions.
- Documentation-only change. **Application code, database, UI, end-to-end calculations and tests were not modified or verified by this commit.**

## Batch 039 checkpoint — 16 November 2026

[PR #89](https://github.com/muhdmuizz/sistem-abm/pull/89) merged at `874f7bf923fb9179231c38bae524e46b71f141b5`. OS21000 Tuntutan Perbelanjaan Pertukaran source editing supports eight approved category paths/five existing mappings, shared full-SSPA numeric grade lookup, explicit claim-component/reference-location choices, read-only fixed rates and matched-blank manual rates. Mileage preview = Kadar × Jarak; other approved claims = Kadar × 1. Special C/VU7 mapping remains unassumed. Source-only selectors add no canonical categories/export columns. Shared draft/audit/revision/Fasa/role/PTJ guards remain.

Tested `1c150db0d391d9a3c89deae03df9cc2802194fab`; [CI 43834725263](https://github.com/muhdmuizz/sistem-abm/actions/runs/43834725263), job 133188408042: **528 tests / 5,330 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/exact-money/Chromium passed. Thirteen Pertukaran and one existing-editor capture inspected from 426 PNGs (20 Pertukaran). Batch 039 records resolved fixture failures and visual/manual limits.

No new migration/seed/reset or reference/rate/catalogue/mapping/export-schema changes. Local sync/financial/device acceptance and Batch 038's additive migration remain unconfirmed. Official outputs/totals/completeness, programme inheritance and submission remain deferred; AD-008 remains Draft, Steps 02/03 remain in progress. Complete remaining OS editors before consolidated UI refinement; keep the development server running.
