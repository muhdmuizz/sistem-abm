# Batch 036 — OS29000 Bacaan Meter Mesin Fotostat

**Status:** Merged via PR #83 at `0aba6d03b0ebb16d7137015f172d2c24e28c0836`; verification passed, local sync pending; owner authorised implementation on 16 November 2026.
**Baseline:** `1ca2ac10e93917a33b6681d4cc3db03363566169` on dev. Batch 035 merged and verified; local sync/financial acceptance remains unconfirmed.

## Approved scope

Implement the existing four distinct OS29000 meter mappings (PPN 010807, Rendah 030221, Menengah 030303, Asrama 030315) in the shared spreadsheet editor. Reuse the existing OS24000 two-choice CSV (Berwarna / Hitam dan Putih); do not merge the financial objects. Authoritative specifications: [meter](../references/OS29000-bacaan-meter-mesin-fotostat-reference.md), [contract periods](../references/OS24000-sewaan-mesin-fotostat-reference.md), AD-008 and AD-013.

Current contract dates stay within Kitaran year. Overall contract dates may span years, as required for Kontrak Keseluruhan. Validate each pair separately; no unapproved containment relation. Calculate inclusive calendar years, months and days independently. Boundary implementation uses complete calendar years clamped to the target month's last day, then complete calendar months with the same clamp, then remaining actual days, against the day after the inclusive end. Verify leap days/month ends and JS/PHP parity. This is calendar presentation, never a source of chargeable months.

Bil. Salinan means monthly copies; manual RM-per-copy Kadar and manual Bilangan Bulan produce exact Bil. Salinan × Kadar × Bilangan Bulan previews. Missing applicable factors withhold preview; partial drafts remain saveable and cannot establish financial completeness. Lokasi Mesin Fotostat is a display label; canonical export fields remain unchanged. Source precision, grouped/two-decimal tabular/right-aligned money and title-case headers apply.

No migration/seed/reset, category/rate/reference/catalogue/mapping/permission changes, OS24000 editor, new mandatory completeness engine, programme inheritance, official output/totals/submission or export schema changes. Existing guards, atomic source save/revisions/audit and read-only scope remain.

## UI sequencing decision

Owner approved completing remaining OS editors before a consolidated editor UI refinement, then pausing before another major module. Fix usability defects as encountered; continue AD-013 during implementation. Refinement remains a future bounded batch, not authority to redesign unfinished workflows here. Existing local confirmations are unchanged.

## Verification

Tested application head `ef1b7c2c21b21fb7686f629ca81e79d6a94fb117`: [CI 43793891272](https://github.com/muhdmuizz/sistem-abm/actions/runs/43793891272), job 133058225694, **503 tests / 4,830 assertions**, PHP 8.4.27/PostgreSQL 18.7 in isolated `sistem_abm_testing`. Production build, exact-money formatter and full Chromium regression passed. Initial runs exposed two test-fixture errors (incorrect hand-calculated multiplication and unsupported workplace type); corrected and independently checked using decimal arithmetic. The empty-fixture browser wait was also corrected before final verification. No financial formula was changed to satisfy an expected test value.

Seven new HTTP/PostgreSQL regressions cover the two shared print choices and editor labels; all four exact activity mappings and rental/other-variant separation; save/reopen/edit/no-op audit with exact per-copy precision; current-year and overall-contract date validation; independent inclusive calendar durations across same-day, month-end, leap-year and maximum supported date boundaries; blank/partial drafts without invented rates or months; atomic rejection of inactive/derived-field injections; live revision/Fasa/role and cross-PTJ guards, read-only access and audited deletion. Existing source/output separation remains (no generated financial outputs).

Chromium checks empty/populated/partial/read-only fixtures at 1440/390 widths and horizontal endpoints; copy/remove independence; current/overall duration parity and boundary changes without altering months or amount; exact rate focus/blur and derived display/payload precision; nested validation and stale-save recovery with retained edits. Client save routes are intercepted; real persistence/guards are separately exercised by HTTP/PostgreSQL. No new filtered-empty or expanded activity states apply to this flat editor. Local Node syntax/calendar checks passed. Browser checks and screenshots are fixtures, not authenticated owner-device acceptance.

Artifact 14193737345 contains **360 PNGs / 18 meter captures**. All 18 meter captures were visually reviewed: empty/populated/partial/read-only at 1440/390 widths and both horizontal endpoints, plus validation at both widths. Also reviewed MySTEP populated desktop-left and grouped Umum populated mobile-right (20 captures total). Spacing, title-case headers, withheld partial results, read-only rendering, error summary/desktop rate error and accounting figures are readable in the inspected views; no blocking new visual defect found. The long input-help text/table density remain candidates for the scheduled consolidated refinement. Horizontal endpoints do not show every middle column or the mobile rate error cell; automated field/duration/error checks cover those interactions. Pending-save/loading and expanded exact-value details were not newly captured for meter; no filtered-empty state applies. Authenticated local Windows, physical-device, screen-reader and owner financial acceptance remain unverified. This is not a UI-complete or production-readiness claim.

No migration/seed or routine server restart required. Keep the existing local server running; pull/cache-clear in another terminal and Ctrl+F5. Previous local confirmations remain unchanged.

## Batch 036 checkpoint — 16 November 2026

[PR #83](https://github.com/muhdmuizz/sistem-abm/pull/83) merged at `0aba6d03b0ebb16d7137015f172d2c24e28c0836`. OS29000 Bacaan Meter source editing now supports the four existing mappings, unchanged shared print choices, monthly copies × exact manual per-copy rate × independent manual months, and separate inclusive calendar current/overall contract durations. Current dates follow Kitaran year; overall contracts may span years. OS24000 remains separate. No migration/seed/reset or reference/rate/catalogue/mapping/permission/export-schema changes.

Tested `ef1b7c2c21b21fb7686f629ca81e79d6a94fb117`; [CI 43793891272](https://github.com/muhdmuizz/sistem-abm/actions/runs/43793891272): **503 tests / 4,830 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. All 18 meter state captures plus two representative existing-editor captures reviewed from 360 PNGs; [Batch 036](batch-036-bacaan-meter-spreadsheet.md) records coverage and visual limits. Local sync/financial acceptance remains pending, including earlier unconfirmed batches. Programme inheritance, official financial outputs/totals/completeness and submission remain pending; AD-008 remains Draft and Steps 02/03 remain in progress.

**UI sequencing approved:** Complete remaining OS editors, then pause for consolidated editor UI refinement before another major module. Fix usability defects as encountered and apply AD-013 throughout. This is an implementation checkpoint, not a change to financial or design approval rules.
