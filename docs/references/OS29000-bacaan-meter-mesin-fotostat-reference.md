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
