# Batch 046 — OS15000 Faedah-faedah Kewangan Yang Lain

**Status:** In progress; owner approved Kadar × 1 on 18 November 2026.
**Baseline:** `85bf3296366078319cd7bea9a55623c4f2c771b9` (Batch 045 application PR #99 / docs #100; 562 tests / 6,297 assertions). Prior local sync/migration/import/financial/device acceptance remains unconfirmed; “Good, proceed” acknowledges progress, not detailed local acceptance.

## Approved scope

Use [approved applicability and new calculation](../references/OS15000-faedah-faedah-kewangan-yang-lain-27-column-applicability.md), consolidated reference, original 125 grade/rate rows, 17 category records and Reference 07's 131 exact personnel rows. Separate Faedah identity, with the three existing PPN/010801, Pendidikan Menengah/030301 and Pengurusan Asrama/030313 mappings. One named recipient and benefit per row; exact **Kadar × 1**. Preserve nine grade-rate types, manual Yuran/territory types, four HPA qualifications and exact Jawatan/full-grade fixed KGT BIPs. No legacy C equivalence, absent-grade fabrication or manual override for fixed/unmatched rates.

The VU7 fixed-KGT cell is intentionally blank; Reference 07 separately records a percentage requirement. Keep its amount unavailable and reject manual substitutes; no salary-basis, percentage, ceiling or rounding implementation. Fixed numeric KGT remains exact per job/grade. Tujuan Yuran only for refunds; category2 only territory/HPA; category3 only territory. Inactive fields are cleared client-side and rejected on save. Partial drafts retained without complete amount; named recipient/personnel/path/rate required for preview, refund also requires description. Never create official financial outputs.

Shared grid adds scoped multi-value activeWhen metadata and exact job/grade reference resolution; existing grade-only resolution retained. Trim blank personnel names in client preview to match existing backend handling. Reuse shared mixed-rate display, exact decimals, CAS/revisions, audit, copy/delete, Fasa/role/PTJ/workplace guards.

## Boundaries

No migration/seed/reset, reference/rate/CSV/catalogue/mapping-policy change, official totals/completeness/submission/export or programme inheritance. No new proposals; finish remaining OS editors → consolidated UI refinement → shared Cadangan Item Baharu. OS26000 purpose unchanged. AD-008 stays Draft; Steps 02/03 in progress. Keep Server running separately from Updates.

## Verification

Pending dedicated reference/formula/percentage-boundary HTTP/PostgreSQL tests, full isolated suite/build/exact-money/Chromium and relevant desktop/mobile states. Record exact tested head/run/runtime/counts and review limits before merge. Merge/local acceptance pending.
