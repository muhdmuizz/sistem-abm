# Batch 045 — OS15000 Bayaran Perubatan spreadsheet

**Status:** Merged in [PR #99](https://github.com/muhdmuizz/sistem-abm/pull/99) at `21fe6d34cdbbd7e95d491979530398b258c6df34`; owner explicitly approved both calculation/duration rules on 18 November 2026.
**Baseline:** `656eb69999f127f94487f9998565a7787224ce09` (Batch 044 application PR #97 / docs #98; 555 tests / 6,183 assertions). Previous local sync/migrations/import/financial/device acceptance remain unconfirmed; starting the server is not a claimed local sync or direct Windows verification.

## Approved scope

Use [approved applicability and new owner rules](../references/OS15000-bayaran-perubatan-27-column-applicability.md) and [nine unchanged category/manual-rate references](../references/OS15000-bayaran-perubatan-reference.md). Separate Bayaran Perubatan identity; PPN/010804 only, not OS15000 Faedah. Nama and shared Jawatan/full SSPA Gred; no fabricated legacy C equivalence. All nine categories use exact **Bil. Unit × manual Kadar × Kekerapan × manual Bilangan Bulan**. No automatic factors, new limits/eligibility/quantity units/rounding. Incomplete financial factors withhold the draft amount; partial drafts remain saveable.

Kidney treatment alone exposes Tarikh Mula/Tamat Kontrak and inclusive calendar duration in years/months/days, reusing the verified photocopier helper. Existing Kitaran-year date rule remains. Dates/duration never determine chargeable months or amount. Other categories clear/reject contract dates. Purpose, Kategori2/3, Unit and extra overall-contract fields remain inactive. New scoped `refreshOnSelect` metadata enables this definition's category/Jawatan cascades in the shared grid without changing unflagged definitions' select handling.

## Boundaries

No migration/seed/reset, catalogue/mapping/rate/CSV change, programme inheritance, official output/totals/completeness/submission/export or new proposal workflow. OS26000 purpose stays unchanged after the prior retraction. Sequence remains remaining OS editors → consolidated UI refinement → shared Cadangan Item Baharu. AD-008 stays Draft; Steps 02/03 in progress. Keep Server running separately from Updates.

## Verification

Tested head `e203d8fe8fab2092608823ce62c6ff70ef4acbe2`; [CI 44219944569](https://github.com/muhdmuizz/sistem-abm/actions/runs/44219944569), job 134519037935 succeeded: **562 tests / 6,297 assertions**, PHP 8.4.27 / PostgreSQL 18.7 in isolated `sistem_abm_testing`. Production build, exact-money presentation and full Chromium suite passed. Local Node syntax checks passed for both changed scripts. Artifact 14389772953 contains 530 PNGs / 22 medical captures.

Seven new HTTP/PostgreSQL tests cover the exact nine unchanged category/manual-rate references, approved four-factor formula for every category, precise decimals and missing/zero factors, shared inclusive leap-day/month-end/full-year duration with independent manual months/amount, partial/no-op/save/reopen/edit/read-only source handling, invalid grade/category/date/type/derived/inactive injection rollback, Kitaran-year and reversed-date rejection, live bid/source revision/central permission/phase/PTJ-workplace guards, exact PPN/010804 mapping/separate Faedah identity, independent copy, category switching and audited delete. No generated financial outputs are written.

Chromium covers empty/populated/partial/read-only at 1440/390 widths and horizontal endpoints; current-year date limits, leap/calendar duration parity, independent manual factors, scoped category/Jawatan cascades, clearing inactive dates without resurrection, manual money focus/blur precision, exact source payloads, validation/stale-save retained edits and copy/remove independence. Full existing-editor browser regression passed after the scoped shared-grid metadata change. Client save routes are intercepted; actual persistence is separately tested through HTTP/PostgreSQL.

Visually reviewed **14 medical and two existing-editor captures**: populated desktop left/right and mobile right; empty desktop/mobile left; partial desktop/mobile right; read-only desktop/mobile right; non-contract desktop/mobile; validation desktop/mobile; expanded exact amount mobile; plus existing office-supply and meter populated desktop-right. Confirmed title-case headers, comfortable empty/error padding, manual rates, independent months, calendar display, inactive contract cells, withheld partial values and tabular/accounting amounts. Long helper text and grid density remain consolidated-refinement candidates; horizontal scrolling is expected. Validation captures include the existing focused skip-link overlay over part of the shell, so broader focus behaviour is not certified. Remaining captures/middle columns, dedicated loading/pending-save states, physical devices, screen readers, real local Windows/Google operation and owner financial acceptance remain unverified. No filtered-empty state applies. No UI-complete or production certification is claimed.

Only documentation changes follow the tested head. Local sync/financial/device acceptance pending. No new migration/seed; earlier unconfirmed migration/import remains pending.
