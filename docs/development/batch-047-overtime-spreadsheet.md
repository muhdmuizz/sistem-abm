# Batch 047 — OS14000 Bayaran Lebih Masa spreadsheet

**Status:** Application merged; local synchronisation/financial/device acceptance pending.
**Baseline:** `177917021ab60596f97ae6832d0858b56f9515d3`, decision PR #103 merged on 18 November 2026. Application baseline remains Batch 046 PR #101 / 570 tests, 6,665 assertions; earlier pending local synchronisation/migrations/import/financial/device acceptance unchanged.

## Approved scope

Use consolidated OS14000 reference and latest 18 November monthly-frequency approval, superseding prior annual-frequency formula. Hourly rate = shared exact Jawatan/full SSPA Gred median × 12 / 2504. Preview = unrounded hourly rate × hours/day × days/occurrence × occurrences/month × independently entered months. Multiply exact numerator before division; final row amount rounded once to two decimals half-up, never via displayed hourly rate. One saved officer entry remains one attributable row; no daily expansion/deduplication. Monetary presentation retains grouped, right-aligned/tabular two decimals.

Four existing exact mappings: PPN010801, Rendah030219, Menengah030301, Asrama030313. Existing date-year/range validation; inclusive day display independent of chargeable days/months. Two approved fixed purposes plus actual registered programme choices queried through existing scoped programme access and same-workplace validation. Programme selection is a source reference only; automatic inheritance/relational association/recalculation integration remains deferred. A changed/deleted programme choice is rejected/quarantined rather than silently remapped. No static legacy programme option.

Reuse partial drafts, shared editor, source revisions/CAS, audit, copy/delete and Fasa/role/PTJ/workplace/central guards. Shared grid changes scoped overtime formula operations using exact BigInt division/half-up, plus strict-reference paste options fallback; unflagged formulas unchanged. Source contains only inputs; salary/rate/preview/duration cannot be injected. No financial eligibility/caps/zero/negative/fraction rules invented. Missing values withhold preview. No official totals/outputs/completeness/submission/export integration, no migration/seed/CSV changes. Fund/programme/participant classifications and full programme inheritance remain deferred context/integration work; this is not production-complete OS14000.

## Verification

Pending CI isolated PostgreSQL suite, production build, exact-money/Chromium and visual review. Local PHP unavailable; Node syntax checks passed for changed scripts. No local Windows access. Keep Server running separately from Updates. AD-008 remains Draft; Steps02/03 in progress. Sequence remains remaining OS editors → consolidated UI refinement → shared proposals.


### Initial CI attempt

Initial head `3adaf040c9be62587a8786399a063e9ddc6aaaf0`, CI44268299591/job134678213355: 5 failed, 573 passed / 6,712 assertions. Three failures from using legacy uppercase Brick Math rounding constant; locked brick/math 1.0.0 uses `RoundingMode::HalfUp`, verified against its exact locked upstream source. Two test fixture failures: mapped activities lacked central activity permission; a second bid reused the unique cycle/PTJ/activity tuple. Corrected enum in application/tests and corrected fixtures, preserving production guards and formula. Build/browser skipped in this failed run.


Static follow-up while corrected CI ran: scoped OS14000 programme lookup explicitly retains Superadmin's existing all-workplace context, rather than requiring a separate preparation assignment through the ordinary programme query. Added a regression assertion; financial write guards unchanged. Supersedes the intermediate application head for final verification.


## Verified application head — 18 November 2026

Tested `e699bda84f7e44c27bc364e36440eea21764c73d`: [CI44269481026](https://github.com/muhdmuizz/sistem-abm/actions/runs/44269481026), job134682192468 passed **578 tests / 7,020 assertions**, production build, exact-money presentation and full Chromium on **PHP8.4.27 / PostgreSQL18.7**, isolated `sistem_abm_testing`. Eight new HTTP/PostgreSQL tests cover all131 salary/job/full-grade pairs, final-only rounding, positive/negative ties, zero/large/fractional inputs, dates/factors independence, partial/save/reopen/no-op/source-only persistence, duplicate named officers retaining separate identities, atomic invalid/derived injection rejection, scoped registered programmes and Superadmin reference access, four exact mappings, independent copy/delete and live access/revision/Fasa/workplace/central guards. No official output rows generated. Node syntax and isolated BigInt formula checks also passed locally; no local PHP or Windows verification.

Intermediate head `1c9eec1c72ec4df35b343e35d59e485b347979ce`, CI44269123200/job134680983740 passed 578 tests/7,019 assertions plus build/browser; superseded by final Superadmin regression verification. Initial failed attempt remains documented above; no failed-head build/browser evidence is claimed.

Artifact14410989701 contains **572 PNGs /20 overtime captures**. Individually reviewed **11 overtime captures**: populated desktop left/right and mobile left/right; empty desktop/mobile left; partial desktop right; read-only desktop/mobile right; validation mobile; large exact amount mobile. Also inspected existing medical populated desktop-right (**12 captures total**). Amount grouping/two decimals, fixed rate, independent factors, partial amount withholding, error cell/summary, read-only rows and button spacing are readable. Mobile requires horizontal table scrolling; long helper text/dense table remain for consolidated refinement. Focused skip link overlays part of the mobile header during validation, as in the shared editor. Other captures, pending/loading states, actual registered-programme selection layout, physical devices/screen readers and local financial acceptance were not individually inspected. No filtered-empty state applies. No UI-complete or production-readiness claim.

Merge pending. Local sync/financial/device acceptance and earlier migration/import statuses unchanged; no new migration/seed. Later documentation-only commits do not extend the tested application evidence.


## Batch 047 merged checkpoint — 18 November 2026

[PR #104](https://github.com/muhdmuizz/sistem-abm/pull/104) merged at `01c48e99ced8db7c41374ccadb3dc56ca2a97fda`. OS14000 source spreadsheet uses the approved salary-derived hourly rate and monthly hours/day × days/occurrence × frequency/month × manual months. Exact numerator before division, final two-decimal half-up amount, independent Kitaran-year dates, four existing mappings, scoped programme/two fixed-purpose choices and dependent full-SSPA personnel. One saved entry per officer record; no daily expansion or generated financial outputs. Official outputs/totals/completeness/submission/export, context classifications and full programme inheritance/association remain pending.

Verified `e699bda84f7e44c27bc364e36440eea21764c73d`, CI44269481026/job134682192468: **578 tests /7,020 assertions**, PHP8.4.27/PostgreSQL18.7 isolated testing DB; build/exact-money/Chromium passed. Artifact14410989701:572 PNGs/20 overtime; 11 overtime and one medical capture individually reviewed. Batch047 records exact failures, intermediate success and presentation/manual limits. No migration/seed/source CSV change. Local sync/financial/device acceptance and prior pending migration/import remain unconfirmed. AD008 stays Draft; Steps02/03 in progress. Keep Server running separately from Updates; remaining editors → consolidated UI refinement → shared proposals.
