# OS22000 — Pengangkutan Barang-barang: reference and category decisions

**Recorded:** 12 November 2026.  
**Status:** Confirmed category mapping and previously supplied reference data. Documentation only.

## Anggaran (OS) identity

- **Kod Objek:** `22000`.
- **Butiran Perbelanjaan:** `Pengangkutan Barang-barang`.
- This is a **distinct Anggaran (OS)** from `22000 — Tuntutan Perbelanjaan Pertukaran/ Penempatan Baharu/ Penempatan Semula`. The same Kod Objek does not merge their reference identities (Reference 04).

## Approved category mapping

| Category level | Source field |
| --- | --- |
| **Kategori1** | **Lokasi Pertukaran** |
| **Kategori2** | **Bujang/ Keluarga** |
| **Kategori3** | **Kategori Tuntutan** |

These category levels were explicitly agreed after the source data was provided.

**Jawatan and Gred are not categories at any level.** They remain separate personnel-reference attributes, using the shared `Jawatan, Gred, Gaji dan KGT` reference (Reference 07) where applicable. The applicable source `Gred` is used independently for Kadar lookup and is **not** Kategori4.

## Source values

The existing machine-readable dataset is [OS22000-pengangkutan-barang-kadar.csv](OS22000-pengangkutan-barang-kadar.csv), with **126 records**, retaining the original fields `Lokasi Pertukaran`, `Bujang/ Keluarga`, `Gred`, `Kategori Tuntutan`, `Kadar (RM)`.

- **Kategori1 — Lokasi Pertukaran:** `Semenanjung`; `Sabah/ Sarawak/ WP Labuan`.
- **Kategori2 — Bujang/ Keluarga:** `Bujang`; `Keluarga`.
- **Kategori3 — Kategori Tuntutan:** `Tambang Pengangkutan Barang`; `Pemberian Pindahan`; `Elaun Berpindah`; `Belanja Pelbagai`.
- **Gred:** Legacy grade as supplied, including special `C`. Do not infer a numeric equivalence for `C`.
- **Kadar:** The supplied rate, when present, is indexed by the category combination and grade; no new values are introduced.

## Unresolved source gaps — preserve without assumptions

- **Gred 11** is missing from the provided rate rows. No replacement value has been approved.
- **Six records** have blank `Kadar`. Do not treat them automatically as zero or user-entered rates; the rule for these blanks has not yet been agreed.
- Do not invent eligibility, caps, approval steps or financial formulas beyond the source data.

## Commit scope

This document records previously agreed facts and the approved Kategori1–Kategori3 mapping. The source CSV, master OS catalogue, Reference 07, architecture decisions, Laravel code and PostgreSQL remain unchanged.
