# OS29000 — Bacaan Meter Mesin Fotostat: approved reference

**Confirmed:** 12 November 2026. **Scope:** Reference categories and Kadar input rules only, not implementation.

## Kategori1 — Jenis Cetakan

Reuse the already approved **OS24000 — Sewaan (Mesin Fotostat)** [shared two-option reference](OS24000-sewaan-mesin-fotostat-kategori.csv), rather than creating an independent duplicate reference table.

| Kategori1 | Kadar mode |
| --- | --- |
| Berwarna | Mandatory user-entered Kadar |
| Hitam dan Putih | Mandatory user-entered Kadar |

- **Kategori1** is Jenis Cetakan.
- **Kategori2 and subsequent category levels** are not applicable.
- **Kadar** must be entered by the user for each applicable selection, consistent with the [shared mandatory Kadar rule](shared-mandatory-kadar-rule.md).
- No prescribed Kadar was supplied; do not autofill, invent or assume one.
- The approved label is **Hitam dan Putih**, not `Tidak Berwarna`.

## Reference reuse and boundaries

This is the OS29000 **Bacaan Meter Mesin Fotostat** reference; it reuses the **selection options**, but does not merge OS29000 and OS24000 as financial objects or imply that their transactions, quantities, accounting mappings or pricing rules are shared. The underlying two-row OS24000 CSV remains unmodified.

No other OS29000 category, export field, unit, calculation, OS catalogue entry, Kod Aktiviti mapping, AD, Laravel or PostgreSQL is changed by this documentation.


## Owner-approved 30-column applicability and calculation — 15 November 2026

The owner explicitly approved following the existing [OS24000 Sewaan Mesin Fotostat specification](OS24000-sewaan-mesin-fotostat-reference.md#approved-os-specific-applicability-and-calculation--14-november-2026) for this **distinct OS29000 Bacaan Meter** Anggaran, including its contractual dates and durations, and separately approved **monthly copies × per-copy rate × months**. The earlier limitations regarding shared quantities and formulas are superseded only by these explicit decisions; this does not merge the two OS identities or Kod Aktiviti.

| Status | Canonical 30-column positions | Count |
| --- | --- | ---: |
| Active | 01, 02, 03, 05, 06, 07, 08, 09, 10, 14, 19, 20, 21, 26, 27, 28 | 16 |
| Inactive | 04, 11, 12, 13, 15, 16, 17, 18, 22, 23, 24, 25 | 12 |
| Deferred | 29, 30 | 2 |

- Columns **05 TARIKH MULA / 06 TARIKH TAMAT / 07 TEMPOH** denote the **Kontrak Semasa**. Columns **08 TARIKH MULA KONTRAK KESELURUHAN / 09 TARIKH TAMAT KONTRAK KESELURUHAN / 10 TEMPOH KONTRAK KESELURUHAN** denote the overall contract. Calculate each duration separately in calendar **years, months and days**, including its end date, as specified by OS24000. Neither calculated duration automatically sets column 26.
- Column **14 KATEGORI1** uses the same existing **Berwarna** and **Hitam dan Putih** choices; **15 KATEGORI2 / 16 KATEGORI3** Inactive.
- Column **19 BIL. UNIT** is displayed in the editor as **BIL. SALINAN**, meaning **estimated number of copies per month**, differentiated by KATEGORI1. The canonical exported header remains **BIL. UNIT**. Column **17 UNIT** stays Inactive following the approved OS24000 layout.
- Column **20 LOKASI URUSAN RASMI** displays as **LOKASI MESIN FOTOSTAT** in the editor; canonical export header unchanged.
- Column **21 KADAR** is mandatory user-entered **RM per copy** for the selected type; no prescribed rate and no automatic rate assumption.
- Column **26 BILANGAN BULAN** is manually entered, not automatically inherited from contract durations.
- Column **27 ANGGARAN JUMLAH SETAHUN = BIL. SALINAN (per month) × KADAR SESALINAN × BILANGAN BULAN**, calculated automatically.
- Columns 01–03 and 28 use existing common organisational/sumber peruntukan rules; 29–30 remain Deferred to Program/ Aktiviti.

Only documentation is updated; preserve the existing two-choice CSV, 30-column export contract, separate OS29000 variants, OS24000 documentation, Laravel application and database. AD-008 remains Draft.
