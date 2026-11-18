# Batch 045 — OS15000 Bayaran Perubatan spreadsheet

**Status:** In progress; owner explicitly approved both calculation/duration rules on 18 November 2026.
**Baseline:** `656eb69999f127f94487f9998565a7787224ce09` (Batch 044 application PR #97 / docs #98; 555 tests / 6,183 assertions). Previous local sync/migrations/import/financial/device acceptance remain unconfirmed; starting the server is not a claimed local sync or direct Windows verification.

## Approved scope

Use [approved applicability and new owner rules](../references/OS15000-bayaran-perubatan-27-column-applicability.md) and [nine unchanged category/manual-rate references](../references/OS15000-bayaran-perubatan-reference.md). Separate Bayaran Perubatan identity; PPN/010804 only, not OS15000 Faedah. Nama and shared Jawatan/full SSPA Gred; no fabricated legacy C equivalence. All nine categories use exact **Bil. Unit × manual Kadar × Kekerapan × manual Bilangan Bulan**. No automatic factors, new limits/eligibility/quantity units/rounding. Incomplete financial factors withhold the draft amount; partial drafts remain saveable.

Kidney treatment alone exposes Tarikh Mula/Tamat Kontrak and inclusive calendar duration in years/months/days, reusing the verified photocopier helper. Existing Kitaran-year date rule remains. Dates/duration never determine chargeable months or amount. Other categories clear/reject contract dates. Purpose, Kategori2/3, Unit and extra overall-contract fields remain inactive. New scoped `refreshOnSelect` metadata enables this definition's category/Jawatan cascades in the shared grid without changing unflagged definitions' select handling.

## Boundaries

No migration/seed/reset, catalogue/mapping/rate/CSV change, programme inheritance, official output/totals/completeness/submission/export or new proposal workflow. OS26000 purpose stays unchanged after the prior retraction. Sequence remains remaining OS editors → consolidated UI refinement → shared Cadangan Item Baharu. AD-008 stays Draft; Steps 02/03 in progress. Keep Server running separately from Updates.

## Verification

Pending dedicated HTTP/PostgreSQL tests, full isolated suite/build/exact-money/Chromium and relevant desktop/mobile state review. Record tested head/run/runtime/counts and limitations before merge. Merge/local acceptance pending.
