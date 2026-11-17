# Batch 044 — OS27000 Bekalan Alat Pejabat baseline spreadsheet

**Status:** Verified; merge pending; owner approved 17 November 2026.
**Baseline:** `19ae4e2e6add5f0fa609adeadde393d094fecc92` (Batch 042 application PR #95 / docs #96; 548 tests / 5,719 assertions). Previous local sync/migration/import/financial/device acceptance remains unconfirmed.

## Scope and authoritative decisions

Use [approved OS27000 specification](../references/OS27000-bekalan-alat-pejabat-reference.md), AD-008 source/output separation and AD-013 presentation rules. Direct entry only: four Kategori1 categories and 194 dependent Kategori2 items; read-only reference Kadar; Bil. Unit × Kadar exact draft preview. Preserve all existing labels/rates, including only the two expressly approved higher duplicate rates (1.20 clips / 36.00 rafia); original source and cleaned CSVs unchanged. Fixed Bil. Unit label; canonical UNIT/Kategori3 inactive. Purpose/dates/duration apply only to future associated Program/ Aktiviti, so omit them from direct entry and reject injection. Five existing exact activity mappings; keep Pakaian Seragam/Touch ’n Go definitions and permissions separate. Reuse shared partial/no-op/save/reopen/revision/Fasa/role/PTJ/copy/delete/audit behaviour; no generated official financial outputs.

## Owner sequencing and retraction

The owner retracted removal of OS26000 purpose before it merged. Batch 043 removal attempt is withdrawn; no PR/merge and no change to dev's OS26000 purpose or applicability. Do not apply abandoned branch work. The owner then approved finishing remaining OS editors, consolidated spreadsheet UI refinement, and only then shared Cadangan Item Baharu for OS26000 Bukan Pukal / OS27000 office supplies. This supersedes earlier notes calling proposal governance the immediate next slice.

## Boundaries and verification

No migration/seed/reset, catalogue/mapping/rate/CSV changes, new-item proposal storage/review/publication/provisional use, programme inheritance, official outputs/totals/completeness/submission or export changes. AD-008 remains Draft; Steps 02/03 in progress. Keep Server running separately from Updates.

## Verification

Tested head `b096af2cac1885fef4e3252905549f3ec363862b`; [CI 44077277421](https://github.com/muhdmuizz/sistem-abm/actions/runs/44077277421), job 134018439201 succeeded: **555 tests / 6,183 assertions**, PHP 8.4.27 / PostgreSQL 18.7 in isolated `sistem_abm_testing`. Production build, exact-money presentation and full Chromium suite passed. Node syntax check also passed locally. Artifact 14326430153 contains 508 PNGs / 20 office-supply captures.

Seven new HTTP/PostgreSQL tests compare all 194 selectable items against all 196 normalised source rows, verify only the two approved higher-price duplicates, all four categories, exact and partial previews, no-op/save/reopen/edit, derived/inactive/malformed/cross-category injections with atomic rollback, current bid/source revision, central OS permission, cross-PTJ workplace and phase guards, five exact mappings, other-OS27000 separation, read-only rendering and independent audited copy/delete. No generated financial outputs are written.

Chromium verifies four editor states at 1440/390 widths and both horizontal endpoints; absence of direct purpose/dates/Unit/Kategori3/multipliers/manual rate inputs; dependent resets, both approved duplicate prices, exact quantity/result versus accounting presentation, source payload separation, field errors, retained stale edits and copy/remove independence. Browser save routes are intercepted; actual persistence is separately covered through HTTP/PostgreSQL.

Visually reviewed **12 office captures and one existing OS26000 capture**: populated desktop left/right and mobile right; empty desktop/mobile left; partial desktop/mobile right; read-only desktop/mobile right; validation desktop/mobile; expanded exact amount mobile. Confirmed comfortable empty/error padding, title-case headers, read-only reference rates, withheld incomplete amounts and tabular accounting figures. Existing OS26000 retains purpose as retracted removal requires. Wide grids still require horizontal scrolling; long helper text remains a consolidated-refinement candidate. Remaining captures/middle columns, dedicated loading/pending-save states, broader focus/skip-link behaviour, physical devices, screen readers, real local Windows/Google operation and owner financial acceptance were not independently verified. No filtered-empty state applies. No UI-complete or production certification is claimed.

Only documentation changes follow the tested head. Merge and local sync/financial/device acceptance pending. This batch adds no migration/seed; earlier pending migration/import remains unconfirmed.
