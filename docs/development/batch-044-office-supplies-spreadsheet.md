# Batch 044 — OS27000 Bekalan Alat Pejabat baseline spreadsheet

**Status:** In progress; owner approved 17 November 2026.
**Baseline:** `19ae4e2e6add5f0fa609adeadde393d094fecc92` (Batch 042 application PR #95 / docs #96; 548 tests / 5,719 assertions). Previous local sync/migration/import/financial/device acceptance remains unconfirmed.

## Scope and authoritative decisions

Use [approved OS27000 specification](../references/OS27000-bekalan-alat-pejabat-reference.md), AD-008 source/output separation and AD-013 presentation rules. Direct entry only: four Kategori1 categories and 194 dependent Kategori2 items; read-only reference Kadar; Bil. Unit × Kadar exact draft preview. Preserve all existing labels/rates, including only the two expressly approved higher duplicate rates (1.20 clips / 36.00 rafia); original source and cleaned CSVs unchanged. Fixed Bil. Unit label; canonical UNIT/Kategori3 inactive. Purpose/dates/duration apply only to future associated Program/ Aktiviti, so omit them from direct entry and reject injection. Five existing exact activity mappings; keep Pakaian Seragam/Touch ’n Go definitions and permissions separate. Reuse shared partial/no-op/save/reopen/revision/Fasa/role/PTJ/copy/delete/audit behaviour; no generated official financial outputs.

## Owner sequencing and retraction

The owner retracted removal of OS26000 purpose before it merged. Batch 043 removal attempt is withdrawn; no PR/merge and no change to dev's OS26000 purpose or applicability. Do not apply abandoned branch work. The owner then approved finishing remaining OS editors, consolidated spreadsheet UI refinement, and only then shared Cadangan Item Baharu for OS26000 Bukan Pukal / OS27000 office supplies. This supersedes earlier notes calling proposal governance the immediate next slice.

## Boundaries and verification

No migration/seed/reset, catalogue/mapping/rate/CSV changes, new-item proposal storage/review/publication/provisional use, programme inheritance, official outputs/totals/completeness/submission or export changes. AD-008 remains Draft; Steps 02/03 in progress. Keep Server running separately from Updates.

Dedicated HTTP/PostgreSQL source/reference/guards and full isolated suite/build/exact-money/Chromium plus relevant desktop/mobile review pending. Record actual tested head/runtime/run/counts and review limits before merge. Local acceptance remains pending.
