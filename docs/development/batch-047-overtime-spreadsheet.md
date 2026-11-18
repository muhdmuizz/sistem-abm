# Batch 047 — OS14000 Bayaran Lebih Masa spreadsheet

**Status:** In progress; verification/merge/local acceptance pending.
**Baseline:** `177917021ab60596f97ae6832d0858b56f9515d3`, decision PR #103 merged on 18 November 2026. Application baseline remains Batch 046 PR #101 / 570 tests, 6,665 assertions; earlier pending local synchronisation/migrations/import/financial/device acceptance unchanged.

## Approved scope

Use consolidated OS14000 reference and latest 18 November monthly-frequency approval, superseding prior annual-frequency formula. Hourly rate = shared exact Jawatan/full SSPA Gred median × 12 / 2504. Preview = unrounded hourly rate × hours/day × days/occurrence × occurrences/month × independently entered months. Multiply exact numerator before division; final row amount rounded once to two decimals half-up, never via displayed hourly rate. One saved officer entry remains one attributable row; no daily expansion/deduplication. Monetary presentation retains grouped, right-aligned/tabular two decimals.

Four existing exact mappings: PPN010801, Rendah030219, Menengah030301, Asrama030313. Existing date-year/range validation; inclusive day display independent of chargeable days/months. Two approved fixed purposes plus actual registered programme choices queried through existing scoped programme access and same-workplace validation. Programme selection is a source reference only; automatic inheritance/relational association/recalculation integration remains deferred. A changed/deleted programme choice is rejected/quarantined rather than silently remapped. No static legacy programme option.

Reuse partial drafts, shared editor, source revisions/CAS, audit, copy/delete and Fasa/role/PTJ/workplace/central guards. Shared grid changes scoped overtime formula operations using exact BigInt division/half-up, plus strict-reference paste options fallback; unflagged formulas unchanged. Source contains only inputs; salary/rate/preview/duration cannot be injected. No financial eligibility/caps/zero/negative/fraction rules invented. Missing values withhold preview. No official totals/outputs/completeness/submission/export integration, no migration/seed/CSV changes. Fund/programme/participant classifications and full programme inheritance remain deferred context/integration work; this is not production-complete OS14000.

## Verification

Pending CI isolated PostgreSQL suite, production build, exact-money/Chromium and visual review. Local PHP unavailable; Node syntax checks passed for changed scripts. No local Windows access. Keep Server running separately from Updates. AD-008 remains Draft; Steps02/03 in progress. Sequence remains remaining OS editors → consolidated UI refinement → shared proposals.


### Initial CI attempt

Initial head `3adaf040c9be62587a8786399a063e9ddc6aaaf0`, CI44268299591/job134678213355: 5 failed, 573 passed / 6,712 assertions. Three failures from using legacy uppercase Brick Math rounding constant; locked brick/math 1.0.0 uses `RoundingMode::HalfUp`, verified against its exact locked upstream source. Two test fixture failures: mapped activities lacked central activity permission; a second bid reused the unique cycle/PTJ/activity tuple. Corrected enum in application/tests and corrected fixtures, preserving production guards and formula. Build/browser skipped in this failed run.
