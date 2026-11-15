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
- 23 **KEKERAPAN** is Active, displayed in the editor as **BIL. PASANG**, user-entered.
- 27 **ANGGARAN JUMLAH SETAHUN** is calculated. Columns 08–10 are Inactive and exported blank; columns 29–30 remain Deferred.
- Editor-specific labels do **not** rename the canonical Excel headers or alter the approved 30-column position/order.

### Approved financial calculation

**ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KEKERAPAN × KADAR** — i.e., **BIL. PENGISIAN × BIL. PASANG × KADAR**.

Illustration (not an additional rate policy): 5 pengisian × 2 pasang × RM230 = **RM2,300**. Follow the established AD-008 deterministic decimal arithmetic, independent Butiran identities, reference attribution and immutable submitted snapshots. No unapproved eligibility, rounding, rate revisions or new category choices are inferred.

**Change control:** This is approved specification **only**; no Laravel implementation, database migration, source reference CSV changes or Excel implementation are claimed. AD-008 remains Draft.

## Batch 027 implementation checkpoint — 15 November 2026

[PR #64](https://github.com/muhdmuizz/sistem-abm/pull/64) merged at `dffc2c44ebcf50f757cb1588488b6924f0b95c55`. Pakaian Seragam source drafts now use existing PPN/010808 mapping, shared Reference 07 Jawatan, four approved category/job rates, fixed Pasang and exact Bil. Pengisian × Bil. Pasang × Kadar preview. Actual title-case headers are implemented across OS25000/SBK/TASKA/OS28000/Pakaian Seragam, preserving canonical Excel/reference headings and accounting-style money.

Tested application head `bea2bed2522e2b9fb816fb1a6a0a13d9d52bed11`; [CI 43665138171](https://github.com/muhdmuizz/sistem-abm/actions/runs/43665138171): 452 tests / 3626 assertions, PHP 8.4.27 / PostgreSQL 18.7 isolated testing database; build/formatter/Chromium and representative desktop/mobile review passed. See [Batch 027](../development/batch-027-uniform-headers.md) for evidence limits. Local acceptance pending. No migration/seed or new policy. Other OS27000 variants, official output/totals/completeness and programme inheritance remain pending; Steps 02/03 remain in progress. AD-008 remains Draft.
