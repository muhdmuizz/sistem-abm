# OS22000 — One Anggaran (OS), two TUJUAN paths

**Owner-confirmed initial structure:** 12 November 2026. **TUJUAN selector revision:** 14 November 2026. **Reference correction:** 14 November 2026 based on the owner's newly supplied 126-row `LOKASI PERTUKARAN / BUJANG/ KELUARGA / GRED / KATEGORI TUNTUTAN / KADAR` table. **Status:** Authoritative OS22000 reference, documentation only.

## Scope and mandatory source separation

**OS22000 is ONE Anggaran (OS), with exactly two TUJUAN choices in canonical column 04 — NAMA PROGRAM/ AKTIVITI/ TUJUAN**. The TUJUAN selector determines the applicable path; **it is not KATEGORI1**. Never create two OS22000 Anggaran identities from historical catalogue rows.

| TUJUAN (column 04) | Confirmed configuration |
| --- | --- |
| **Pertukaran/ Penempatan** | Use the owner's verified 126-row OS22000 Pertukaran rate reference, currently stored under its historical filename [OS22000-pengangkutan-barang-kadar.csv](OS22000-pengangkutan-barang-kadar.csv). **Lokasi Pertukaran uses canonical Excel column 17 (LOKASI URUSAN RASMI), displayed as LOKASI PERTUKARAN for this OS22000 path; it is not a Kategori level.** `Bujang/ Keluarga` and `Kategori Tuntutan` are KATEGORI1 and KATEGORI2. `Gred` remains an independent personnel/rate-lookup attribute. |
| **Pengangkutan Barang** | No Kategori levels. **KADAR** is mandatory manual input; no grade-based rate lookup. The earlier requirement for user-entered descriptive `Program/ Aktiviti/ Tujuan` remains recorded but its placement after column 04 becomes the path selector is **unresolved**, not silently removed or assigned a new field. Do not populate this path with additional choices from the old 126-row table. |

For **Pertukaran/ Penempatan**, the verified hierarchy is:

| System field | Approved source / values |
| --- | --- |
| TUJUAN (column 04) | Pertukaran/ Penempatan |
| KATEGORI1 (column 11) | Bujang/ Keluarga: Bujang; Keluarga |
| KATEGORI2 (column 12) | Kategori Tuntutan: Tambang Pengangkutan Barang; Pemberian Pindahan; Elaun Berpindah; Belanja Pelbagai |
| KATEGORI3 (column 13) | No approved values for this path; do not populate by inference |
| GRED (separate field) | Source Gred from verified rate rows, including special C; do not invent mappings or missing grade values |

**Approved OS-specific column 17 mapping (14 November 2026):** The canonical 27-column Excel field **17 — LOKASI URUSAN RASMI** is **Active** for `TUJUAN = Pertukaran/ Penempatan` and captures source **Lokasi Pertukaran**, with choices `Semenanjung` and `Sabah/ Sarawak/ WP Labuan`. In this path's spreadsheet-like editor, **rename the displayed field label to `LOKASI PERTUKARAN`**. Keep the official Excel column header `LOKASI URUSAN RASMI` unchanged so the 27-column export contract is preserved. This is an OS-specific display label, not a global rename, and does not add another category.

**Rate behaviour in the existing reference:** For Pemberian Pindahan and Elaun Berpindah, use the grade/location/status-matched supplied Kadar. For Tambang Pengangkutan Barang and Belanja Pelbagai, Kadar is mandatory user input where the supplied reference is blank. Preserve all source figures and blank values. The exact formulas and remaining column applicability are not approved by this correction; the TUJUAN, KATEGORI1–2 and Lokasi Pertukaran column mappings above are approved.

### Explicit exclusion: misattributed eight-row data

**Do not use** [OS22000-pertukaran-penempatan-kategori.csv](OS22000-pertukaran-penempatan-kategori.csv), or the eight-row category table in [OS22000-pertukaran-penempatan-kategori.md](OS22000-pertukaran-penempatan-kategori.md), to configure OS22000. Those rows contain `Bertukar Ke Luar Kawasan Pejabat`, `Tidak Berpindah Rumah / Berpindah Rumah`, `Elaun Makan`, `Bayaran Sewa Hotel/ Elaun Lojing`, etc. The owner has explicitly identified them as **OS21000 Tuntutan Perbelanjaan Pertukaran** material, not the OS22000 Pertukaran rate reference. They remain in Git only for provenance, **superseded and non-operative**. Do not append them as KATEGORI2–KATEGORI5.

### Historical filename clarification

The 126-row source file name `OS22000-pengangkutan-barang-kadar.csv` is **historically misleading**. Its actual content (five source columns) was resupplied by the owner on 14 November 2026 as the reference for **OS22000 Pertukaran**. The exact rows and amounts are preserved; **the source filename is not evidence that its rate lookup belongs to TUJUAN = Pengangkutan Barang**. The existing [OS22000-pengangkutan-barang-reference.md](OS22000-pengangkutan-barang-reference.md) is similarly a historical file name, and has been marked with corrected provenance.

## Other retained decisions

- Selection of a TUJUAN path does not depend on Kod Aktiviti.
- The older decision required a user-entered descriptive `Program/ Aktiviti/ Tujuan` for Pengangkutan Barang. With column 04 now reserved as the TUJUAN path selector, **how that descriptive requirement is retained is pending reconciliation**. Do not overwrite the selector with free text, drop the requirement silently, add an unapproved export column, or import OS14000 options.
- Jawatan and Gred remain independent shared reference attributes. Do not fabricate Gred 11 where the verified rate reference has none.
- The two OS22000 labels in the legacy master catalogue and mapping records do **not** supersede the one-OS/two-path decision. Reconcile the eventual import carefully without inventing codes.
- No new eligibility, quantities, rounding or formulas are authorised by this correction.

## Implementation boundary

This correction updates OS22000 documentation only. The historical source CSV, existing amount values, catalogue, application code, DB and Excel export are not modified; implementation and testing remain outstanding. **For all future OS22000 work, read this document first.**
