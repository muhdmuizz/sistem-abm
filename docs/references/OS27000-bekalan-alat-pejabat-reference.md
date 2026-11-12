# OS27000 — Bekalan Alat Pejabat: approved categories and Kadar

**Owner-confirmed:** 12 November 2026. **Scope:** Reference-data consolidation and automatic Kadar lookup only; no implementation.

## Category structure

| Level | Source column | Confirmed behaviour |
| --- | --- | --- |
| **Kategori1** | KATEGORI | User selection |
| **Kategori2** | BEKALAN | Depends on Kategori1 |
| **Kadar** | KOS PER UNIT | Mandatory, **automatically populated from selected reference** |

No Kategori3 or separate Unit field was supplied; do not invent one.

## Reference preservation and cleaning

- [Full original reference CSV](OS27000-bekalan-alat-pejabat-source.csv): **196 source records**, retaining supplied labels and rates, including both conflicting duplicate prices. Source row numbers identify the original item order.
- [Cleaned selectable reference CSV](OS27000-bekalan-alat-pejabat-kategori-kadar.csv): **194 uniquely identified items** (by normalised Kategori1 + Kategori2); duplicated whitespace is normalised, and original item names are not otherwise rewritten.
- **Kategori1 composition (original rows):** Dakwat/ Toner Mesin Pencetak **22**; Alat Tulis Pejabat **158**; Lain-lain **8**; Kertas **8**.
- Every item has a prescribed reference Kadar; there are no source blanks.

## Owner-approved conflicting duplicate resolution

Where the **same normalised Kategori1 and Kategori2** appeared twice with different Kadar, the user expressly selected the **higher Kadar**:

| Kategori2 | Higher Kadar retained | Lower source Kadar excluded from selectable list |
| --- | ---: | ---: |
| Klip Kertas Segitiga Saiz 31MM | **RM1.20** | RM0.95 |
| Tali Pengikat Rafia Saiz 100 Meter | **RM36.00** | RM20.00 |

The excluded lower values remain in the original source CSV for audit. Do not automatically merge similar but **nonidentical** item names (e.g. differently worded products) or infer any replacement prices. This higher-price selection is specific to the two conflicts above, **not** a universal rate policy for every OS.

## Kadar completeness

Kadar is retrieved automatically from the confirmed OS27000 reference row; it remains mandatory under [the shared mandatory Kadar rule](shared-mandatory-kadar-rule.md). No independently supplied formula, quantity input rules, rounding rules, effective-date rule or new price updates are introduced.

## Boundaries

This reference covers **OS27000 — Bekalan Alat Pejabat**. No decision is implied for other OS27000 variants such as Pakaian Seragam or Bekalan dan Bahan-bahan Lain. The master OS catalogue, Kod Aktiviti mapping, Architecture Decisions, Laravel and PostgreSQL are unchanged.
