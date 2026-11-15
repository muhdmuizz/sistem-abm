# OS27000 — Pakaian Seragam: reference and Kadar rules

**Confirmed:** 12 November 2026. **Scope:** Reference-data and selection decisions only, not application implementation.

## Approved category and lookup structure

- **Kategori1 — Jenis Bekalan:** `Pakaian Seragam` or `Kasut`.
- **Jawatan:** Separate personnel reference field, **never Kategori2 or any category level**.
- **Kategori2 and subsequent levels:** Not applicable to this supplied reference.
- **Kadar:** Mandatory, automatically populated from the matching `Kategori1 + Jawatan` combination.

| Kategori1 | Jawatan | Kadar (RM) |
| --- | --- | ---: |
| Pakaian Seragam | Pembantu Khidmat Am | 230.00 |
| Pakaian Seragam | Pembantu Makmal | 230.00 |
| Kasut | Pembantu Khidmat Am | 150.00 |
| Kasut | Pembantu Makmal | 250.00 |

**Machine-readable source:** [OS27000-pakaian-seragam-kadar.csv](OS27000-pakaian-seragam-kadar.csv), four supplied combinations.

## Shared personnel reference

Use the single [Reference 07 — Jawatan, Gred, Gaji dan KGT](REF-07-jawatan-gred-gaji-kgt.md) for Jawatan selection and relevant personnel attributes; **do not create an independent category or duplicate the shared Jawatan dataset**.

Both `Pembantu Khidmat Am` and `Pembantu Makmal` are present in the 131-row shared Jawatan/Gred source. The four OS27000 rates depend on **Jawatan and Kategori1**, not an additionally inferred Gred rate or salary formula. No eligibility restriction or detailed grade filter has been approved from the supplied table.

## Rate and boundaries

- Preserve the four specified Kadar values. No new prices, units, quantities, formulas or extra options are introduced.
- All applicable Kadar values are mandatory in accordance with [the universal Kadar rule](shared-mandatory-kadar-rule.md).
- This covers the **Pakaian Seragam reference only**. Other OS27000 references remain separately documented. No decision to restructure the master OS catalogue is implied.
- The master OS catalogue, Kod Aktiviti mapping, ADs, Laravel and PostgreSQL remain unchanged.


## Owner-approved 30-column Anggaran specification — 14 November 2026

**Scope:** Only **OS27000 Pakaian Seragam**, retaining its separate existing Kod Aktiviti. This subsequent owner approval supersedes earlier reference-only statements that UNIT, quantities and formulas were not approved. It does not change other OS27000 variants or the four original reference-rate combinations.

### Canonical Perbelanjaan applicability

| Status | Canonical 30-column positions | Count |
| --- | --- | ---: |
| Active | 01, 02, 03, 12, 14, 17, 19, 21, 23, 27, 28 | 11 |
| Inactive | 04, 05, 06, 07, 08, 09, 10, 11, 13, 15, 16, 18, 20, 22, 24, 25, 26 | 17 |
| Deferred | 29, 30 | 2 |

- 01 **OBJEK SEBAGAI** comes from selected Anggaran; 02 **JPNT/ PPD** from authorised Bidaan PTJ; 03 **SEKTOR/ SEKOLAH** from controlled Tempat Bertugas; 28 **KATEGORI SUMBER PERUNTUKAN** from Bidaan Aktiviti Kewangan.
- 12 **JAWATAN** uses the existing shared personnel reference and remains separate from category levels; 14 **KATEGORI1** is `Pakaian Seragam` or `Kasut`; 15–16 are Inactive. 21 **KADAR** is mandatory, automatically looked up from **KATEGORI1 + JAWATAN** using the four unchanged combinations above.
- 17 **UNIT** is Active and **fixed to `Pasang` for both Pakaian Seragam and Kasut**.
- 19 **BIL. UNIT** is Active, displayed in the editor as **BIL. PENGISIAN**, user-entered.
- 23 **KEKERAPAN** is Active, displayed in the editor as **Bil. Pasang**, fixed to **2** and not editable (owner correction, 15 November 2026).
- 27 **ANGGARAN JUMLAH SETAHUN** is calculated. Columns 08–10 are Inactive and exported blank; columns 29–30 remain Deferred.
- Editor-specific labels do **not** rename the canonical Excel headers or alter the approved 30-column position/order.

### Approved financial calculation

**ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KEKERAPAN × KADAR** — i.e., **BIL. PENGISIAN × BIL. PASANG × KADAR**.

Illustration (not an additional rate policy): 5 pengisian × 2 pasang × RM230 = **RM2,300**. Follow the established AD-008 deterministic decimal arithmetic, independent Butiran identities, reference attribution and immutable submitted snapshots. No unapproved eligibility, rounding, rate revisions or new category choices are inferred.

**Change control:** This is approved specification **only**; no Laravel implementation, database migration, source reference CSV changes or Excel implementation are claimed. AD-008 remains Draft.

## Batch 027 implementation checkpoint — 15 November 2026

[PR #64](https://github.com/muhdmuizz/sistem-abm/pull/64) merged at `dffc2c44ebcf50f757cb1588488b6924f0b95c55`. Pakaian Seragam source drafts now use existing PPN/010808 mapping, shared Reference 07 Jawatan, four approved category/job rates, fixed Pasang and exact Bil. Pengisian × Bil. Pasang × Kadar preview. Actual title-case headers are implemented across OS25000/SBK/TASKA/OS28000/Pakaian Seragam, preserving canonical Excel/reference headings and accounting-style money.

Tested application head `bea2bed2522e2b9fb816fb1a6a0a13d9d52bed11`; [CI 43665138171](https://github.com/muhdmuizz/sistem-abm/actions/runs/43665138171): 452 tests / 3626 assertions, PHP 8.4.27 / PostgreSQL 18.7 isolated testing database; build/formatter/Chromium and representative desktop/mobile review passed. See [Batch 027](../development/batch-027-uniform-headers.md) for evidence limits. Local acceptance pending. No migration/seed or new policy. Other OS27000 variants, official output/totals/completeness and programme inheritance remain pending; Steps 02/03 remain in progress. AD-008 remains Draft.

## Owner correction — 15 November 2026

**Bil. Pasang must default to and remain fixed at 2**, with editing restricted and server enforcement. This supersedes earlier user-entered KEKERAPAN wording and applies to the existing Pakaian Seragam/Kasut definition. Formula remains Bil. Pengisian × **2** × Kadar. Existing stored draft inputs are retained on read; current preview follows the correction and explicit audited save upgrades the source. No bulk rewriting of historical/submitted data or new rate/eligibility rule. Batch 028 implements this correction; verification passed, local acceptance pending. See checkpoint below.

## Batch 028 checkpoint — 15 November 2026

[PR #66](https://github.com/muhdmuizz/sistem-abm/pull/66) merged into dev at `496dbfb0d9cb30f982bc02f8470f6b46773b5269`. Bil. Pasang is now fixed at **2** on the screen and server, superseding the previous editable source input. Valid v1 drafts remain readable; read does not rewrite stored input, current preview uses two, and explicit audited save upgrades the row to v2. OS27000 Touch ’n Go (canonical Bekalan dan Bahan-bahan Lain) now has source editing under existing PPN/010809, Pendidikan Menengah/030304 and Pengurusan Asrama/030316 mappings, with exact Bil. Unit × Kadar Bulanan × Bilangan Bulan previews. No frequency/category/unit input or new rate policy.

Tested application head `c780d2dbb6cb5dcb40d10e6e7e7b78fcb7033a28`; [CI 43669111898](https://github.com/muhdmuizz/sistem-abm/actions/runs/43669111898): **458 tests / 3701 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB, build/formatter/Chromium and representative desktop/mobile review passed. [Batch 028](../development/batch-028-fixed-pairs-touchngo.md) records review limits. No migration/seed; local acceptance pending. Official outputs/totals/completeness, programme inheritance, remaining OS and submission remain pending; Steps 02/03 remain in progress and AD-008 remains Draft.

## Owner correction — applicable Jawatan only, 15 November 2026

OS27000 Pakaian Seragam Jawatan selections must be restricted to **Pembantu Khidmat Am** and **Pembantu Makmal**, the posts covered by the existing four category/job rates. This explicitly supersedes the earlier full-shared-list/unpriced-new-draft behaviour. Continue using shared Reference 07 as the personnel source, with the rate reference defining this OS's applicable subset. Jawatan is separate from categories; no Gred or other new eligibility rule. Bil. Pasang remains fixed at **2**. Preserve old unrelated-job drafts and flag them for replacement without read-time rewriting; new unrelated selections are rejected. Batch 029 implements this correction; verification passed and local acceptance pending. See checkpoint below.

## Batch 029 implementation checkpoint — 15 November 2026

[PR #68](https://github.com/muhdmuizz/sistem-abm/pull/68) merged at `4c5a112ff31892df04156c62d69aeb13cb27a360`. Owner-corrected Pakaian Seragam Jawatan now offers only **Pembantu Khidmat Am / Pembantu Makmal**, using the existing rate subset of shared Reference 07. Server and paste guards reject other posts; older valid unrelated-job drafts remain preserved/repairable, with a reselection notice and explicit audited v3 replacement. Bil. Pasang remains fixed at 2.

OS23000 **Perhubungan dan Utiliti** source entry is implemented for its existing canonical mappings: seven dependent choices, manual monthly Kadar, fixed 12 months and exact Kadar × 12 previews. No legacy Utiliti Pukal identity/mapping merge is claimed; that entry remains unconfigured pending reconciliation. Shared SBK Pukal-only behaviour and other definitions remain intact.

Tested application head `f7cdd2553615c4620804fa21359348338c7b1887`; [CI 43670963928](https://github.com/muhdmuizz/sistem-abm/actions/runs/43670963928): **464 tests / 3802 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB, production build and formatter/Chromium passed. Representative final-head desktop/mobile visual review completed; [Batch 029](../development/batch-029-uniform-jobs-utilities.md) records limits. No migration/seed or role/mapping/CSV edits. Batches 028/029 local acceptance remains unconfirmed. Official outputs/totals/completeness, programme inheritance, remaining OS definitions and submission remain pending; Steps 02/03 remain in progress, AD-008 remains Draft.
