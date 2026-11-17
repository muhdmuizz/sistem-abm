# Batch 041 — OS26000 baseline source spreadsheets

**Status:** Merged in [PR #93](https://github.com/muhdmuizz/sistem-abm/pull/93) at `cafacd3a86debdd30a0f67fec0e2c9558d21a909`. Owner approved 17 November 2026.
**Baseline:** `c3605055b3224c9daad83ddd0e052bac12f501e2`. Batch 040 merged/verified at `a45ec936cb67d565e72446729830fd12f96047f2`, 537 tests / 5,435 assertions. “Good, next” acknowledges progress; supplied local migration/import and financial/device checks remain unconfirmed. Server/Updates terminals remain separate; no routine restart.

## Approved scope

Implement only the baseline editor slice from [OS26000 approved specification](../references/OS26000-bahan-mentah-reference.md): 42 unchanged rate paths (3 Pukal/39 Bukan Pukal), exact approved UNIT mapping, contextual quantity labels, purpose input and separate catalogue identities/activity-code permissions. Pukal = Bil. Unit × Kadar × Kekerapan × Bilangan Bulan; Bukan Pukal = Bil. Unit × Kadar. UNIT/Kadar derived read-only, no manual overrides. No invented vehicle-format requirement, multiplier, rounding or eligibility rule. Inactive Pukal multipliers are cleared/rejected in Bukan Pukal.

New machine-readable unit CSV transcribes the exact approved Markdown table; it does not add/change rates or items. Preserve five fuel assumptions RON95 4.00/Diesel 5.00 only in the two explicitly approved planning contexts (2027 original, 2028 subsequent approval); these are not observed prices. Withhold fuel rate/preview for other cycles rather than invent a generic rate-switching policy or manual fallback. The 37 non-fuel costs remain unchanged across these contexts.

Retain all nine exact existing activity mappings: five Bukan Pukal and four Pukal; no Pukal mapping for Pendidikan Awal. Filter reference choices per selected OS identity and enforce catalogue/code parentage and existing live PTJ/workplace/role/Fasa/revision guards. Preserve source IDs, partial save, exact decimals, audit/copy/delete and shared title-case/tabular accounting presentation.

## Deferred boundaries

Cadangan Item Baharu proposal storage/review/provisional use/approval/rejection/publication is the subsequent agreed slice, shared with OS27000 office supplies; not exposed or claimed complete here. No migration/seed/reset, original rate/source CSV edit, catalogue/mapping/permission-policy change, programme inheritance, official financial outputs/totals/completeness/submission or export change. AD-008 remains Draft; Steps 02/03 in progress. Remaining OS editors precede consolidated UI refinement.

## Verification

Tested application head `0680c16ec8b92ffccd7839f5abbee2bee6d70274`; [CI run 44046082599](https://github.com/muhdmuizz/sistem-abm/actions/runs/44046082599), job 133911984263 succeeded: **544 tests / 5,705 assertions**, PHP 8.4.27 / PostgreSQL 18.7 in isolated `sistem_abm_testing`; production build, exact-money checks and full Chromium suite passed. Artifact 14311643820 contains 482 PNGs, including 36 OS26000 captures.

Reviewed 19 OS26000 captures plus one existing OS22000 capture: desktop/mobile empty, populated, partial, read-only, validation and expanded exact-value states, both horizontal endpoints across the reviewed set. Confirmed accounting amounts, contextual quantity labels, derived units/rates and withheld incomplete previews. Long mobile titles/help text remain consolidated-refinement candidates. Some validation/exact captures include the shared focused skip-link overlay; broader focus behaviour is not certified. Remaining captures/middle columns, dedicated loading/pending-save states, physical devices, screen readers, real Google login, local Windows operation and owner financial acceptance were not verified.

Initial head `0db6ed6e9efacd62d1f6965dfeb442d9babd77c5`, run 44045824953/job 133911127526: 543 passed / one failed, 5,700 assertions. Only failure expected 403 where the inaccessible route correctly returns 404. Test-only correction asserts route concealment and independently verifies backend service 403; final run above passes. Only documentation changed after the tested application head.

Local sync and prior Batch 038 additive migration / Batch 040 targeted migration/reference import remain unconfirmed. Batch 041 introduces no migration or seed. Financial outputs, completeness/submission and proposal governance remain deferred.
