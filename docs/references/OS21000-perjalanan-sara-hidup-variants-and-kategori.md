# OS21000 — Perjalanan Sara Hidup (Umum, Sukan dan Kokurikulum)

**Recorded:** 12 November 2026. **Status:** User-approved reference and classification decisions; not implementation.

## OS variant labels

The legacy Excel workbook uses **three separate sheets**. The user approved treating **Umum**, **Sukan** and **Kokurikulum** as distinguishing **Anggaran (OS) labels**, **not Kategori1 options**:

| Kod Objek | Label Anggaran (OS) | Kategori1 |
| --- | --- | --- |
| 21000 | Perjalanan Sara Hidup (Umum) | Kategori Tuntutan |
| 21000 | Perjalanan Sara Hidup (Sukan) | Kategori Tuntutan |
| 21000 | Perjalanan Sara Hidup (Kokurikulum) | Kategori Tuntutan |

This follows Reference 04's identification of Anggaran (OS) by `Kod Objek + canonical Butiran Perbelanjaan`. The three OS variants may refer to the **same 83-row rate reference**, without duplicating reference values for each variant. **Do not add a required Umum/Sukan/Kokurikulum dropdown as Kategori1.** The three-sheet arrangement is a legacy workbook detail, not a mandate to duplicate the rate catalogue.

## Revised user reference

**Authoritative supplied revision:** User-provided revised 4-column table on 12 November 2026, superseding the earlier legacy table.

**Machine-readable data:** [OS21000-perjalanan-sara-hidup-revised-kadar.csv](OS21000-perjalanan-sara-hidup-revised-kadar.csv) — **83 rows**, preserving all source grade/category/location/rate values, including blank Kadar cells. Internal repeated whitespace in text labels was trimmed for CSV readability, with no semantic label or rate correction.

**Columns:** Gred; Kategori Tuntutan; Lokasi Urusan Rasmi; Kadar.

**Kategori1** corresponds to the distinct `Kategori Tuntutan` labels in the supplied table:

1. Makanan dan Minuman
2. Penginapan
3. Bayaran Tambang Kereta Api
4. Elaun Perjalanan, Bayaran Tambang Bas dan Teksi
5. Bayaran Tambang Kapal/ Bot
6. Bayaran Kapal Terbang

## Approved boundaries and outstanding reconciliation

- Use the **revised values as supplied**. Do not override, 'repair', or augment them based on external interpretation of WP 1.4 without a subsequent user decision.
- Preserve **blank Kadar** cells as blank: no automatic zero, formula, or requirement is approved solely from their presence.
- The user previously approved shared **Jawatan + full SSPA Gred** input where personnel attributes are relevant. The source's special grade `C` is preserved without guessing a mapping.
- **User-confirmed Kod Aktiviti:** Umum = `010801`, Sukan = `010803`, and Kokurikulum = `010803`. Sukan and Kokurikulum share the same Kod Aktiviti; the legacy Excel sheets separated them because of Excel limitations, not because they use different activity codes. The new Kokurikulum mapping is explicitly annotated as user-confirmed, rather than presented as a row transcribed from the original source mapping.
- No decisions made here about rate formulas, vehicle bands, unit basis, other WP 1.4 categories, eligibility, Laravel, PostgreSQL, or Architecture Decisions.

## Catalogue reconciliation — 12 November 2026

The confirmed OS21000 variant labels are now represented in the master `anggaran-os-catalogue.csv`:

- **Umum:** the existing `Perjalanan Sara Hidup` entry is now labelled `Perjalanan Sara Hidup (Umum)`. Its verified PPN Kod Aktiviti `010801` is retained. In `kod-aktiviti-anggaran-os-mapping.csv`, only the **canonical** description is updated; the **original** source text remains `Perjalanan Sara Hidup`.
- **Sukan:** the existing `Perjalanan Sara Hidup (Sukan)` entry and PPN Kod Aktiviti `010803` remain unchanged.
- **Kokurikulum:** user explicitly confirmed Kod Aktiviti `010803`, the **same as Sukan**. The master catalogue labels it as an eligible reference, and `kod-aktiviti-anggaran-os-mapping.csv` adds a **user-confirmed** PPN mapping row. Its `Butiran Perbelanjaan (Original)` is deliberately blank, because this distinct Kokurikulum row was confirmed in conversation rather than present in the supplied 103-row legacy mapping.

The catalogue contains **32 entries rather than 31**, reflecting the separately approved Kokurikulum label. The mapping CSV now contains **104 rows**: **103 consolidated legacy-source mappings plus one explicitly user-confirmed Kokurikulum mapping**. The two OS labels (Sukan and Kokurikulum) share Kod Aktiviti `010803`, but remain distinguishable OS labels, and both use the same revised 83-row reference. No additional activity code was invented. No rate calculations, application/database changes, or AD changes have been made.


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
