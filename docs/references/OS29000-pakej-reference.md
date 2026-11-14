# OS29000 — Pakej: confirmed category reference and Program/ Aktiviti alignment

**Owner-confirmed:** 12 November 2026. **Scope:** Reference design and confirmed change to existing Program/ Aktiviti Pakej component options; no application implementation.

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

**Boundary:** Documentation update only. No change to underlying four-row CSV, master catalogue, other OS29000 variants, Laravel, database or actual Excel exporter. AD-008 remains Draft.
