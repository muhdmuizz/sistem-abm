# Batch 042 — One OS26000 card and spreadsheet

**Status:** Merged in [PR #95](https://github.com/muhdmuizz/sistem-abm/pull/95) at `ae59695121bf72c1adc061ada09f1693187c513a`; owner approved 17 November 2026 after reporting the two OS26000 cards.
**Baseline:** `129a8c63b03d56a9e40d99eb696134fe28057182` (Batch 041 application PR #93, documentation PR #94). Batch 041 local sync/financial acceptance remains unconfirmed; earlier unconfirmed migrations/imports remain pending.

## Authorised correction

Supersede Batch 041's separate catalogue identities with one retained OS26000 card (`Bekalan Bahan Mentah dan Bahan-bahan Untuk Penyelenggaraan dan Pembaikan`). Select Pukal/Bukan Pukal per spreadsheet row; both can coexist in one Anggaran. Preserve all nine existing Kod Aktiviti mappings, original provenance, exact code parents, 42 reference paths, units/rates, source schema and formula semantics. Filter available paths by the exact mappings for the current Aktiviti Kewangan and validate the same limits server-side. Pendidikan Awal has Bukan Pukal only. The single retained OS permission controls the card; obsolete permission overrides are not promoted.

Pukal = quantity × reference rate × frequency × months. Bukan Pukal = quantity × reference rate, with frequency/months cleared client-side and rejected if injected. Source-only `os26000.v1` values under the retained identity remain readable; no version/schema rewrite. Fuel assumptions remain approved only for 2027/2028 planning; no new financial rule.

As previously authorised for development-only obsolete Pukal records, a targeted transactional migration removes only the obsolete OS26000 identity's draft/source/output graph and overrides; retains canonical and unrelated records; repoints non-duplicate mappings with stable IDs and original wording; increments affected bid revisions; aborts atomically if obsolete estimates belong to non-draft bids. Idempotent reference import cannot recreate the old card. No reset/fresh or broad financial seed. Expected catalogue 30 (28 eligible/two excluded), mappings unchanged at 105 (103 eligible); prior counts are historical.

## Boundaries and verification

No proposal workflow, official outputs/totals/completeness/submission, programme inheritance or export changes. AD-008 remains Draft; Steps 02/03 in progress. Proposal governance remains the next agreed slice. Server remains running in its dedicated terminal; use Updates for Git/migration/import.

Node syntax and static CSV counts passed locally.

Tested head `4113f1985ecd26153ae39e50e39a55a263d933d9`; [CI 44070143813](https://github.com/muhdmuizz/sistem-abm/actions/runs/44070143813), job 133993980538: **548 tests / 5,719 assertions**, PHP 8.4.27 / PostgreSQL 18.7 in isolated `sistem_abm_testing`; production build, exact-money and full Chromium suite passed. Artifact 14322591685 contains 488 PNGs / 42 OS26000 captures.

HTTP/PostgreSQL checks cover one catalogue card, mixed rows in one estimate, save/reopen/path changes, exact formulas, 42 unchanged references/units, nine mapping contexts and missing-code path rejection, derived/inactive injections with rollback, live role/PTJ/Fasa/revision guards, independent copy/audited deletion, targeted idempotent cleanup preserving canonical sources/mapping IDs/permissions and atomic abort for non-draft bids. Repeat seed preserves nine mappings/provenance and one identity. Browser checks additionally prove category cascade resets, inactive multiplier clearing without resurrection, exact payloads and validation/stale recovery. Browser save calls use intercepted routes; real persistence is covered separately by HTTP/PostgreSQL.

Visually reviewed **14 OS26000 captures plus one existing OS22000 capture**: one-card desktop/mobile; mixed desktop/mobile left/right; empty desktop/mobile; partial desktop/mobile right; read-only desktop/mobile right; validation mobile and expanded exact amount desktop. One card, mixed formulas, inactive cells, comfortable empty/error padding, title-case headers and accounting amounts are readable. Long mobile titles/help density remain consolidated-refinement candidates. The validation capture includes the existing focused skip-link overlay covering part of the title; broader focus behaviour is not certified. Remaining captures/middle columns, dedicated loading/pending-save states, physical devices, screen readers, local Windows/Google operation and owner financial acceptance remain unverified. No filtered-empty state applies. This is not UI-complete or production certification.

Initial head `8c2934a14c62889ac7d33833709d81f2535390ce`, CI 44069145444/job 133990552756: 547 passed / one failed (5,706 assertions). New one-card test opened bids.index (activity list) rather than bids.show (OS cards), so its title assertion failed before mixed-row checks. Corrected the test route and added an explicit single-OS26000-card assertion; no application code changed for this correction. Build/browser were skipped in that failed run.

Only documentation changes follow the tested application head. Local sync/migration/reference import remain pending; no reset/fresh. Keep Server running separately from Updates.
