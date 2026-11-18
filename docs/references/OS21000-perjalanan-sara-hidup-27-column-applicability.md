# OS21000 — Perjalanan Sara Hidup: 27-column direct-entry applicability checkpoint

**Recorded:** 14 November 2026. **Status:** Latest dated owner approvals implemented/verified in Batch048 source editors; official financial/programme integration and special-grade policy remain pending. Table reconciled18November2026 by canonical field identity; numerical positions retain the historical27-column frame.

## Scope and preserved references

This checkpoint applies to three **separate Anggaran (OS)**, each with Kod Objek 21000:

- **Perjalanan Sara Hidup (Umum)** — Kod Aktiviti `010801`.
- **Perjalanan Sara Hidup (Sukan)** — Kod Aktiviti `010803`.
- **Perjalanan Sara Hidup (Kokurikulum)** — Kod Aktiviti `010803`.

These are Anggaran labels, **not** KATEGORI1 values. All three use the approved shared six-category, 83-row rate source in [OS21000-perjalanan-sara-hidup-variants-and-kategori.md](OS21000-perjalanan-sara-hidup-variants-and-kategori.md) and [OS21000-perjalanan-sara-hidup-revised-kadar.csv](OS21000-perjalanan-sara-hidup-revised-kadar.csv). Preserve original rates and blank cells, including special grade C; do not invent rates, zero defaults, or mappings. General column behaviours remain as recorded in the [OS15000 column-behaviour checkpoint](OS15000-faedah-faedah-kewangan-yang-lain-27-column-applicability.md). This exercise configures direct OS entry only; Program/ Aktiviti remains a future alternative doorway.

## Approved common applicability (all three Anggaran)

| No. | Canonical column | Status | Input behaviour or contextual rule |
| ---: | --- | --- | --- |
| 01 | OBJEK SEBAGAI | Active | Auto from selected Anggaran (OS) |
| 02 | JPNT/ PPD | Active | Auto from authorised Bidaan PTJ |
| 03 | SEKTOR/ SEKOLAH | Active | Manual controlled Tempat Bertugas selection restricted to user's PTJ |
| 04 | NAMA PROGRAM/ AKTIVITI/ TUJUAN | Active | OS-specific direct-entry value; meaning not further defined |
| 05 | TARIKH MULA | Active | OS-specific start date |
| 06 | TARIKH TAMAT | Active | OS-specific end date |
| 07 | TEMPOH | Active | Inclusive calendar days; informational, independent of manual claim quantities |
| 08 | NAMA | Active | Manual |
| 09 | JAWATAN | Active | Existing shared reference drop-down |
| 10 | GRED | Active | Existing full SSPA Gred reference drop-down |
| 11 | KATEGORI1 | Active | Existing six Kategori Tuntutan choices |
| 12 | KATEGORI2 | Conditional | Elaun Perjalanan Kenderaan or Bas/ Teksi under the existing combined category only; blank elsewhere |
| 13 | KATEGORI3 | Inactive | — |
| 14 | UNIT | Inactive | — |
| 15 | GRED PENCERAMAH/ FASILITATOR | Inactive | — |
| 16 | BIL. UNIT | Inactive | — |
| 17 | LOKASI URUSAN RASMI | Active | Existing approved shared reference; location-dependent rate data retained |
| 18 | KADAR | Active | Fixed supplied meal/hotel/mileage rates; approved manual fares; withhold unmatched grade rates |
| 19 | JARAK | Conditional | Manual distance, **only for Elaun Perjalanan Kenderaan** within KATEGORI1 **Elaun Perjalanan, Bayaran Tambang Bas dan Teksi**; not for the Bas/Teksi component; do not create a new KATEGORI1 |
| 20 | KEKERAPAN | Active | Trips per month |
| 21 | JAM | Inactive | — |
| 22 | BILANGAN HARI | Conditional | Manual claimable days per trip for meals; manual nights per trip for accommodation; inactive elsewhere |
| 23 | BILANGAN BULAN | Active | Independently entered month multiplier |
| 24 | ANGGARAN JUMLAH SETAHUN | Active | Approved component formula × trips/month × manual months; source preview verified, official integration pending |
| 25 | KATEGORI SUMBER PERUNTUKAN | Active | Auto from Bidaan Aktiviti Kewangan, approved mapping |
| 26 | KATEGORI PROGRAM | Deferred | Program/ Aktiviti alternative entry |
| 27 | KATEGORI PESERTA | Deferred | Program/ Aktiviti alternative entry |

**Reconciled counts (historical27-column positions):**17 active,3 conditional,5 inactive,2 deferred=27. The three inserted current contract fields are inactive for travel, making current30-column counts17 active,3 conditional,8 inactive,2 deferred. Earlier18/1/4/2-unresolved/2 counts are historical, superseded by explicit18November approvals.

## Approved KATEGORI1 labels (unchanged)

1. Makanan dan Minuman
2. Penginapan
3. Bayaran Tambang Kereta Api
4. Elaun Perjalanan, Bayaran Tambang Bas dan Teksi
5. Bayaran Tambang Kapal/ Bot
6. Bayaran Kapal Terbang

**Clarification explicitly approved:** `JARAK` applies only to **Elaun Perjalanan Kenderaan**, a component falling under existing KATEGORI1 `Elaun Perjalanan, Bayaran Tambang Bas dan Teksi`. This does **not** rename or split that category.

## Historical outstanding list — superseded only by dated approvals below

- Reconciliation of KATEGORI2/3 applicability or any conditional subcomponent representation; no new reference values authorized.
- Handling of missing/blank Kadar reference rows.
- Exact formulas, units, multipliers, duration calculation, decimal rounding, and eligibility.
- Direct-entry vs Program/ Aktiviti inheritance mapping, implementation, DB and tests.

**Archival documentation only.** Do not claim full production readiness.


## Owner-approved row structure and formulas — 18 November 2026

Owner approved the proposed structure/formulas for all three separate travel variants (Umum, Sukan, Kokurikulum): **one named officer and one claim component per source row**. An officer claiming meals, accommodation and vehicle mileage has three separate rows, each with a stable independent Butiran identity. Do not derive individual claim quantities from programme headcounts or silently merge rows by officer.

| Component | Annual amount | Rate mode / quantity meaning |
| --- | --- | --- |
| Makanan dan Minuman | Reference rate × Claimable Days × Trips per Month × Months | Existing grade/location reference rate; manually entered claimable days per trip |
| Penginapan | Reference rate × Nights × Trips per Month × Months | Existing grade/location reference rate; manually entered nights per trip |
| Elaun Perjalanan Kenderaan | Reference rate × Distance per Trip × Trips per Month × Months | Existing supplied reference rate, distance applies only to vehicle mileage |
| Bas/ Teksi | Manually entered fare per trip × Trips per Month × Months | Manual fare, no distance factor |
| Bayaran Tambang Kereta Api | Manually entered fare per trip × Trips per Month × Months | Manual fare, approved handling of source blank rate |
| Bayaran Tambang Kapal/ Bot | Manually entered fare per trip × Trips per Month × Months | Manual fare, approved handling of source blank rate |
| Bayaran Kapal Terbang | Manually entered fare per trip × Trips per Month × Months | Manual fare, approved handling of source blank rate |

Kekerapan means trips per month; Bilangan Bulan is the month multiplier. Bas/ Teksi and vehicle mileage remain under unchanged Kategori1 **Elaun Perjalanan, Bayaran Tambang Bas dan Teksi**, distinguished by a component selector; the supplied 1.00 reference is used for mileage, while the separately approved manual fare applies to Bas/ Teksi. No CSV value is overwritten or reclassified as zero. These approvals supersede only prior unresolved row/formula/manual-fare behaviour, not all remaining field applicability.

Preserve the six Kategori1 labels, three distinct Anggaran identities, existing mappings and all83 source rows including blank rates and special C. No C→VU7 or other absent-grade/rate mapping is approved. Two-decimal grouped/tabular money is display formatting, not a new financial rounding policy. OS14000's specific half-up rounding approval is not automatically extended to travel.

Still unresolved: mapping the approved component selector to canonical Kategori2 versus a source-only field, Kategori3 applicability, inclusive date-duration convention and its independence from claimable days/nights, detailed conditional applicability for day/night factors, and any eligibility/caps or missing reference match policy. No application implementation or source CSV change in this checkpoint; full programme inheritance/official integration remains pending. AD008 stays Draft.


## Owner-approved component mapping and duration —18November2026

Owner explicitly approved both remaining proposals: Kategori2 is conditional, with **Elaun Perjalanan Kenderaan** or **Bas/ Teksi** only under existing Kategori1 **Elaun Perjalanan, Bayaran Tambang Bas dan Teksi**; Kategori2 is unused/blank for the other five categories. **Kategori3 is Inactive**. Preserve all six Kategori1 labels. Tempoh is inclusive calendar days (end minus start plus one), informational and independent of manually entered claimable days, accommodation nights and months. Dates remain subject to existing Kitaran-year constraints. Bilangan Hari is contextual: claimable days for meals, nights for accommodation; no extra day/night multiplier on mileage/fares. The latest dated approvals supersede earlier unresolved category/duration/formula status only; no new rates, C mapping, entitlement/caps or financial rounding policy. Official integration/programme inheritance and implementation verification remain pending.


## Batch048 source-editor implementation in progress

Implement latest approved travel formulas and Kategori2/duration decisions across three existing variants; all83 source rows/mappings retained. No C mapping or absent grade/rate fabricated. Verification/merge/local acceptance pending. Official financial/programme inheritance integration remains deferred; AD008 stays Draft. See Batch048 development record. Historical unresolved statements above are superseded only by the explicit dated owner approvals.


## Batch048 merged checkpoint —18November2026

[PR107](https://github.com/muhdmuizz/sistem-abm/pull/107) merged at `77219f10ccedf872c13e3ec4b087469d0de498b0`. Separate Umum/Sukan/Kokurikulum source editors share83 unchanged rate rows and exact PPN010801/010803/010803 mappings. One named officer/component per source; approved monthly formulas, conditional vehicle/Bas-Teksi Kategori2, inactive Kategori3, manually entered day/night/distance/month factors and independent inclusive Kitaran-year duration. No special C or absent shared-grade mapping guessed. Exact manual fares retained; no new financial rounding policy. Pertukaran remains separate/unchanged.

Verified `9eebfba5da255e5908fa3368221e91d74d62d0e7`, CI44338401373/job134921288935: **586 tests /7,484 assertions**, PHP8.4.27/PostgreSQL18.7 isolated testing DB, build/exact-money/full Chromium passed. Artifact14442019034:594 PNGs/22 travel;11 travel+one Pertukaran capture individually reviewed. Batch048 records failed/intermediate runs, hint correction and visual/manual limits. No migration/seed/CSV/catalogue change; local sync/financial/device acceptance and prior pending migration/import remain unconfirmed. Official totals/outputs/completeness/submission/export, programme inheritance/context integration and unresolved special-grade policies remain pending. AD008 stays Draft; Steps02/03 in progress. Keep Server running separately from Updates. Consolidated spreadsheet UI refinement follows baseline OS editors, then shared proposals.
