# OS29000 — Perkhidmatan Iktisas dan Perkhidmatan Lain Yang Dibeli dan Hospitaliti (Umum)

**Owner-approved reference and Excel mapping:** 12 November 2026. **Scope:** documentation of supplied categories, dedicated Gred Penceramah/ Fasilitator and mandatory Kadar treatment only. No wizard, database, or export implementation.

## Field mapping and separation from universal personnel reference

| Field | Meaning |
| --- | --- |
| **Kategori1** | Source `KATEGORI` |
| **Kategori2** | Source `PERKHIDMATAN`, selected within Kategori1 |
| **Kategori3** | **Not required** by this OS29000 (Umum) reference; do not repurpose it for Gred Penceramah/ Fasilitator |
| **Gred Penceramah/ Fasilitator** | Dedicated classification and dedicated **existing Excel output column**; applicable only to `Bayaran Penceramah` and `Bayaran Fasilitator` |
| **Jawatan & Gred** | Separate universal personnel reference (Reference 07); **must not be conflated** with Gred Penceramah/ Fasilitator |
| **Kadar** | Mandatory; automatic fixed lookup for 18 rows or user-entered for 4 rows |

The existing approved 27-column Excel output includes a dedicated `GRED PENCERAMAH/ FASILITATOR` column (previously identified as column 15). **No new Excel column is requested.** Do not move the speaker classification into `KATEGORI3` merely to avoid the dedicated column.

## Penceramah/ Fasilitator classification and Kadar

These labels belong to the dedicated speaker/facilitator classification, **not** Reference 07 `Gred`. Preserve source grouping/range labels exactly (especially `Kumpulan P&P Gred 11-13`).

| Gred Penceramah/ Fasilitator | Penceramah (RM) | Fasilitator (RM) |
| --- | ---: | ---: |
| Kumpulan Pengurusan Tertinggi | 300.00 | 100.00 |
| Kumpulan P&P Gred 14 | 200.00 | 100.00 |
| Kumpulan P&P Gred 11-13 | 150.00 | 90.00 |
| Kumpulan P&P Gred 9-10 | 120.00 | 80.00 |
| Kumpulan Sokongan | 80.00 | 60.00 |

No payment unit (per hour/session etc.) was supplied; do not invent one.

## Other supplied services

| Kategori1 | Kategori2 | Kadar (RM) | Mode |
| --- | --- | ---: | --- |
| Bayaran Yuran Bengkel/ Kursus/ Taklimat/ Seminar | Bayaran Yuran | *(blank)* | User input |
| Keraian, Hospitaliti, Honorarium dll. | Makan Minum (Mesyuarat) | 8.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Makan Minum (Program) | 18.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Makan Minum (Majlis Persaraan) | 60.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Hadiah Penghargaan Pesara | 600.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Medal | 10.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Piala | 50.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Dobi | *(blank)* | User input |
| Keraian, Hospitaliti, Honorarium dll. | Percetakan (Banner) | 150.00 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Percetakan (Sijil) | 1.90 | Automatic |
| Keraian, Hospitaliti, Honorarium dll. | Percetakan | *(blank)* | User input |
| Keraian, Hospitaliti, Honorarium dll. | Lain-lain | *(blank)* | User input |

## Reference files and boundaries

- [Supplied-source reference, standardised whitespace](OS29000-umum-kadar-source.csv): 22 rows; blanks preserved, not set to zero.
- [Reference choices and rate modes](OS29000-umum-kategori-kadar.csv): 22 rows, **18 automatic**, **4 mandatory user-entered** Kadar.
- Mandatory applicable rates follow the [shared mandatory Kadar rule](shared-mandatory-kadar-rule.md).
- Do not add eligibility checks, grade-to-speaker-group conversion rules, rate units, price changes, or extra categories without separate decisions.
- No changes to existing Excel column layout, OS catalogue, Kod Aktiviti mapping, other OS29000 variants, application or database.
