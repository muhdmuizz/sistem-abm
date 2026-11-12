# OS26000 — Bekalan Bahan Mentah dan Bahan-bahan Untuk Penyelenggaraan dan Pembaikan

**Owner-confirmed:** 12 November 2026. **Scope:** Reference data, category mapping and approved **2027 unsubsidised fuel-planning assumptions** only. This is not application/DB implementation.

## Category hierarchy

| Level | Source |
| --- | --- |
| **Kategori1** | Kategori (Pukal / Bukan Pukal) |
| **Kategori2** | Subkategori |
| **Kategori3** | Jenis Bekalan |
| **Kadar** | Kos Per Liter/ Unit; reference-populated according to the selected three-level category path |

The source contains **42 records**: Pukal **3** (Petrol RON95: 1; Diesel: 2); Bukan Pukal **39** (Alat Ganti Elektrik: 8; Kenderaan: 5; Lain-lain: 9; Peralatan: 8; Perpaipan: 9).

The user directed that `Column1` be **removed**, because it is just a concatenation of Subkategori and Jenis Bekalan. **It is not a category or field in the cleaned reference.** Internal repeated whitespace was normalised in category/item labels, with all 42 source items and amounts otherwise retained.

## Reference files (separate historical values from planning rates)

- [OS26000-bahan-mentah-kadar-source.csv](OS26000-bahan-mentah-kadar-source.csv) — **42 original numerical costs** as supplied, with `Column1` excluded.
- [OS26000-bahan-mentah-kategori-kadar.csv](OS26000-bahan-mentah-kategori-kadar.csv) — **42 proposed planning Kadar values**: the four petroleum/diesel items use the owner's agreed **2027 unsubsidised estimates**; all 38 other entries retain the supplied numerical costs.
- [OS26000-bahan-mentah-2027-fuel-planning.csv](OS26000-bahan-mentah-2027-fuel-planning.csv) — **two fuel-type assumptions** for reuse, distinct from observed pump prices.

## Approved fuel planning assumptions

The owner agreed to budget the **next calendar year, 2027**, using forecast **unsubsidised** costs:

| Fuel | Forecast planning Kadar for 2027 |
| --- | ---: |
| **RON95** | **RM4.00 per litre** |
| **Diesel** | **RM5.00 per litre** |

These are **budget assumptions, not official or guaranteed future retail prices**. They supersede the legacy costs **for 2027 planning only** in the following existing source entries:

| Category path (Kategori2 → Kategori3) | Original (RM) | 2027 planning (RM/L) |
| --- | ---: | ---: |
| Petrol (RON95) → Kereta | 3.20 | 4.00 |
| Diesel → Van | 4.40 | 5.00 |
| Diesel → Bas | 4.40 | 5.00 |
| Alat Ganti Kenderaan → Diesel (Bukan Pukal) | 2.95 | 5.00 |
| Alat Ganti Kenderaan → Petrol (Bukan Pukal) | 2.05 | 4.00 |

**Important:** Five fuel-related source entries are affected, not four. Each is linked to the same relevant 2027 fuel-type assumption; no duplicated independent rate policy is introduced.

All **37 non-fuel** records retain their supplied Kadar. In the planning table the `AUTO_REFERENCE` mode indicates that the Kadar is populated from the matching reference row, consistent with the earlier proposal subsequently accepted by the owner.

## Period boundary and completeness

- The values are approved for **2027 planning assumptions** only. The owner has separately discussed an active **ABM 2028** cycle; this record does **not** claim a distinct 2028 price forecast or automatically relabel the 2027 estimate as a 2028 observed price.
- Every applicable Kadar must be present under [the shared mandatory Kadar rule](shared-mandatory-kadar-rule.md). This does not introduce a new separate quantity, unit selection, calculation formula, price effective-date switching algorithm, or rounding rule.
- Do not override the user-supplied 42 costs with later spot prices, subsidies or external interpretations without owner approval.

## Scope

The master catalogue, legacy Kod Aktiviti mapping, Architecture Decisions, Laravel and PostgreSQL are unchanged.
