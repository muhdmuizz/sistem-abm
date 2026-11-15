# Batch 029 — Applicable Uniform Jawatan and OS23000 Utilities

**Status:** In progress; owner authorised 15 November 2026.
**Baseline:** `dffc449c439e9cc5c4181bb1177bffa2a6f8011d` on dev.

## Correction and approved scope

Owner requires OS27000 Pakaian Seragam Jawatan limited to applicable posts. Derive the permitted subset from the unchanged four [approved category/job rates](../references/OS27000-pakaian-seragam-reference.md): **Pembantu Khidmat Am** and **Pembantu Makmal**, verified against shared Reference 07 rather than a duplicate personnel dataset. No additional jobs/grade filters/payroll rules. Keep Jawatan independent of category selection and Bil. Pasang fixed at 2. Enforce new input restriction server-side and on spreadsheet paste; keep consistent actual title-case headers and tabular accounting amounts.

Version v3. Existing valid v1/v2 drafts with unrelated shared jobs stay stored and repairable: no read-time rewrite, no fabricated price, project an empty Jawatan selection with an explicit reselection notice, offer only applicable choices, and replace through explicit audited save. Legacy unknown/malformed sources retain private quarantine. No bulk data rewrite.

Next bounded OS: [OS23000 Perhubungan dan Utiliti](../references/OS23000-perhubungan-utiliti-reference.md), seven existing dependent category combinations, manual monthly Kadar, fixed 12 months, exact preview Kadar × 12. Current canonical Perhubungan dan Utiliti mappings only: PPN/010801, Pendidikan Rendah/030219, Pendidikan Awal/030101, Pendidikan Menengah/030301, Pengurusan Asrama/030313, each requiring its existing mapping and live access. This does not resolve/merge the older **Utiliti Pukal** identity or create another wizard: that catalogue entry remains unconfigured, with source-association reconciliation pending. Reuse existing seven-row CSV shared by SBK; SBK retains its Pukal-only template.

No new quantity/date/eligibility/month/rounding policy. No migration/seed, catalogue/mapping/permission/reference CSV edits, official outputs/totals/completeness, programme inheritance or submission. Preserve exact source decimals, identities, audit/session/Fasa/revision/role guards, copy/removal and legacy protection.

## Verification


## Verification — passed before merge

Tested application head `f7cdd2553615c4620804fa21359348338c7b1887`; [CI 43670963928](https://github.com/muhdmuizz/sistem-abm/actions/runs/43670963928), job 132680674403: **464 tests / 3802 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`. Production build, money formatter/browser checks and full Chromium regression passed. Six new OS23000 HTTP tests cover seven paths, exact grouped-rate normalisation/save/reopen/edit/no-op, all five existing mappings, legacy-alias exclusion, dependent-choice and fixed-field injection/atomic rollback, partial drafts, stale/Fasa/read-only guards and audited removal. Uniform tests now require exactly two permitted choices, reject other shared jobs atomically, preserve old stored jobs on read and replace through audited v3 saves.

Browser checks assert exact allowed Jawatan options on new and older affected rows, reject unsupported paste, retain independent category selection/fixed two pairs, and clear the reselection notice after valid repair. Utility checks cover Pukal/Bukan Pukal dependent reset, fixed 12, exact/format/paste/copy, validation retention and inactive-field payload exclusion; existing grid regression remains intact.

Artifact **14144262800** contains 235 PNGs (20 new: 18 utility states and two uniform repaired states; old unpriced fixtures now represent repairable legacy rows). Representative final-head review covered 14 captures: uniform legacy/repaired desktop/mobile shell, and utility empty/populated/partial/read-only/validation desktop/mobile. Desktop legacy notice was inspected; narrow left-edge captures do not expose its middle Jawatan cell. Not every artifact, expanded/loading state or physical device was visually reviewed. Horizontal grid scrolling is intentional; outer overflow checks pass. Actual persistence is tested by HTTP; browser error responses use static-fixture interception, not a claimed live browser save.

No application changes after tested head. No migration/seed or official financial output/completeness. Batches 028/029 local acceptance remains unconfirmed.

## Local follow-through — pending

Keep existing server running. Clean dev pull --ff-only, php artisan optimize:clear, Ctrl+F5; no migration/seed/build/restart. Pakaian Seragam Jawatan should offer only Pembantu Khidmat Am/Pembantu Makmal, with fixed Bil. Pasang 2. For older unrelated-job drafts choose a replacement and save/reopen; original stored job is retained until explicit save. Open active OS23000 Perhubungan dan Utiliti, select either category path and enter monthly RM360: fixed 12 months gives **4,320.00**. Check save/reopen/edit/copy/removal and internal scrolling. Older Utiliti Pukal identity is not enabled or merged; reconciliation remains pending. Official financial integration remains pending.

