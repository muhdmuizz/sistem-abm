# OS26000 — Bekalan Bahan Mentah dan Bahan-bahan Untuk Penyelenggaraan dan Pembaikan

**Owner-confirmed:** 12 November 2026. **Scope:** Reference data, category mapping and approved **2027 unsubsidised fuel-planning assumptions** only. This is not application/DB implementation.

## Category hierarchy

| Level | Source |
| --- | --- |
| **Kategori1** | Kategori (Pukal / Bukan Pukal) |
| **Kategori2** | Subkategori |
| **Kategori3** | Jenis Bekalan |
| **Kadar** | Kos Per Liter/ Unit; reference-populated according to the selected three-level category path |

The source contains **42 records**: Pukal **3** (Petrol RON95: 1; Diesel: 2); Bukan Pukal **39** (Alat Ganti Elektrik: 8; Kenderaan: 5; Lain-lain: 9; Peralatan: 8; Perpaipan: 9).

The user directed that `Column1` be **removed**, because it is just a concatenation of Subkategori and Jenis Bekalan. **It is not a category or field in the cleaned reference.** Internal repeated whitespace was normalised in category/item labels, with all 42 source items and amounts otherwise retained.

## Reference files (separate historical values from planning rates)

- [OS26000-bahan-mentah-kadar-source.csv](OS26000-bahan-mentah-kadar-source.csv) — **42 original numerical costs** as supplied, with `Column1` excluded.
- [OS26000-bahan-mentah-kategori-kadar.csv](OS26000-bahan-mentah-kategori-kadar.csv) — **42 proposed planning Kadar values**: the five petrol/diesel items use the owner's agreed **2027 unsubsidised estimates**; all 37 other entries retain the supplied numerical costs.
- [OS26000-bahan-mentah-2027-fuel-planning.csv](OS26000-bahan-mentah-2027-fuel-planning.csv) — **two fuel-type assumptions** for reuse, distinct from observed pump prices.

## Approved fuel planning assumptions

The owner agreed to budget the **next calendar year, 2027**, using forecast **unsubsidised** costs:

| Fuel | Forecast planning Kadar for 2027 |
| --- | ---: |
| **RON95** | **RM4.00 per litre** |
| **Diesel** | **RM5.00 per litre** |

These are **budget assumptions, not official or guaranteed future retail prices**. They supersede the legacy costs **for 2027 planning only** in the following existing source entries:

| Category path (Kategori2 → Kategori3) | Original (RM) | 2027 planning (RM/L) |
| --- | ---: | ---: |
| Petrol (RON95) → Kereta | 3.20 | 4.00 |
| Diesel → Van | 4.40 | 5.00 |
| Diesel → Bas | 4.40 | 5.00 |
| Alat Ganti Kenderaan → Diesel (Bukan Pukal) | 2.95 | 5.00 |
| Alat Ganti Kenderaan → Petrol (Bukan Pukal) | 2.05 | 4.00 |

**Important:** Five fuel-related source entries are affected, not four. Each is linked to the same relevant 2027 fuel-type assumption; no duplicated independent rate policy is introduced.

All **37 non-fuel** records retain their supplied Kadar. In the planning table the `AUTO_REFERENCE` mode indicates that the Kadar is populated from the matching reference row, consistent with the earlier proposal subsequently accepted by the owner.

## Period boundary and completeness

- The values are approved for **2027 planning assumptions** only. The owner has separately discussed an active **ABM 2028** cycle; this record does **not** claim a distinct 2028 price forecast or automatically relabel the 2027 estimate as a 2028 observed price.
- Every applicable Kadar must be present under [the shared mandatory Kadar rule](shared-mandatory-kadar-rule.md). This does not introduce a new separate quantity, unit selection, calculation formula, price effective-date switching algorithm, or rounding rule.
- Do not override the user-supplied 42 costs with later spot prices, subsidies or external interpretations without owner approval.

## Scope

The master catalogue, legacy Kod Aktiviti mapping, Architecture Decisions, Laravel and PostgreSQL are unchanged.

## Owner-approved OS26000 Anggaran specification — 14 November 2026

**Scope:** Approved specification for the shared OS26000 category hierarchy, its distinct existing Kod Aktiviti mappings, both Pukal/Bukan Pukal applicability paths, 2028 planning fuel rates, dynamic measurement units and Bukan Pukal new-item proposal governance. This section supersedes earlier *pending* statements about these subjects, but preserves the historical 2027-only source provenance above. **Documentation only:** no application, database, catalogue CSV or reference record was modified.

### Identity and category hierarchy

- Retain **KATEGORI1 = Pukal / Bukan Pukal**, **KATEGORI2 = existing Subkategori**, **KATEGORI3 = existing Jenis Bekalan**, and dependent lookup of **KADAR** by the complete selected path.
- Preserve the existing **distinct Kod Aktiviti** for Pukal and Bukan Pukal, including their existing authorisation boundaries. Sharing the category hierarchy does not merge Kod Aktiviti or authorise cross-activity access.
- Existing baseline comprises **42 items**, **3 Pukal** and **39 Bukan Pukal**. Newly Superadmin-approved Bukan Pukal items extend the central catalogue in future; they are not included in the historical baseline of 42.

### Canonical 30-column applicability

The 30 canonical headers and positions are defined by [AD-008](../architecture/AD-008-os-catalogue-calculation-framework.md). The following field positions apply *separately* to the two KATEGORI1 paths:

| Status | Pukal | Bukan Pukal |
| --- | --- | --- |
| Active | 01, 02, 03, 04, 14, 15, 16, 17, 19, 21, 23, 26, 27, 28 | 01, 02, 03, 04, 14, 15, 16, 17, 19, 21, 27, 28 |
| Inactive | 05, 06, 07, 08, 09, 10, 11, 12, 13, 18, 20, 22, 24, 25 | 05, 06, 07, 08, 09, 10, 11, 12, 13, 18, 20, 22, 23, 24, 25, 26 |
| Deferred | 29, 30 | 29, 30 |
| Count | 14 Active / 14 Inactive / 2 Deferred | 12 Active / 16 Inactive / 2 Deferred |

- 01 **OBJEK SEBAGAI** from selected Anggaran; 02 **JPNT/ PPD** from authorised Bidaan PTJ; 03 **SEKTOR/ SEKOLAH** controlled Tempat Bertugas under the PTJ; 28 **KATEGORI SUMBER PERUNTUKAN** from Bidaan Aktiviti Kewangan.
- 04 **NAMA PROGRAM/ AKTIVITI/ TUJUAN** is Active for both paths: purpose may identify vehicle type, brand and/or registration (illustration: `Van Hiace TTB 1234`). Do not mandate a particular vehicle format for non-vehicle items.
- 14/15/16 are active dependent **KATEGORI1/KATEGORI2/KATEGORI3**; 17 **UNIT** auto-populates from the approved item mapping below; 19 **BIL. UNIT** is the user-entered quantity with a contextual UI label (e.g., `BIL. LITER`); 21 **KADAR** auto-populates from the matching approved reference; 27 **ANGGARAN JUMLAH SETAHUN** is calculated.
- **Pukal only:** 23 **KEKERAPAN** and 26 **BILANGAN BULAN** are Active; those fields are Inactive for Bukan Pukal.
- 08–10 are Inactive and export **blank**, reserved for OS24000 Mesin Fotostat. 29–30 remain Deferred under the existing Program/ Aktiviti rules.
- Dynamic on-screen labels **do not rename canonical Excel headers** or alter column order.

### Approved calculations

- **Pukal:** `ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KADAR × KEKERAPAN × BILANGAN BULAN`.
- **Bukan Pukal:** `ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KADAR`.
- User-entered quantities and Pukal multipliers are distinct from auto-populated UNIT and KADAR. Preserve AD-008's decimal arithmetic, stable Butiran IDs, source-to-output traceability and immutable submitted snapshots. No extra rounding or eligibility policy is inferred.

### Approved UNIT reference mapping — 42 baseline items

| KATEGORI1 | KATEGORI2 | KATEGORI3 | UNIT |
| --- | --- | --- | --- |
| Pukal | Petrol (RON95) | Kereta | Liter |
| Pukal | Diesel | Van | Liter |
| Pukal | Diesel | Bas | Liter |
| Bukan Pukal | Alat Ganti Elektrik | Alat Ganti Kipas | Unit |
| Bukan Pukal | Alat Ganti Elektrik | Bateri | Unit |
| Bukan Pukal | Alat Ganti Elektrik | Lampu Kalimantang (LED) | Unit |
| Bukan Pukal | Alat Ganti Elektrik | Lampu Kalimantang (18W) | Unit |
| Bukan Pukal | Alat Ganti Elektrik | Lampu Kalimantang (36W) | Unit |
| Bukan Pukal | Alat Ganti Elektrik | Lampu Kalimantang (Pendaflour) | Unit |
| Bukan Pukal | Alat Ganti Elektrik | Soket | Unit |
| Bukan Pukal | Alat Ganti Elektrik | Starter Lampu/ Choke Lampu | Unit |
| Bukan Pukal | Alat Ganti Kenderaan | Diesel Exhaust Fluid (AdBlue) | Liter |
| Bukan Pukal | Alat Ganti Kenderaan | Diesel (Bukan Pukal) | Liter |
| Bukan Pukal | Alat Ganti Kenderaan | Petrol (Bukan Pukal) | Liter |
| Bukan Pukal | Alat Ganti Kenderaan | Tyre Wax | Botol |
| Bukan Pukal | Alat Ganti Kenderaan | Syampu Kereta | Botol |
| Bukan Pukal | Alat Ganti Lain-lain | Atap | Keping |
| Bukan Pukal | Alat Ganti Lain-lain | Baja | Beg |
| Bukan Pukal | Alat Ganti Lain-lain | Batang Kayu | Batang |
| Bukan Pukal | Alat Ganti Lain-lain | Batu Bata Merah | Biji |
| Bukan Pukal | Alat Ganti Lain-lain | Batu Bata Putih | Biji |
| Bukan Pukal | Alat Ganti Lain-lain | Cat | Tin |
| Bukan Pukal | Alat Ganti Lain-lain | Papan Lapis | Keping |
| Bukan Pukal | Alat Ganti Lain-lain | Pasir (20KG) | Beg |
| Bukan Pukal | Alat Ganti Lain-lain | Simen (10KG) | Beg |
| Bukan Pukal | Alat Ganti Peralatan | Bingkai Tingkap Nako | Unit |
| Bukan Pukal | Alat Ganti Peralatan | Cermin Nako (Sekeping) | Keping |
| Bukan Pukal | Alat Ganti Peralatan | Engsel Pintu | Unit |
| Bukan Pukal | Alat Ganti Peralatan | Penutup Longkang | Unit |
| Bukan Pukal | Alat Ganti Peralatan | Pintu | Unit |
| Bukan Pukal | Alat Ganti Peralatan | Selak Pintu | Unit |
| Bukan Pukal | Alat Ganti Peralatan | Siling | Keping |
| Bukan Pukal | Alat Ganti Peralatan | Tombol Pintu | Unit |
| Bukan Pukal | Alat Ganti Perpaipan | Batang Paip | Batang |
| Bukan Pukal | Alat Ganti Perpaipan | Culvert | Unit |
| Bukan Pukal | Alat Ganti Perpaipan | Gutter (1 kaki) | Kaki |
| Bukan Pukal | Alat Ganti Perpaipan | Kepala Paip | Unit |
| Bukan Pukal | Alat Ganti Perpaipan | Paip Getah | Meter |
| Bukan Pukal | Alat Ganti Perpaipan | Pam Tandas | Unit |
| Bukan Pukal | Alat Ganti Perpaipan | Penutup Tandas | Unit |
| Bukan Pukal | Alat Ganti Perpaipan | Sinki | Unit |
| Bukan Pukal | Alat Ganti Perpaipan | Stopcock | Unit |

**UNIT** is automatically determined from the exact KATEGORI1/KATEGORI2/KATEGORI3 record; users do not replace the approved UNIT through normal Anggaran entry. The **BIL. UNIT** on-screen input label follows the unit (e.g., `BIL. LITER`, `BIL. KEPING`, `BIL. UNIT`) while the stored/exported column remains `BIL. UNIT`.

### Fuel KADAR approved for ABM 2028

- Owner explicitly approved **RON95 RM4.00/L** and **Diesel RM5.00/L** for **ABM Kitaran 2028 planning**, across the applicable five fuel-related reference entries: Pukal Petrol (RON95) → Kereta; Pukal Diesel → Van/Bas; Bukan Pukal Alat Ganti Kenderaan → Diesel (Bukan Pukal)/Petrol (Bukan Pukal).
- These numerical assumptions originate in the earlier **2027 planning source**; their **use for ABM 2028** is a separate subsequent owner approval. They are **not verified 2028 retail pump prices**. The 37 non-fuel reference costs remain as supplied. Do not invent a generic year-dependent rate-switching algorithm.

### Cadangan Item Baharu — Bukan Pukal only

1. **Penyedia Anggaran** may submit a new **Bukan Pukal** Jenis Bekalan proposal under an **existing** KATEGORI2 Subkategori, stating proposed item name, UNIT, KADAR and Justifikasi; KATEGORI1 is fixed to Bukan Pukal. Keep proposing user's identity/PTJ and timestamps.
2. **Superadmin is the sole reviewer/approver.** Track proposal status **Menunggu Semakan → Diluluskan / Ditolak**, proposal details, reviewer, decision, timestamps and audit history. Check duplicates against the entire central approved catalogue and pending proposals, within the relevant category path.
3. While **Menunggu Semakan**, the proposal is **not published globally**. The proposer can use it **provisionally in Draf Anggaran only**, with clear pending/provisional labelling and a provisional amount using its proposed KADAR. Pending items must not enter submitted or finalised financial amounts; submission of affected drafts is blocked.
4. When **Diluluskan**, the approved record (Subkategori, Jenis Bekalan, UNIT, KADAR) is **added to the central OS26000 Bukan Pukal database reference and becomes globally selectable by every authorised OS26000 Bukan Pukal user**, regardless of proposing PTJ. Previously affected draft entries are reconciled to the approved reference and its approved values, revalidated and recalculated before any normal submission. **Approval does not automatically submit an Anggaran.**
5. When **Ditolak**, no reference item is published; rejected entries cannot be newly selected, and affected drafts must be corrected/replaced before submission. Retain audit history and decision.
6. This facility **creates proposals for new items only**, never silently edits existing approved catalogue records, alters Pukal items, bypasses Kod Aktiviti permissions or creates an independent financial transaction layer.

**Implementation boundary:** These are approved future application/database behaviours. They are not a claim that the current Laravel or PostgreSQL system already enforces them. Reference CSVs and the 42 original records are unchanged by this documentation commit.


## Batch 041 implementation checkpoint — 17 November 2026

OS26000 baseline source spreadsheets merged in [PR #93](https://github.com/muhdmuizz/sistem-abm/pull/93) at `cafacd3a86debdd30a0f67fec0e2c9558d21a909`: all 42 approved items, separate Pukal/Bukan Pukal permissions, derived units/rates and exact draft previews. Verified at `0680c16ec8b92ffccd7839f5abbee2bee6d70274`: 544 tests / 5,705 assertions plus build/exact-money/Chromium. No migration or seed added; local sync and prior migration/import remain pending. New-item proposal governance and official financial workflow remain deferred; AD-008 remains Draft and Steps 02/03 in progress. Full evidence and limits: [Batch 041](../development/batch-041-os26000-spreadsheets.md).

[Machine-readable unit mapping](OS26000-bahan-mentah-unit.csv) transcribes the approved unit table exactly; original cost/source CSVs are unchanged. Fuel assumptions apply only to explicitly approved 2027/2028 planning contexts; other-year fuel previews are withheld.


## Owner-approved single-card correction — 17 November 2026

Supersedes Batch 041's separate OS identities/permissions: OS26000 uses one retained catalogue card and Anggaran, with Pukal/Bukan Pukal selected per Kategori1 row. Preserve all nine distinct Kod Aktiviti mappings and original wording; no code merger or fabricated Pendidikan Awal Pukal mapping. Available paths follow the exact current activity mappings; one retained OS permission controls access. Frequency/months apply only to Pukal and must not survive a switch to Bukan Pukal. Approved formulas, 42 reference paths, units and rates are unchanged. Targeted obsolete development-card cleanup is authorised; canonical and unrelated drafts remain. Implementation/evidence: [Batch 042](../development/batch-042-unified-os26000-spreadsheet.md).


## Batch 042 implementation checkpoint — 17 November 2026

OS26000 single-card correction merged in [PR #95](https://github.com/muhdmuizz/sistem-abm/pull/95) at `ae59695121bf72c1adc061ada09f1693187c513a`. One retained identity supports Pukal/Bukan Pukal per row, preserving nine distinct activity codes and all 42 approved references/units/rates. Supersedes Batch 041's separate identities. Catalogue 30/28 eligible/two excluded; mappings 105/103 eligible. Tested `4113f1985ecd26153ae39e50e39a55a263d933d9`: 548 tests / 5,719 assertions plus build/exact-money/Chromium. Targeted obsolete development-card migration and normal reference import required; local acceptance pending. Proposal governance and official financial integration remain deferred; AD-008 stays Draft, Steps 02/03 in progress. Evidence and limits: [Batch 042](../development/batch-042-unified-os26000-spreadsheet.md).
