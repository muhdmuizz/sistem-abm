# Monetary Display Formatting — Implementation Commitment

**Owner instruction:** 15 November 2026. **Baseline:** `38b36f1156d8876e5afaa028b54da3f352fce742` on dev.
**Status:** Approved display clarification; application implementation pending.

## Existing rule and clarification

AD-013 Sections 05/06 and the anti-slop checklist already require right-aligned financial values, consistent precision and tabular numerals. The owner clarified the concrete monetary format: **4,320.00**, not **4320**. Section 05.1 is the authoritative shared display contract; AD-008 retains exact arithmetic and OS-specific financial-policy boundaries.

## Next-task acceptance gate

The next proposed OS28000 spreadsheet task must include the shared formatting correction for existing OS25000/SBK/TASKA monetary cells as well as OS28000. Do not implement another isolated unformatted definition first. Quantities/months/frequencies are not automatically currency.

Verify comma grouping, exactly two monetary display decimals, tabular/right-aligned cells and headers; examples 0 → 0.00, 180 → 180.00, 4320 → 4,320.00 and 1234567.8 → 1,234,567.80. Cover reference rates, manual rates, previews and read-only monetary values. Verify focus/blur, grouped paste, save/reopen, duplication and retained validation inputs. Include large exact decimals and higher-precision source/rate calculations so formatting cannot corrupt financial inputs or arithmetic. Use a common presentation path and inspect desktop/mobile states.

No new negative-number/zero-dash convention, financial rounding, rate, eligibility, export schema or OS calculation is authorised by this clarification. Preserve exact values and keep display-only formatting separate from persisted numeric data and calculation inputs. Do not claim the format is implemented until code, regression/browser checks and visual evidence support it.

## Documentation verification

Checked the current dev head, repository instructions, architecture index, AD-013, AD-008 and Batch 025/current register. Updated only Markdown; no application tests were run or required for this documentation-only change. Existing application checkpoint/CI evidence remains unchanged. PR/merge metadata is recorded by the GitHub PR; local application formatting remains pending.
