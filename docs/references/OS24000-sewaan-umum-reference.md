# OS24000 — Sewaan (Umum): category and Kadar reference

**Owner-confirmed:** 12 November 2026. **Scope:** Reference decisions and structured data only; no application/DB changes.

## Identity

This is the reference for **OS24000 — Sewaan (Umum)**. **Sewaan (Mesin Fotostat)** has a separate reference to be supplied; this document does not assume its categories or rates.

## Approved category mapping

| Level | Field |
| --- | --- |
| **Kategori1** | **Kategori Sewaan** (`Berkontrak`, `Tidak Berkontrak`) |
| **Kategori2** | **Kategori Peralatan** |
| **Kategori3** | **Jenis Peralatan** |
| **Kadar** | **Mandatory user-entered** |

The [machine-readable category CSV](OS24000-sewaan-umum-kategori.csv) contains **64 combinations**: the same 32 equipment selections for each rental arrangement. `Kategori3` for **Tanah** is left blank in both source combinations; **do not invent a Jenis Peralatan** or force a fabricated category value.

Normalise only repeated interior whitespace from the supplied table (e.g. `Tidak   Berkontrak`, `Alat   Kelengkapan`). The two supplied category-wording variants for broadcasting equipment are retained as distinct source labels when applicable:

- **Berkontrak:** Kategori2 `Sewa Alat Kelengkapan Penyiaran`; Kategori3 `Alat Kelengkapan Penyiaran`.
- **Tidak Berkontrak:** Kategori2 `Alat Kelengkapan Penyiaran`; Kategori3 `Alat Kelengkapan Penyiaran`.

## Mandatory Kadar

**Kadar for Sewaan (Umum) is provided by the user** for every applicable record, and must be present for the item to be complete. No fixed rate, Gred-based lookup, formula, rounding or additional fields are supplied in this source.

The owner's **universal decision** is recorded separately in [the shared Kadar rule](shared-mandatory-kadar-rule.md): every applicable Kadar is mandatory, whether derived automatically from approved references/calculations or entered by the user. This does **not** introduce new rate rules for other OS or override their documented input modes.

## Boundaries

This file documents the 64 supplied category paths, with no change to the source's actual options. It does not decide whether Sewaan (Umum) and Sewaan (Mesin Fotostat) share a unified OS identity or occupy separate catalogue identities. Master OS catalogue, Kod Aktiviti mapping, ADs, Laravel and PostgreSQL are unchanged.
