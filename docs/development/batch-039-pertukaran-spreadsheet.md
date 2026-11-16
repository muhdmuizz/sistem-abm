# Batch 039 — OS21000 Tuntutan Perbelanjaan Pertukaran

**Status:** In progress; owner approved 16 November 2026.
**Baseline:** `3f8638cae2ce0d0430f44918623991aa588ce318`. Batch 038 merged at `9f294dc9a33c6b6dbb544e0505fa7b0bfe83a42d`; local sync/additive migration/financial acceptance remains unconfirmed. “Proceed” authorises this batch, not a claim of prior local acceptance.

## Approved scope and authority

Implement only [the approved Pertukaran reference](../references/OS21000-tuntutan-perbelanjaan-pertukaran-category-combinations.md), using unchanged shared 83-row OS21000 rates and shared 131-row Jawatan/full SSPA Gred reference. Preserve eight category paths, one fixed Tujuan and five existing activity mappings. Numeric full-SSPA interpretation follows the universal rule in OS15000-jawatan-gred-and-unspecified-kadar-rules.md; source grade C is never guessed or mapped to VU7.

Use the shared flat spreadsheet and existing audited atomic draft service. Source-only Komponen Tuntutan distinguishes vehicle/public transport and the approved three Tambang modes; Padanan Kadar selects the existing reference location to disambiguate rates. These are source lookup selectors, not new canonical categories or activation of inactive Lokasi Urusan Rasmi/export columns. Jawatan/full-grade pairs reuse the shared catalogue. Auto rates cannot be overridden; only an existing matched blank rate permits manual Kadar. Unmatched grades retain incomplete drafts with no assumed rate/result. Jarak applies only to vehicle mileage; calculation is Kadar × Jarak for that component, otherwise Kadar × 1. No additional multiplier/rounding/eligibility policy.

Title-case headers, slash typography and tabular/right-aligned grouped/two-decimal monetary presentation are mandatory; exact inputs remain unrounded. Validate malicious/inactive fields, malformed dependent choices, stale sources, Fasa/roles/PTJ and scope; retain partial drafts and source IDs/copy/delete behaviour. No migration/seed/reset, reference/rate/catalogue changes, official outputs/totals/completeness/submission, programme inheritance or export-schema changes. AD-008 stays Draft; Steps 02/03 in progress. Consolidated editor UI refinement follows remaining OS editors.

## Verification

Pending PHP/PostgreSQL tests in isolated sistem_abm_testing, build/exact-money/Chromium regression and desktop/mobile state review. Record tested head/run/counts and limitations before merge; actual merge checkpoint/local acceptance pending.
