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

## Batch 036 checkpoint — 16 November 2026

[PR #83](https://github.com/muhdmuizz/sistem-abm/pull/83) merged at `0aba6d03b0ebb16d7137015f172d2c24e28c0836`. OS29000 Bacaan Meter source editing now supports the four existing mappings, unchanged shared print choices, monthly copies × exact manual per-copy rate × independent manual months, and separate inclusive calendar current/overall contract durations. Current dates follow Kitaran year; overall contracts may span years. OS24000 remains separate. No migration/seed/reset or reference/rate/catalogue/mapping/permission/export-schema changes.

Tested `ef1b7c2c21b21fb7686f629ca81e79d6a94fb117`; [CI 43793891272](https://github.com/muhdmuizz/sistem-abm/actions/runs/43793891272): **503 tests / 4,830 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. All 18 meter state captures plus two representative existing-editor captures reviewed from 360 PNGs; [Batch 036](../development/batch-036-bacaan-meter-spreadsheet.md) records coverage and visual limits. Local sync/financial acceptance remains pending, including earlier unconfirmed batches. Programme inheritance, official financial outputs/totals/completeness and submission remain pending; AD-008 remains Draft and Steps 02/03 remain in progress.

**UI sequencing approved:** Complete remaining OS editors, then pause for consolidated editor UI refinement before another major module. Fix usability defects as encountered and apply AD-013 throughout. This is an implementation checkpoint, not a change to financial or design approval rules.

## Owner-approved Fotostat dependency and entry pathway — 16 November 2026

OS24000 can also use Program/ Aktiviti as an entry pathway; direct entry is not restricted by that note. Programme linkage/inheritance implementation remains separate.

OS24000 Sewaan Mesin Fotostat dan Riso activates its paired OS29000 Bacaan Meter atomically in the same Bidaan, respecting both permissions/catalogue mappings/Fasa/role guards. Direct meter activation is blocked. Paired deactivation retains drafts and links; explicit reactivation retains source-only drafts. Each saved rental item creates/updates one linked meter item, sharing workplace/category/location/current and overall contract dates. Meter copies, per-copy rate and months remain independent/initially blank. Rental is authoritative for shared details; no automatic financial copying or duplicate creation on repeated saves.

Existing unlinked meter drafts remain separate until explicit user selection/review. Linking preserves chosen meter financial inputs; only a financially blank generated meter may be explicitly replaced and deleted. Deletion of a rental requires confirmation displaying linked meter values and current revisions, then atomically audits/removes both sources/link. Direct copy/removal of linked meters uses rental instead; rental copy creates an independent pair. No name-based matching. Submitted/finalised and generated-output safeguards remain. These approved dependency rules supersede earlier standalone activation/source-ownership boundaries only for this pair; the financial identities/formulas and reference CSVs remain separate.

Implementation/verification is recorded in the Batch 038 checkpoint below; local acceptance remains pending.

## Batch 038 implementation checkpoint — 16 November 2026

[PR #87](https://github.com/muhdmuizz/sistem-abm/pull/87) merged at `9f294dc9a33c6b6dbb544e0505fa7b0bfe83a42d`. The approved rental editor and dependency/linkage rules above are implemented and cloud-verified at `75809c99be402bf0d0805837286557e2519d247a`: [CI 43817060723](https://github.com/muhdmuizz/sistem-abm/actions/runs/43817060723), 521 tests / 5,233 assertions, build/exact-money/Chromium passed. See [Batch 038](../development/batch-038-fotostat-meter-linkage.md) for guard coverage and reviewed/uninspected states. One additive linkage migration and local acceptance remain pending. Programme association/inheritance and official financial outputs/totals/completeness/submission remain deferred; direct entry remains available. No reference/rate/catalogue/mapping/export-schema changes. AD-008 remains Draft; this does not mark the broader AD implemented.
