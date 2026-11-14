# OS28000 — Penyelenggaraan dan Pembaikan Kecil Yang Dibeli: reference and Kadar decisions

**Confirmed by owner:** 12 November 2026. **Scope:** Reference-data and input rules only; no financial formula or application implementation.

## Category structure

| Level | Source field |
| --- | --- |
| **Kategori1** | **Kategori** |
| **Kategori2** | **Peralatan** (where supplied) |
| **Kadar** | **Mandatory** for all applicable entries: fixed reference value where prescribed; otherwise entered by user |

The reference has **10 rows**, comprising **four Kategori1 values**, with six vehicle-maintenance options, one Aset Alih, two fire-safety options and one Tebang Pokok.

| Kategori1 | Kategori2 | Prescribed Kadar (RM) | Kadar mode |
| --- | --- | ---: | --- |
| Penyelenggaraan Kenderaan Jabatan | Sedan | — | User-entered |
| Penyelenggaraan Kenderaan Jabatan | Pacuan Empat Roda | — | User-entered |
| Penyelenggaraan Kenderaan Jabatan | Van | — | User-entered |
| Penyelenggaraan Kenderaan Jabatan | Koster (Coaster) | — | User-entered |
| Penyelenggaraan Kenderaan Jabatan | Bas | — | User-entered |
| Penyelenggaraan Kenderaan Jabatan | Bot | — | User-entered |
| Penyelenggaraan Aset Alih | Aset Alih | — | User-entered |
| Penyelenggaraan Alat Pemadam Api | Alat Pemadam Api | 100.00 | Automatic reference lookup |
| Penyelenggaraan Alat Pemadam Api | Sistem Pencegahan Kebakaran | — | User-entered |
| Tebang Pokok | *(blank)* | 1,500.00 | Automatic reference lookup |

## Machine-readable references

- [Original Kadar reference](OS28000-penyelenggaraan-kadar-source.csv) — 10 supplied entries; the original eight blank Kadar cells remain **blank**, not RM0.
- [Selection and Kadar modes](OS28000-penyelenggaraan-kategori-kadar.csv) — the same 10 category combinations, with eight `USER_INPUT_KADAR` entries and two `FIXED_REFERENCE` entries.

The owner approved this **mixed Kadar behaviour**. All Kadar remain mandatory pursuant to the [shared mandatory Kadar rule](shared-mandatory-kadar-rule.md). No free-text description, additional rate, extra category, quantity formula or rounding policy is inferred.

**Tebang Pokok:** The source has an empty Peralatan field. Do not invent a Kategori2 value or require users to select a fabricated one.

## Source handling and scope

Only repeated internal whitespace has been normalised for readability; reference wording and numerical values otherwise follow the supplied source. This documentation does **not** modify the master Anggaran (OS) catalogue, Kod Aktiviti mapping, Architecture Decisions, Laravel or PostgreSQL. This is not a declaration that the complete OS28000 wizard or 27-column output mapping is finished.


## Owner-approved 30-column Anggaran specification — 14 November 2026

This subsequent owner decision supersedes the earlier pending quantity/formula statements **for OS28000 only**. The 10 reference combinations, original Kategori names and mixed KADAR behaviour above remain unchanged.

### Canonical Perbelanjaan applicability

| Status | Column positions | Count |
| --- | --- | ---: |
| Active | 01, 02, 03, 04, 14, 19, 21, 23, 26, 27, 28 | 11 |
| Conditional | 15 | 1 |
| Inactive | 05, 06, 07, 08, 09, 10, 11, 12, 13, 16, 17, 18, 20, 22, 24, 25 | 16 |
| Deferred | 29, 30 | 2 |

- 01 **OBJEK SEBAGAI** comes from the selected Anggaran; 02 **JPNT/ PPD** from the authorised Bidaan PTJ; 03 **SEKTOR/ SEKOLAH** from the controlled Tempat Bertugas; 28 **KATEGORI SUMBER PERUNTUKAN** from the Bidaan Aktiviti Kewangan.
- 04 **NAMA PROGRAM/ AKTIVITI/ TUJUAN** is Active, for the user-provided maintenance purpose / asset description.
- 14 **KATEGORI1** is Active with the four existing values. 15 **KATEGORI2** is Conditional, dependent on KATEGORI1 and **blank for Tebang Pokok**; no fabricated Peralatan choice. 16 **KATEGORI3** is Inactive.
- 19 **BIL. UNIT** is Active (quantity of items/assets).
- 21 **KADAR** is Active and mandatory, with **USER_INPUT_KADAR** for the original eight manually priced combinations and **FIXED_REFERENCE** for Alat Pemadam Api (RM100) and Tebang Pokok (RM1,500).
- 23 **KEKERAPAN** is Active and means **maintenance occurrences per month**. 26 **BILANGAN BULAN** is Active and means **number of months budgeted**. Both are user-entered.
- 27 **ANGGARAN JUMLAH SETAHUN** is Active and calculated. Columns 08–10 are Inactive/blank under the shared 30-column contract; 29–30 Deferred.
- Editor labels must not change the canonical 30 Excel headers or column order.

### Approved annual calculation

**ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KADAR × KEKERAPAN × BILANGAN BULAN**.

Illustrative only: 3 fire extinguishers × RM100 × 1 maintenance occurrence per month × 12 months = RM3,600. Preserve AD-008's exact-decimal calculations, stable Butiran identities, attribution to original reference inputs, totals and immutable submitted snapshots. No additional rounding, category, rate or eligibility rule is inferred.

**Implementation boundary:** Documentation only; the original reference CSVs, master catalogue, Kod Aktiviti mappings, Laravel, PostgreSQL and Excel implementation are unchanged. AD-008 remains Draft.
