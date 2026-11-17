# OS27000 — Bekalan Alat Pejabat: approved categories and Kadar

**Owner-confirmed:** 12 November 2026. **Scope:** Reference-data consolidation and automatic Kadar lookup only; no implementation.

## Category structure

| Level | Source column | Confirmed behaviour |
| --- | --- | --- |
| **Kategori1** | KATEGORI | User selection |
| **Kategori2** | BEKALAN | Depends on Kategori1 |
| **Kadar** | KOS PER UNIT | Mandatory, **automatically populated from selected reference** |

No Kategori3 or separate Unit field was supplied; do not invent one.

## Reference preservation and cleaning

- [Full original reference CSV](OS27000-bekalan-alat-pejabat-source.csv): **196 source records**, retaining supplied labels and rates, including both conflicting duplicate prices. Source row numbers identify the original item order.
- [Cleaned selectable reference CSV](OS27000-bekalan-alat-pejabat-kategori-kadar.csv): **194 uniquely identified items** (by normalised Kategori1 + Kategori2); duplicated whitespace is normalised, and original item names are not otherwise rewritten.
- **Kategori1 composition (original rows):** Dakwat/ Toner Mesin Pencetak **22**; Alat Tulis Pejabat **158**; Lain-lain **8**; Kertas **8**.
- Every item has a prescribed reference Kadar; there are no source blanks.

## Owner-approved conflicting duplicate resolution

Where the **same normalised Kategori1 and Kategori2** appeared twice with different Kadar, the user expressly selected the **higher Kadar**:

| Kategori2 | Higher Kadar retained | Lower source Kadar excluded from selectable list |
| --- | ---: | ---: |
| Klip Kertas Segitiga Saiz 31MM | **RM1.20** | RM0.95 |
| Tali Pengikat Rafia Saiz 100 Meter | **RM36.00** | RM20.00 |

The excluded lower values remain in the original source CSV for audit. Do not automatically merge similar but **nonidentical** item names (e.g. differently worded products) or infer any replacement prices. This higher-price selection is specific to the two conflicts above, **not** a universal rate policy for every OS.

## Kadar completeness

Kadar is retrieved automatically from the confirmed OS27000 reference row; it remains mandatory under [the shared mandatory Kadar rule](shared-mandatory-kadar-rule.md). No independently supplied formula, quantity input rules, rounding rules, effective-date rule or new price updates are introduced.

## Boundaries

This reference covers **OS27000 — Bekalan Alat Pejabat**. No decision is implied for other OS27000 variants such as Pakaian Seragam or Bekalan dan Bahan-bahan Lain. The master OS catalogue, Kod Aktiviti mapping, Architecture Decisions, Laravel and PostgreSQL are unchanged.


## Owner-approved 30-column Anggaran specification — 14 November 2026

**Scope:** OS27000 **Bekalan Alat Pejabat only**. This section records later owner decisions and supersedes earlier statements that no quantities or calculation had been approved. It does **not** alter Pakaian Seragam, Bekalan dan Bahan-bahan Lain or any other Kod Aktiviti.

### Category and reference boundary

- Retain KATEGORI1 (four existing source categories) and dependent KATEGORI2 (194 selectable items); KATEGORI3 is Inactive.
- KADAR is mandatory and automatically populated from the selected existing reference item, preserving all published rates and the two previously approved duplicate-price resolutions.
- **UNIT is Inactive** (canonical column 17); do not introduce per-item dynamic UNIT mapping. Quantities are understood as **Unit**, with the fixed displayed input label **BIL. UNIT** (canonical column 19), which is Active. This is a UI/input convention, not a modification to original prices or their historical source unit basis.
- The existing 194-item source remains unchanged. Superadmin-approved new items will extend the database catalogue when the future workflow is implemented.

### Canonical 30-column applicability

| Status | Columns | Count |
| --- | --- | ---: |
| Active | 01, 02, 03, 14, 15, 19, 21, 27, 28 | 9 |
| Conditional | 04, 05, 06, 07 | 4 |
| Inactive | 08, 09, 10, 11, 12, 13, 16, 17, 18, 20, 22, 23, 24, 25, 26 | 15 |
| Deferred | 29, 30 | 2 |

- 01 **OBJEK SEBAGAI** from selected Anggaran; 02 **JPNT/ PPD** from authorised Bidaan PTJ; 03 **SEKTOR/ SEKOLAH** selected Tempat Bertugas under the PTJ; 28 **KATEGORI SUMBER PERUNTUKAN** from Bidaan Aktiviti Kewangan.
- Conditional 04 **NAMA PROGRAM/ AKTIVITI/ TUJUAN**, 05 **TARIKH MULA**, 06 **TARIKH TAMAT**, 07 **TEMPOH** apply only when sourced from associated **Program/ Aktiviti**. Name and dates are inherited; TEMPOH is derived under the existing programme duration rules. For direct Anggaran entries, these columns are Inactive; no manual dates or new programme fields are introduced.
- 08–10 remain blank for this OS under the global 30-column contract; 29–30 are Deferred to the Program/ Aktiviti pathway. Preserve canonical Excel column positions and headers.

### Annual amount calculation

**ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KADAR.**

BIL. UNIT is the entered quantity and KADAR is the required automatic reference value. Derived totals must follow the existing AD-008 decimal arithmetic, stable Butiran ID, recalculation and historical submission snapshot rules. Do not invent additional multipliers, rounding policy or rate updates.

### Cadangan Item Baharu — identical governance to OS26000 Bukan Pukal

1. **Penyedia Anggaran** can propose a new OS27000 Bekalan Alat Pejabat KATEGORI2 item under an **existing** KATEGORI1, providing new item name, **UNIT = Unit** as a fixed convention (not an active canonical UNIT input), proposed KADAR and Justifikasi, with proposing user/PTJ and timestamps recorded.
2. **Superadmin only** reviews and approves or rejects. Track Menunggu Semakan / Diluluskan / Ditolak, the proposal and decision history, reviewer, timestamps, and an audit log. Check duplicates within the selected category against the **global approved central catalogue and pending proposals**.
3. **Menunggu Semakan:** the proposer may use the item provisionally **only in Draf Anggaran**, with a visibly provisional KADAR and calculated amount. An affected Anggaran **cannot be submitted** while it contains a pending item; provisional values do not enter submitted or finalised financial totals.
4. **Diluluskan:** publish the new approved item and KADAR to the **central database reference catalogue**, globally available to **all authorised OS27000 Bekalan Alat Pejabat users** regardless of originating PTJ. Existing affected drafts reconcile to approved values and revalidate/recalculate before submission. **Never submit automatically** as a consequence of reference approval.
5. **Ditolak:** never publish to the central reference; prohibit new use and require correction or removal of affected draft items before submission. Retain decision history.
6. The workflow **adds new items**, not edits to existing approved reference records, does not bypass Kod Aktiviti permissions, and does not create a second financial transaction layer.

**Change-control:** Documentation approval only. The Laravel application, PostgreSQL database, original 194 selectable reference CSV items and financial exporter remain unchanged by this commit.


## Batch 044 implementation checkpoint — 17 November 2026

OS27000 Bekalan Alat Pejabat baseline spreadsheet merged in [PR #97](https://github.com/muhdmuizz/sistem-abm/pull/97) at `721fe9216b9b0d0f84c806f9188599bed7c9c274`: four categories/194 unchanged approved items, exact quantity × read-only rate, five existing activity mappings and no inactive direct-entry programme/Unit/Kategori3 inputs. Verified `b096af2cac1885fef4e3252905549f3ec363862b`: 555 tests / 6,183 assertions plus build/exact-money/Chromium. No new migration/seed; local sync/financial/device acceptance pending. Cadangan Item Baharu is deferred until remaining OS editors and consolidated UI refinement finish. Programme inheritance/official financial integration remain pending; AD-008 stays Draft and Steps 02/03 in progress. OS26000 purpose removal was retracted and never merged; it remains unchanged. Evidence and limits: [Batch 044](../development/batch-044-office-supplies-spreadsheet.md).
