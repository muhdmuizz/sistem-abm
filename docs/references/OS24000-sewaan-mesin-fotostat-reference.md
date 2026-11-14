# OS24000 — Sewaan (Mesin Fotostat): approved reference

**Confirmed:** 12 November 2026. **Scope:** Reference categories and Kadar mode, not implementation.

## Kategori1 — Jenis Cetakan

| Kategori1 | Kadar mode |
| --- | --- |
| Berwarna | Mandatory user-entered Kadar |
| Hitam dan Putih | Mandatory user-entered Kadar |

**Hitam dan Putih** is the final approved wording, replacing the earlier proposed `Tidak Berwarna` label. **No Kategori2 or subsequent categories** are established for this reference.

**Machine-readable data:** [OS24000-sewaan-mesin-fotostat-kategori.csv](OS24000-sewaan-mesin-fotostat-kategori.csv) — two records.

**Kadar is mandatory**, under the [shared mandatory-Kadar rule](shared-mandatory-kadar-rule.md). The owner has not provided prescribed rates or a separate calculation formula for this reference.

## Distinction and boundaries

This is the reference for **OS24000 — Sewaan (Mesin Fotostat)**. It is distinct in reference content from [OS24000 — Sewaan (Umum)](OS24000-sewaan-umum-reference.md), which uses a three-level category hierarchy. **No decision has been made here to combine or split these references as separate selectable OS identities**.

Do not infer additional categories, quantities, rate formulas, eligibility, or mappings. The original master catalogue, Kod Aktiviti mapping, ADs, Laravel and PostgreSQL are unchanged.

## Approved OS-specific applicability and calculation — 14 November 2026

**Owner-approved for OS24000 — Sewaan (Mesin Fotostat) only.** This Anggaran is distinct from Sewaan (Umum) because its Kod Aktiviti differs. Preserve the existing `Berwarna` / `Hitam dan Putih` KATEGORI1 choices and mandatory manually entered KADAR. KATEGORI2 and KATEGORI3 are inactive.

This specification uses the **30-column Perbelanjaan contract** defined in [AD-008](../architecture/AD-008-os-catalogue-calculation-framework.md), not the superseded 27-column positions.

| Status | New column numbers | Count |
| --- | --- | ---: |
| Active | 01, 02, 03, 05, 06, 07, 08, 09, 10, 14, 19, 20, 21, 26, 27, 28 | 16 |
| Inactive | 04, 11, 12, 13, 15, 16, 17, 18, 22, 23, 24, 25 | 12 |
| Deferred | 29, 30 | 2 |

**16 + 12 + 2 = 30.** The original column meanings are retained when renumbered.

### Contract and financial fields

| Column | Canonical header | OS24000 Mesin Fotostat behaviour |
| ---: | --- | --- |
| 05 | TARIKH MULA | Tarikh Mula Kontrak Semasa; input |
| 06 | TARIKH TAMAT | Tarikh Tamat Kontrak Semasa; input |
| 07 | TEMPOH | Tempoh Kontrak Semasa; calculated calendar years, months, days, inclusive end date |
| 08 | TARIKH MULA KONTRAK KESELURUHAN | Input |
| 09 | TARIKH TAMAT KONTRAK KESELURUHAN | Input |
| 10 | TEMPOH KONTRAK KESELURUHAN | Calculated calendar years, months, days, inclusive end date |
| 14 | KATEGORI1 | Berwarna / Hitam dan Putih |
| 19 | BIL. UNIT | Active financial input |
| 20 | LOKASI URUSAN RASMI | Active; system display label **LOKASI MESIN FOTOSTAT**, canonical export header unchanged |
| 21 | KADAR | Mandatory manual input |
| 26 | BILANGAN BULAN | Manually entered, independent of both contract durations |
| 27 | ANGGARAN JUMLAH SETAHUN | Automatically calculated **BIL. UNIT × KADAR × BILANGAN BULAN** |

Columns 01–03 and 28 retain approved global organisational and sumber peruntukan rules; 29–30 remain deferred to Program/ Aktiviti.

**Contract durations:** Each period is calculated independently from its respective start/end dates, using inclusive end dates, expressed in calendar **tahun, bulan dan hari**. Do not approximate every year as 365 days or month as 30 days. Month-end and leap-year boundary rules must be verified through tests before implementation without overriding the approved concept. Neither duration automatically determines BILANGAN BULAN.

**Boundaries:** No change to the existing two-row category reference, Sewaan (Umum) references, catalogue, application, database, or Excel workbook is made by this documentation commit. The exact 30-column schema, compatibility measures and change-control gates are recorded in AD-008.
