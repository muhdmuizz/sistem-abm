# OS29000 — Pakej: confirmed category reference and Program/ Aktiviti alignment

**Owner-confirmed:** 12 November 2026. **Original scope:** Reference design and confirmed Program/ Aktiviti component choices. Application implementation status is recorded below.

## Kategori1 — Komponen Pakej

| Kategori1 | Kadar |
| --- | --- |
| Penginapan | Mandatory, user-entered |
| Makan Minum | Mandatory, user-entered |
| Sewaan Dewan | Mandatory, user-entered |
| Sewaan Peralatan | Mandatory, user-entered |

- **Kategori1:** the four choices above, in the approved order.
- **Kategori2 onwards:** not required on the provided reference.
- **Kadar:** mandatory user entry for the applicable component; no prescribed rate supplied, no rate formula inferred.
- **Machine-readable reference:** [OS29000-pakej-kategori.csv](OS29000-pakej-kategori.csv).
- **Shared Kadar completeness:** [shared mandatory Kadar rule](shared-mandatory-kadar-rule.md).

## Supersession of previous Program/ Aktiviti options

The earlier Program/ Aktiviti design listed **three** package components (`Makan Minum`, `Penginapan`, `Dewan`). The owner explicitly directed: **onward, use these four**.

Therefore, **both OS29000 — Pakej and the Program/ Aktiviti Pakej component selection** must use the four-item list above. `Sewaan Dewan` is the approved label in the new four-option list. **Sewaan Peralatan** is now included.

This changes the component choices, **not** the previously approved rules around Pakej/Bukan Pakej, whether a package triggers OS selection, financial totals, output-field layout, or stored-data migration. Do not invent additional requirements.

## Limits

No Kategori2 or Kategori3, price, unit, quantity rule, or new Excel column is implied. This reference does not affect other OS29000 variants, the master OS catalogue or Kod Aktiviti mappings.


## Approved 30-column Anggaran and calculation specification — 15 November 2026

The owner approved the following **duration-aware** OS29000 Pakej design after the earlier reference-only decisions. It uses the existing **four KATEGORI1 options**, in their approved order, and does not alter the established Program/ Aktiviti Pakej component choices or separate Kod Aktiviti mappings. Earlier statements that no rate basis, UNIT or formula was approved are superseded by this later approval.

| Status | Canonical Perbelanjaan column numbers | Count |
| --- | --- | ---: |
| Active | 01, 02, 03, 04, 05, 06, 07, 14, 17, 19, 21, 25, 27, 28 | 14 |
| Inactive | 08, 09, 10, 11, 12, 13, 15, 16, 18, 20, 22, 23, 24, 26 | 14 |
| Deferred | 29, 30 | 2 |

**14 + 14 + 2 = 30.** Use AD-008's existing canonical headers, including 04 NAMA PROGRAM/ AKTIVITI/ TUJUAN, 05/06 TARIKH MULA/TAMAT, 07 TEMPOH, 14 KATEGORI1, 17 UNIT, 19 BIL. UNIT, 21 KADAR, 25 BILANGAN HARI, 27 ANGGARAN JUMLAH SETAHUN and 28 KATEGORI SUMBER PERUNTUKAN. Organisational and source-of-funding fields retain their globally approved behaviours. Columns 29–30 remain Deferred to Program/ Aktiviti.

### Approved component quantities and rate bases

| KATEGORI1 | UNIT (17) | Meaning of BIL. UNIT (19) | Mandatory manual KADAR (21) | Column 25 editor label / duration |
| --- | --- | --- | --- | --- |
| Penginapan | Bilik | Number of rooms | RM sebilik semalam | **BIL. MALAM** (chargeable nights) |
| Makan Minum | Orang | Number of people | RM seorang sehari | BILANGAN HARI (chargeable days) |
| Sewaan Dewan | Dewan | Number of halls | RM sedewan sehari | BILANGAN HARI (chargeable days) |
| Sewaan Peralatan | Unit | Number of equipment units | RM seunit sehari | BILANGAN HARI (chargeable days) |

**Calculation for each component: ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KADAR × BILANGAN HARI (column 25).** For Penginapan, column 25 is semantically chargeable **nights**, not calendar days; its canonical Excel header remains **BILANGAN HARI** despite the contextual editor label **BIL. MALAM**. Do not insert or rename canonical columns.

The Program/ Aktiviti start/end dates may inform the budget period but must **not silently determine** the number of billable nights, meals or rental days. Users enter the appropriate chargeable duration. For Makan Minum the approved rate basis is **per person per day**; a price quoted per meal cannot be used as a per-day rate without a suitable explicitly supplied daily equivalent. Keep all four KADAR values as **mandatory user inputs**; do not invent default prices.

These are separate Pakej component Anggaran rows. Retain the approved Program/ Aktiviti four-component selection and existing Pakej/Bukan Pakej behaviour without adding unapproved programme-trigger, eligibility or migration rules.

**Boundary at the specification approval checkpoint:** Documentation update only. No change to underlying four-row CSV, master catalogue, other OS29000 variants, Laravel, database or actual Excel exporter. AD-008 remains Draft.

## Batch 032 checkpoint — 15 November 2026

[PR #74](https://github.com/muhdmuizz/sistem-abm/pull/74) merged at `3494a318fda11ecc8468d26f13e3e2f8f56caff5`. OS29000 **Pakej (Mesyuarat/ Bengkel/ Kursus)** now has the approved direct source spreadsheet across its five existing activity mappings. Four components retain separate manual rate bases: Penginapan uses rooms/nights; Makan Minum people/days; Sewaan Dewan halls/days; Sewaan Peralatan units/days. Exact preview = Bil. Unit × Kadar × chargeable duration. Calendar dates/Tempoh do not set charged nights/days. Partial drafts, source precision, title-case headers and tabular/grouped/two-decimal money are retained. Shared validation keeps rate/duration descriptions alongside errors.

Tested application head `5dfb64a5fbeab18df512772ec89de6812802ceea`; [CI 43689211251](https://github.com/muhdmuizz/sistem-abm/actions/runs/43689211251): **480 tests / 4030 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Eleven representative final-head desktop/mobile captures reviewed from 18 Pakej captures (282 total). See [Batch 032](../development/batch-032-pakej-spreadsheet.md) for evidence and limits. No migration/seed or catalogue/mapping/permission/reference CSV changes. Local Batch 032 update and financial acceptance remain pending. Batch 031 supplied local synchronisation/targeted cleanup migration is user-confirmed; detailed financial/role/device acceptance remains unverified. Official outputs/totals/completeness, programme inheritance and submission remain pending; Steps 02/03 remain in progress and AD-008 remains Draft.

The earlier reference-only/no-Laravel status is historical and superseded for direct source entry by Batch 032. The approved quantities, rate bases, canonical column mapping and deferred programme fields are unchanged. No actual Excel exporter or automatic programme association is implemented by this batch.

## Owner-approved grouped activity refinement — 15 November 2026

After reviewing an interactive preview, the owner directed implementation with **“Let's have a go.”** One direct Pakej activity contains multiple component inputs. Enter Tempat Bertugas, Nama Program/ Aktiviti and dates once at activity level, then expand its component spreadsheet. Each component retains its existing category, unit, quantity, mandatory manual rate basis and chargeable duration. Show component previews and a draft subtotal for the activity; an incomplete component does not yield a misleading partial subtotal. Preserve title-case headers and exact accounting-style monetary presentation. This supersedes Batch 032's repeated shared fields/flat presentation, without merging unrelated activities by name.

Technical source representation is one existing Butiran draft per grouped activity containing distinct component inputs; no new financial hierarchy/entity or canonical Excel column is added. Components remain granular for later approved output integration; this refinement does not implement or change the actual Excel exporter. Add/copy/remove/undo and the established transactional guards apply. The approved preview did not choose association with an existing Program/ Aktiviti; direct grouped entry is implemented here and programme linking/inheritance remains pending. Rates, eligibility, dates-versus-chargeable-duration rules and the four-component list are unchanged.

## Batch 033 checkpoint — 15 November 2026

[PR #76](https://github.com/muhdmuizz/sistem-abm/pull/76) merged at `a9c51a29720d5d833aef86a51f2af568d075bffd`. A direct Pakej activity now holds shared workplace/name/dates once, with expandable component rows and an exact draft subtotal. Components retain separate quantity, manual rate basis and chargeable nights/days; dates never determine charged duration. Add/copy/remove/undo operate at both levels, nested errors reopen the affected group, and pending saves lock edits then restore controls on failure. Incomplete components leave the subtotal incomplete.

One grouped activity uses one existing Butiran source envelope (v2) with a component list and stable group-local UUIDs. This retains parent workplace ownership, transactional save/audit/revision guards and whole-activity pagination. Existing v1 component sources reopen separately as one-component activities; no name-based merge, new preservation interface, schema migration or reset. No rate/mapping/permission/reference CSV/canonical-column changes. Linking to existing Program/ Aktiviti, inheritance and official financial outputs/totals/completeness/submission remain pending; AD-008 remains Draft and Steps 02/03 remain in progress.

Tested application head `54ddebf271de5a979a089beaabd5cb0688ce2d1e`; [CI 43694224425](https://github.com/muhdmuizz/sistem-abm/actions/runs/43694224425): **485 tests / 4137 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Fifteen representative final-head screenshots reviewed from 39 Pakej captures (303 total). See [Batch 033](../development/batch-033-grouped-pakej.md) for coverage, resolved failures and visual-review limits. Local Batch 033 update/financial acceptance pending; Batch 032 local confirmation remains unreceived. No migration/seed or server restart required.
