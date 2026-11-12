# OS27000 — Pakaian Seragam: reference and Kadar rules

**Confirmed:** 12 November 2026. **Scope:** Reference-data and selection decisions only, not application implementation.

## Approved category and lookup structure

- **Kategori1 — Jenis Bekalan:** `Pakaian Seragam` or `Kasut`.
- **Jawatan:** Separate personnel reference field, **never Kategori2 or any category level**.
- **Kategori2 and subsequent levels:** Not applicable to this supplied reference.
- **Kadar:** Mandatory, automatically populated from the matching `Kategori1 + Jawatan` combination.

| Kategori1 | Jawatan | Kadar (RM) |
| --- | --- | ---: |
| Pakaian Seragam | Pembantu Khidmat Am | 230.00 |
| Pakaian Seragam | Pembantu Makmal | 230.00 |
| Kasut | Pembantu Khidmat Am | 150.00 |
| Kasut | Pembantu Makmal | 250.00 |

**Machine-readable source:** [OS27000-pakaian-seragam-kadar.csv](OS27000-pakaian-seragam-kadar.csv), four supplied combinations.

## Shared personnel reference

Use the single [Reference 07 — Jawatan, Gred, Gaji dan KGT](REF-07-jawatan-gred-gaji-kgt.md) for Jawatan selection and relevant personnel attributes; **do not create an independent category or duplicate the shared Jawatan dataset**.

Both `Pembantu Khidmat Am` and `Pembantu Makmal` are present in the 131-row shared Jawatan/Gred source. The four OS27000 rates depend on **Jawatan and Kategori1**, not an additionally inferred Gred rate or salary formula. No eligibility restriction or detailed grade filter has been approved from the supplied table.

## Rate and boundaries

- Preserve the four specified Kadar values. No new prices, units, quantities, formulas or extra options are introduced.
- All applicable Kadar values are mandatory in accordance with [the universal Kadar rule](shared-mandatory-kadar-rule.md).
- This covers the **Pakaian Seragam reference only**. Other OS27000 references remain separately documented. No decision to restructure the master OS catalogue is implied.
- The master OS catalogue, Kod Aktiviti mapping, ADs, Laravel and PostgreSQL remain unchanged.
