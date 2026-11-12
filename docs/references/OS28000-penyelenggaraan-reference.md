# OS28000 — Penyelenggaraan dan Pembaikan Kecil Yang Dibeli: reference and Kadar decisions

**Confirmed by owner:** 12 November 2026. **Scope:** Reference-data and input rules only; no financial formula or application implementation.

## Category structure

| Level | Source field |
| --- | --- |
| **Kategori1** | **Kategori** |
| **Kategori2** | **Peralatan** (where supplied) |
| **Kadar** | **Mandatory** for all applicable entries: fixed reference value where prescribed; otherwise entered by user |

The reference has **10 rows**, comprising **four Kategori1 values**, with six vehicle-maintenance options, one Aset Alih, two fire-safety options and one Tebang Pokok.

| Kategori1 | Kategori2 | Prescribed Kadar (RM) | Kadar mode |
| --- | --- | ---: | --- |
| Penyelenggaraan Kenderaan Jabatan | Sedan | — | User-entered |
| Penyelenggaraan Kenderaan Jabatan | Pacuan Empat Roda | — | User-entered |
| Penyelenggaraan Kenderaan Jabatan | Van | — | User-entered |
| Penyelenggaraan Kenderaan Jabatan | Koster (Coaster) | — | User-entered |
| Penyelenggaraan Kenderaan Jabatan | Bas | — | User-entered |
| Penyelenggaraan Kenderaan Jabatan | Bot | — | User-entered |
| Penyelenggaraan Aset Alih | Aset Alih | — | User-entered |
| Penyelenggaraan Alat Pemadam Api | Alat Pemadam Api | 100.00 | Automatic reference lookup |
| Penyelenggaraan Alat Pemadam Api | Sistem Pencegahan Kebakaran | — | User-entered |
| Tebang Pokok | *(blank)* | 1,500.00 | Automatic reference lookup |

## Machine-readable references

- [Original Kadar reference](OS28000-penyelenggaraan-kadar-source.csv) — 10 supplied entries; the original eight blank Kadar cells remain **blank**, not RM0.
- [Selection and Kadar modes](OS28000-penyelenggaraan-kategori-kadar.csv) — the same 10 category combinations, with eight `USER_INPUT_KADAR` entries and two `FIXED_REFERENCE` entries.

The owner approved this **mixed Kadar behaviour**. All Kadar remain mandatory pursuant to the [shared mandatory Kadar rule](shared-mandatory-kadar-rule.md). No free-text description, additional rate, extra category, quantity formula or rounding policy is inferred.

**Tebang Pokok:** The source has an empty Peralatan field. Do not invent a Kategori2 value or require users to select a fabricated one.

## Source handling and scope

Only repeated internal whitespace has been normalised for readability; reference wording and numerical values otherwise follow the supplied source. This documentation does **not** modify the master Anggaran (OS) catalogue, Kod Aktiviti mapping, Architecture Decisions, Laravel or PostgreSQL. This is not a declaration that the complete OS28000 wizard or 27-column output mapping is finished.
