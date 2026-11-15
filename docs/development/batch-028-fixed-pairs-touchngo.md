# Batch 028 — Fixed Bil. Pasang and Touch ’n Go

**Status:** Merged; local acceptance pending. Owner authorised 15 November 2026.
**Baseline:** `8ca2c50ce2e026d1e1f404f676c3962d58b39cc6` on dev.

## Scope and authority

Owner correction: Pakaian Seragam **Bil. Pasang = 2**, defaulted and locked; enforce server-side, for the existing Pakaian Seragam/Kasut choices without inventing different category limits. Supersedes prior editable multiplier wording in [uniform reference](../references/OS27000-pakaian-seragam-reference.md). UI fixed output and server-derived source value; reject posted kekerapan. Version v2, retaining read/edit compatibility with valid v1 drafts. Read does not rewrite existing stored inputs; current preview uses two and an explicit save upgrades that row through existing audit/revision guards. No bulk data rewrite or migration.

Next bounded definition: approved [Touch ’n Go](../references/OS27000-bekalan-dan-bahan-lain-touch-n-go-reference.md), canonical variant Bekalan dan Bahan-bahan Lain, existing PPN/010809, Pendidikan Menengah/030304, Pengurusan Asrama/030316 only. Tujuan, Bil. Unit, manual Kadar Bulanan, Bilangan Bulan; exact preview = quantity × monthly rate × months. No frequency/categories/unit/card operations or reference rate; partial drafts allowed. Shared title-case headers, tabular right-aligned grouped/two-decimal money with exact precision retained. No extra month/quantity cap or financial rounding policy.

Retain existing source identities, access/Fasa/session/revision/audit guards, copy/removal and private legacy quarantine. No migration/seed, role/mapping/CSV changes, item proposals, official totals/outputs/completeness, programme inheritance or submission.

## Verification


## Verification — passed before merge

Tested application head `c780d2dbb6cb5dcb40d10e6e7e7b78fcb7033a28`; [CI 43669111898](https://github.com/muhdmuizz/sistem-abm/actions/runs/43669111898), job 132674973027: **458 tests / 3701 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`. Production build, formatter/browser checks and full Chromium regression passed. Six new HTTP tests (five Touch ’n Go, one legacy/fixed-pairs correction) plus updated uniform assertions cover exact persistence, no-op/edit, all three activity mappings, injection/atomic rollback, partial drafts, stale/Fasa/read-only guards, removal and legacy protection.

Final artifact **14143164930** contains 215 PNGs, including 18 new Touch ’n Go captures; existing uniform captures now reflect fixed pairs. Initial visual pass at `8193c17e627e021ca59e97658004c46d6bfaeee1` reviewed 13 representative desktop/mobile captures: uniform empty/populated and Touch ’n Go empty/populated/partial/read-only/validation. Final-head recheck reviewed uniform populated desktop, Touch ’n Go populated desktop and mobile validation. Comfortable empty/error insets, actual title-case headers and grouped/right-aligned money retained; wide grids scroll internally. Not every capture, loading/expanded state or physical device was inspected. HTTP tests establish real database persistence; browser error responses are intercepted static-fixture checks, not live browser save certification.

No application edits after tested head. No migration/seed or official financial output/completeness. Local acceptance pending.

## Local follow-through — pending

Keep existing server running; clean dev pull --ff-only, php artisan optimize:clear, Ctrl+F5. Confirm Pakaian Seragam Bil. Pasang shows fixed **2** on new/reopened rows and cannot be edited; 5 × 2 × RM230 = **2,300.00**. Save/reopen an older draft if applicable; only explicit save upgrades its stored source through audit. Open eligible OS27000 Bekalan dan Bahan-bahan Lain (Touch ’n Go): Tujuan, 3 units, RM200 monthly, 12 months => **7,200.00**. Check save/reopen/edit/copy/remove and horizontal scrolling. No frequency/category/unit fields in Touch ’n Go. Official financial integration remains pending.


## Merge checkpoint

PR #66 merged at `496dbfb0d9cb30f982bc02f8470f6b46773b5269`. Evidence-only head `b38b844307d804552ef906a0c69d4d57a90e8997` preserves tested application code. This documentation close-out adds no test execution or local acceptance.
