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

For OS22000's `KATEGORI1 = Pertukaran/ Penempatan`, the approved current field mapping is:

| Category | Value / source |
| --- | --- |
| KATEGORI1 (canonical column 11) | Pertukaran/ Penempatan |
| KATEGORI2 (canonical column 12) | Bujang/ Keluarga |
| KATEGORI3 (canonical column 13) | Kategori Tuntutan |

**Approved OS22000 column mapping (14 November 2026):** `Lokasi Pertukaran` is **not a category**. It is mapped to canonical Excel **column 17 — LOKASI URUSAN RASMI**, with the **OS-specific displayed label `LOKASI PERTUKARAN`** for the Pertukaran/ Penempatan path. Its two source choices remain `Semenanjung` and `Sabah/ Sarawak/ WP Labuan`. Canonical export column header is unchanged. Column 17 is active for this path; no column-17 decision is made for the separate `Pengangkutan Barang` path. `GRED` remains a separate reference attribute.

## Reference Kadar modes

| KATEGORI3 / Kategori Tuntutan | Treatment |
| --- | --- |
| Tambang Pengangkutan Barang | Mandatory manually entered Kadar (blank in supplied rate table) |
| Pemberian Pindahan | Automatic lookup from source by location, Bujang/ Keluarga and applicable Gred |
| Elaun Berpindah | Automatic lookup from source by location, Bujang/ Keluarga and applicable Gred |
| Belanja Pelbagai | Mandatory manually entered Kadar (blank in supplied rate table) |

The earlier four-row [OS22000-pengangkutan-barang-kadar-modes.csv](OS22000-pengangkutan-barang-kadar-modes.csv) is **historical rate-mode reference for these source claim types**; its old header/Butiran identification must not be used to redefine the new simple Pengangkutan Barang branch.

For `KATEGORI1 = Pengangkutan Barang` (the *other* path of the single OS22000 Anggaran), **no KATEGORI2 or subsequent levels exist**. **TUJUAN (column 04) and KADAR (column 18) are mandatory user-entered inputs**. TUJUAN is not the path selector. **Do not apply the grade-based rate table to this path.**

**14 November 2026 final correction (supersedes earlier TUJUAN-selector text):** KATEGORI1 (column 11) is the approved path selector, with values `Pertukaran/ Penempatan` and `Pengangkutan Barang`. Under Pertukaran/ Penempatan, KATEGORI2 = Bujang/ Keluarga and KATEGORI3 = Kategori Tuntutan; column 17 remains LOKASI PERTUKARAN in the editor. Under Pengangkutan Barang, TUJUAN (column 04) is manual and mandatory. No additional calculation formulas, other column applicability, eligibility or application implementation are approved.

## Batch 040 checkpoint — 17 November 2026

[PR #91](https://github.com/muhdmuizz/sistem-abm/pull/91) merged at `a45ec936cb67d565e72446729830fd12f96047f2`. OS22000 now uses one retained catalogue identity and two Kategori1 paths: Pertukaran/ Penempatan (family, claim, Lokasi Pertukaran and personnel/reference lookup) and Pengangkutan Barang (manual Tujuan/Kadar). Both exact draft previews = Kadar × 1; inactive fields are cleared/rejected. No missing grade 11, special C/VU7 mapping or absent location is fabricated. Existing source rates and canonical export headers remain unchanged.

Targeted migration removes only the obsolete OS22000 identity's development graph/overrides, retains current/unrelated records and mapping provenance/IDs, increments affected bid revisions and aborts atomically for non-draft Bidaan. Retains all seven existing codes. Catalogue **31 (29 eligible/two excluded)**; mappings **105 (103 eligible)**. Ordinary migration and idempotent OsReferenceSeeder import are required locally; no broad reset or financial seed.

Tested `457cc94cd01d40280edea8fac2a77936d0d01d13`; [CI 44038128200](https://github.com/muhdmuizz/sistem-abm/actions/runs/44038128200), job 133885875932: **537 tests / 5,435 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/exact-money/Chromium passed. Thirteen OS22000 and one existing-editor capture reviewed from 446 PNGs (20 OS22000); Batch 040 records resolved fixture/count failures and review limits.

Local sync/migration/import/financial/device acceptance remains pending; earlier unconfirmed statuses unchanged. Use Updates separately from Server; no routine server restart. Official outputs/totals/completeness/submission, programme inheritance and export remain deferred. AD-008 stays Draft; Steps 02/03 in progress. Complete remaining OS editors before consolidated UI refinement.
