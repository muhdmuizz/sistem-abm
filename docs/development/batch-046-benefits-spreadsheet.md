# Batch 046 — OS15000 Faedah-faedah Kewangan Yang Lain

**Status:** Verified; merge pending. Owner approved Kadar × 1 on 18 November 2026.
**Baseline:** `85bf3296366078319cd7bea9a55623c4f2c771b9` (Batch 045 application PR #99 / docs #100; 562 tests / 6,297 assertions). Prior local sync/migration/import/financial/device acceptance remains unconfirmed; “Good, proceed” acknowledges progress, not detailed local acceptance.

## Approved scope

Use [approved applicability and new calculation](../references/OS15000-faedah-faedah-kewangan-yang-lain-27-column-applicability.md), consolidated reference, original 125 grade/rate rows, 17 category records and Reference 07's 131 exact personnel rows. Separate Faedah identity, with the three existing PPN/010801, Pendidikan Menengah/030301 and Pengurusan Asrama/030313 mappings. One named recipient and benefit per row; exact **Kadar × 1**. Preserve nine grade-rate types, manual Yuran/territory types, four HPA qualifications and exact Jawatan/full-grade fixed KGT BIPs. No legacy C equivalence, absent-grade fabrication or manual override for fixed/unmatched rates.

The VU7 fixed-KGT cell is intentionally blank; Reference 07 separately records a percentage requirement. Keep its amount unavailable and reject manual substitutes; no salary-basis, percentage, ceiling or rounding implementation. Fixed numeric KGT remains exact per job/grade. Tujuan Yuran only for refunds; category2 only territory/HPA; category3 only territory. Inactive fields are cleared client-side and rejected on save. Partial drafts retained without complete amount; named recipient/personnel/path/rate required for preview, refund also requires description. Never create official financial outputs.

Shared grid adds scoped multi-value activeWhen metadata and exact job/grade reference resolution; existing grade-only resolution retained. Trim blank personnel names in client preview to match existing backend handling. Reuse shared mixed-rate display, exact decimals, CAS/revisions, audit, copy/delete, Fasa/role/PTJ/workplace guards.

## Boundaries

No migration/seed/reset, reference/rate/CSV/catalogue/mapping-policy change, official totals/completeness/submission/export or programme inheritance. No new proposals; finish remaining OS editors → consolidated UI refinement → shared Cadangan Item Baharu. OS26000 purpose unchanged. AD-008 stays Draft; Steps 02/03 in progress. Keep Server running separately from Updates.

## Verification

Tested application head `6bc52911cf169bd09a5da6ac1aad7fa321b7ea89`: [run 44224595873](https://github.com/muhdmuizz/sistem-abm/actions/runs/44224595873), job 134534547451 passed **570 tests / 6,665 assertions**, production build, exact-money presentation and full Chromium checks on PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`. Both changed JavaScript files passed Node syntax checks. Eight new feature tests cover references, all benefit modes and 131 KGT personnel pairs, unavailable percentage boundary, exact money, save/reopen, partial drafts, source-only persistence, copy/delete, atomic validation and access/revision guards.

Initial head `5a13a33f05823318d6a2559ad202aec6743d2ba3`, CI 44224220274/job 134533297287 stopped before tests executed: private test helper travel() collided with Laravel TestCase's public time-travel method (exit 255). Renamed the helper to territoryRow; application code unchanged. No successful test count/build/browser evidence from that run.

Artifact 14393180199 contains 552 PNGs, including 22 benefit captures. Visually inspected five individual captures: benefits-populated-1440-right, benefits-populated-390-right, benefits-unavailable-1440, benefits-validation-390 and benefits-readonly-1440-right. Grouped two-decimal amounts, fixed/manual modes, unavailable percentage explanation, withheld preview, validation and read-only display are legible. Mobile uses horizontal table scrolling and long instructional text; the focused skip link overlays the mobile header during validation. Unavailable rate uses shared “Tidak berkenaan” text alongside its specific pending-basis explanation. These remain presentation limits for the planned consolidated UI refinement. Other artifact images were not individually visually inspected; browser assertions cover their states.

No new migration/seed. Local sync and financial/device acceptance remain pending. Documentation-only commits after the tested head do not extend the application test evidence.
