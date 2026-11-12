# OS22000 — Pengangkutan Barang-barang: reference and category decisions

> **Historical reference notice (12 November 2026):** The later [unified OS22000 decision](OS22000-unified-kategori-decisions.md) supersedes this file's former *separate-OS identity and category-level interpretation*. Preserve the data below as supplied; it must not be used to populate the new Kategori1 = Pengangkutan Barang branch automatically. The owner placed the earlier reference material under Pertukaran/ Penempatan. This notice makes no data or calculation changes.

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

## Confirmed rate modes and SSPA grade treatment — 12 November 2026

The user expressly confirmed the following rules for **OS22000 — Pengangkutan Barang-barang**.

| Kategori3 — Kategori Tuntutan | Kadar treatment |
| --- | --- |
| Tambang Pengangkutan Barang | **Mandatory user-entered Kadar** |
| Pemberian Pindahan | **Lookup prescribed Kadar** using the supplied reference by location, status and applicable Gred |
| Elaun Berpindah | **Lookup prescribed Kadar** using the supplied reference by location, status and applicable Gred |
| Belanja Pelbagai | **Mandatory user-entered Kadar** |

The **six source rows with blank Kadar** (two Tambang Pengangkutan Barang and four Belanja Pelbagai) intentionally remain blank in the source CSV. The applicable **user-input** rule is encoded separately in [OS22000-pengangkutan-barang-kadar-modes.csv](OS22000-pengangkutan-barang-kadar-modes.csv). Blank does **not** mean zero or a predefined amount. No additional free-text description requirement has been approved for these OS22000 categories.

**Gred 11:** The user clarified that the **SSPA Pengurusan dan Profesional tier** runs directly from **Gred 10 to Gred 12**, omitting Gred 11. Accordingly **do not fabricate a Gred 11 rate or interpolate one** for this source reference. This clarification concerns that tier and does **not** establish a general claim that Gred 11 is absent from every SSPA service group. Jawatan and Gred remain separate from all category levels, using Reference 07 as appropriate.

No other eligibility, caps, approval steps or financial formulas are established by this decision.

## Commit scope

This document records approved Kategori1–Kategori3 mapping, rate modes, and the supplied SSPA Pengurusan dan Profesional Gred 11 clarification. The **126-row source CSV**, master OS catalogue, Reference 07, architecture decisions, Laravel code and PostgreSQL remain unchanged. A separate four-row rate-mode CSV records the confirmed manual-versus-prescribed Kadar rule.
