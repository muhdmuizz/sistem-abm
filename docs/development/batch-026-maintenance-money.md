# Batch 026 — OS28000 and Shared Monetary Presentation

**Status:** Verification passed; merge pending. Owner authorised 15 November 2026.
**Baseline:** `eeca0f056288d62e27d0fb0946c91560d4c54877` on dev.

## Approved scope

Implement the [OS28000 specification](../references/OS28000-penyelenggaraan-reference.md) in the shared spreadsheet: purpose/asset description, four parent categories and ten approved equipment combinations (blank for Tebang Pokok), quantity, mixed manual/reference KADAR, occurrences per month and budgeted months. Exact preview = quantity × rate × frequency × months; fixed RM100 fire extinguisher and RM1500 tree cutting use the original CSV. No fabricated child option, rate, eligibility, quantity cap or financial rounding rule.

Fulfil [the shared monetary display commitment](monetary-display-formatting-commitment.md) across OS25000/SBK/TASKA and OS28000: tabular/right-aligned money with comma grouping and two displayed decimals; exact source/reference/calculation strings remain distinct and inspectable. Monetary editing/focus/blur, grouped paste, save/reopen, duplication and validation must preserve exact values. Display-only rounding is presentation, not financial output policy. Counts/months/frequency remain non-currency.

Reuse live role/workplace/Fasa/central/session/revision/audit guards, partial drafts and legacy protection. No migration, seeder, reference/catalogue/mapping/permission change, official outputs/totals/completeness, programme inheritance or submission implementation.

## Verification — passed 15 November 2026

Tested application head `d9f2742950eb6e071ad003bc4609971a09173b96`: [CI 43662888155](https://github.com/muhdmuizz/sistem-abm/actions/runs/43662888155), **445 tests / 3544 assertions**, PHP 8.4.27 / PostgreSQL 18.7 in isolated `sistem_abm_testing`; production build, standalone exact monetary formatter checks and Chromium regression passed.

Six new maintenance HTTP tests exercise all ten reference combinations, fixed/manual rates, exact four-factor formula, blank tree equipment, partial drafts, edit identity, invalid dependencies/overrides and live stale/Fasa/role/legacy safeguards. A new SBK HTTP test verifies grouped money normalisation, exact saved/reopened rate and noncanonical legacy quarantine. Existing role/workplace/session/audit rollback and other-definition tests remain passing.

Shared browser checks verify two-decimal/grouped tabular presentation across OS25000/SBK/TASKA and maintenance; exact preview values remain separately available. Focus/blur, grouped monetary entry, duplication, validation retention and source-only JSON payloads retain original decimal precision. The formatter uses strings/BigInt and display-only magnitude rounding to two decimal places; that display result never feeds source arithmetic or persistence and does not establish official financial rounding. Higher precision is inspectable through Nilai tepat and exact input on focus. Monetary grouping is normalised only at explicit input boundaries; quantities are not silently treated as money.

Maintenance browser checks cover dependent equipment, fixed RM100 and RM1500, automatic clearing when switching rate/category context, eight manual-rate choices, disabled blank equipment for tree cutting, formatted previews, and left/right horizontal grid positions. Static browser save failures are intercepted; actual persistence and validation use PostgreSQL HTTP tests.

Final artifact `14140569010` contains 165 PNG captures, including 24 new maintenance/money captures. From the earlier all-pass run 43662787493 (same UI code; final change is a server legacy guard), visually reviewed 16 new captures: populated left/right at desktop/mobile; empty left at both widths; partial/read-only/dynamic right at both widths; existing OS25000/SBK/TASKA money and money-validation desktop states. Final-head review rechecked maintenance dynamic desktop right, read-only mobile right and expanded OS25000 exact-value desktop. Other captures were generated/browser-checked but not individually re-reviewed. Mobile expanded exact-value disclosure and fixed-position screenshot behaviour while a lower field is focused were not separately visually certified. No new initial async/filter interface; no physical-device, Windows, full-design or production acceptance claim.

No migration/seeder, reference/catalogue/mapping/permission changes, official outputs/totals/completeness, programme inheritance or submission.

## Local follow-through — pending

Keep the server running; use a clean dev pull --ff-only, php artisan optimize:clear and Ctrl+F5. No migration/seeder/npm build or restart. In an eligible active OS28000 open Buka Jadual Butiran; check categories/equipment, blank tree equipment and fixed reference rates; 3 extinguishers × RM100 × 1 × 12 previews **3,600.00**. Save/reopen and test manual-rate changes. Check existing TASKA **4,320.00** and shared reference/manual rates. For a high-precision rate, verify the two-decimal idle display, exact value on focus/Nilai tepat, grouped paste, duplicate/save/reopen and validation retention. Live access/Fasa controls still apply. Local execution has not been performed by the cloud agent.
