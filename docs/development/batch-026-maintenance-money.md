# Batch 026 — OS28000 and Shared Monetary Presentation

**Status:** In progress; owner authorised 15 November 2026.
**Baseline:** `eeca0f056288d62e27d0fb0946c91560d4c54877` on dev.

## Approved scope

Implement the [OS28000 specification](../references/OS28000-penyelenggaraan-reference.md) in the shared spreadsheet: purpose/asset description, four parent categories and ten approved equipment combinations (blank for Tebang Pokok), quantity, mixed manual/reference KADAR, occurrences per month and budgeted months. Exact preview = quantity × rate × frequency × months; fixed RM100 fire extinguisher and RM1500 tree cutting use the original CSV. No fabricated child option, rate, eligibility, quantity cap or financial rounding rule.

Fulfil [the shared monetary display commitment](monetary-display-formatting-commitment.md) across OS25000/SBK/TASKA and OS28000: tabular/right-aligned money with comma grouping and two displayed decimals; exact source/reference/calculation strings remain distinct and inspectable. Monetary editing/focus/blur, grouped paste, save/reopen, duplication and validation must preserve exact values. Display-only rounding is presentation, not financial output policy. Counts/months/frequency remain non-currency.

Reuse live role/workplace/Fasa/central/session/revision/audit guards, partial drafts and legacy protection. No migration, seeder, reference/catalogue/mapping/permission change, official outputs/totals/completeness, programme inheritance or submission implementation.

## Verification

Pending isolated PostgreSQL HTTP/full regression, build, exact-decimal formatter checks, Chromium dependent/mixed-rate and money round-trip regression across all configured OS, and desktop/mobile state inspection. Record actual tested head/counts and limitations before merge.
