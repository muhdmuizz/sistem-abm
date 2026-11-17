# Batch 041 — OS26000 baseline source spreadsheets

**Status:** In progress; owner approved 17 November 2026.
**Baseline:** `c3605055b3224c9daad83ddd0e052bac12f501e2`. Batch 040 merged/verified at `a45ec936cb67d565e72446729830fd12f96047f2`, 537 tests / 5,435 assertions. “Good, next” acknowledges progress; supplied local migration/import and financial/device checks remain unconfirmed. Server/Updates terminals remain separate; no routine restart.

## Approved scope

Implement only the baseline editor slice from [OS26000 approved specification](../references/OS26000-bahan-mentah-reference.md): 42 unchanged rate paths (3 Pukal/39 Bukan Pukal), exact approved UNIT mapping, contextual quantity labels, purpose input and separate catalogue identities/activity-code permissions. Pukal = Bil. Unit × Kadar × Kekerapan × Bilangan Bulan; Bukan Pukal = Bil. Unit × Kadar. UNIT/Kadar derived read-only, no manual overrides. No invented vehicle-format requirement, multiplier, rounding or eligibility rule. Inactive Pukal multipliers are cleared/rejected in Bukan Pukal.

New machine-readable unit CSV transcribes the exact approved Markdown table; it does not add/change rates or items. Preserve five fuel assumptions RON95 4.00/Diesel 5.00 only in the two explicitly approved planning contexts (2027 original, 2028 subsequent approval); these are not observed prices. Withhold fuel rate/preview for other cycles rather than invent a generic rate-switching policy or manual fallback. The 37 non-fuel costs remain unchanged across these contexts.

Retain all nine exact existing activity mappings: five Bukan Pukal and four Pukal; no Pukal mapping for Pendidikan Awal. Filter reference choices per selected OS identity and enforce catalogue/code parentage and existing live PTJ/workplace/role/Fasa/revision guards. Preserve source IDs, partial save, exact decimals, audit/copy/delete and shared title-case/tabular accounting presentation.

## Deferred boundaries

Cadangan Item Baharu proposal storage/review/provisional use/approval/rejection/publication is the subsequent agreed slice, shared with OS27000 office supplies; not exposed or claimed complete here. No migration/seed/reset, original rate/source CSV edit, catalogue/mapping/permission-policy change, programme inheritance, official financial outputs/totals/completeness/submission or export change. AD-008 remains Draft; Steps 02/03 in progress. Remaining OS editors precede consolidated UI refinement.

## Verification

Pending dedicated source/reference/permission/year-boundary HTTP/PostgreSQL tests, full isolated suite/build/exact-money/Chromium and relevant desktop/mobile state review. Record actual tested head/run/counts and limitations before merge. Actual merge and local sync/financial/device acceptance pending.
