# OS22000 — Historical file name: corrected ownership of Pertukaran rate reference

**CORRECTED 14 NOVEMBER 2026.** This file's original identity as a separate Anggaran `OS22000 — Pengangkutan Barang-barang` and its old interpretation of the category hierarchy were superseded by the approved unified OS22000 decision. The owner has now directly re-supplied and identified the five-column 126-row source as **OS22000 Pertukaran**, **not** the simple `KATEGORI1 = Pengangkutan Barang` branch.

**Read [OS22000-unified-kategori-decisions.md](OS22000-unified-kategori-decisions.md) first**; that is the authoritative current structure. The CSV retains its existing filename [OS22000-pengangkutan-barang-kadar.csv](OS22000-pengangkutan-barang-kadar.csv) for data integrity and historical traceability, but the filename must not determine the branch.

## Verified OS22000 Pertukaran reference

Source fields: **LOKASI PERTUKARAN**, **BUJANG/ KELUARGA**, **GRED**, **KATEGORI TUNTUTAN**, **KADAR**.

- **Lokasi Pertukaran:** Semenanjung; Sabah/ Sarawak/ WP Labuan.
- **Bujang/ Keluarga:** Bujang; Keluarga.
- **Kategori Tuntutan:** Tambang Pengangkutan Barang; Pemberian Pindahan; Elaun Berpindah; Belanja Pelbagai.
- **Gred:** Separate lookup attribute, **not a category**. Preserve original grades including `C`; do not invent Gred 11 or interpolate absent rows.
- **Kadar:** Preserve prescribed source values and intentional blanks. The CSV contains 126 source rows.

For OS22000's `KATEGORI1 = Pertukaran/ Penempatan`, the approved current category fields are:

| Category | Value / source |
| --- | --- |
| KATEGORI1 | Pertukaran/ Penempatan |
| KATEGORI2 | Bujang/ Keluarga |
| KATEGORI3 | Kategori Tuntutan |

**Approved OS22000 column mapping (14 November 2026):** `Lokasi Pertukaran` is **not a category**. It is mapped to canonical Excel **column 17 — LOKASI URUSAN RASMI**, with the **OS-specific displayed label `LOKASI PERTUKARAN`** for the Pertukaran/ Penempatan path. Its two source choices remain `Semenanjung` and `Sabah/ Sarawak/ WP Labuan`. Canonical export column header is unchanged. Column 17 is active for this path; no column-17 decision is made for the separate `Pengangkutan Barang` path. `GRED` remains a separate reference attribute.

## Reference Kadar modes

| KATEGORI3 / Kategori Tuntutan | Treatment |
| --- | --- |
| Tambang Pengangkutan Barang | Mandatory manually entered Kadar (blank in supplied rate table) |
| Pemberian Pindahan | Automatic lookup from source by location, Bujang/ Keluarga and applicable Gred |
| Elaun Berpindah | Automatic lookup from source by location, Bujang/ Keluarga and applicable Gred |
| Belanja Pelbagai | Mandatory manually entered Kadar (blank in supplied rate table) |

The earlier four-row [OS22000-pengangkutan-barang-kadar-modes.csv](OS22000-pengangkutan-barang-kadar-modes.csv) is **historical rate-mode reference for these source claim types**; its old header/Butiran identification must not be used to redefine the new simple Pengangkutan Barang branch.

For `KATEGORI1 = Pengangkutan Barang` (the *other* branch of the single OS22000 Anggaran), **no KATEGORI2 or later levels exist**; the user manually enters both `Program/ Aktiviti/ Tujuan` and `Kadar`. **Do not apply the grade-based rate table to that branch.**

No additional calculation formulas, 27-column mapping, eligibility or application implementation are approved by this source correction.
