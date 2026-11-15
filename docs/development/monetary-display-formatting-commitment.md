# Monetary Display Formatting — Implementation Commitment

**Owner instruction:** 15 November 2026. **Baseline:** `38b36f1156d8876e5afaa028b54da3f352fce742` on dev.
**Status:** Implemented in Batch 026 for configured OS25000/SBK/TASKA/OS28000 spreadsheet cells; local acceptance pending. Future monetary interfaces remain bound by the same rule.

## Existing rule and clarification

AD-013 Sections 05/06 and the anti-slop checklist already require right-aligned financial values, consistent precision and tabular numerals. The owner clarified the concrete monetary format: **4,320.00**, not **4320**. Section 05.1 is the authoritative shared display contract; AD-008 retains exact arithmetic and OS-specific financial-policy boundaries.

## Next-task acceptance gate

The next proposed OS28000 spreadsheet task must include the shared formatting correction for existing OS25000/SBK/TASKA monetary cells as well as OS28000. Do not implement another isolated unformatted definition first. Quantities/months/frequencies are not automatically currency.

Verify comma grouping, exactly two monetary display decimals, tabular/right-aligned cells and headers; examples 0 → 0.00, 180 → 180.00, 4320 → 4,320.00 and 1234567.8 → 1,234,567.80. Cover reference rates, manual rates, previews and read-only monetary values. Verify focus/blur, grouped paste, save/reopen, duplication and retained validation inputs. Include large exact decimals and higher-precision source/rate calculations so formatting cannot corrupt financial inputs or arithmetic. Use a common presentation path and inspect desktop/mobile states.

No new negative-number/zero-dash convention, financial rounding, rate, eligibility, export schema or OS calculation is authorised by this clarification. Preserve exact values and keep display-only formatting separate from persisted numeric data and calculation inputs. Do not claim the format is implemented until code, regression/browser checks and visual evidence support it.

## Documentation verification

Checked the current dev head, repository instructions, architecture index, AD-013, AD-008 and Batch 025/current register. Updated only Markdown; no application tests were run or required for this documentation-only change. Existing application checkpoint/CI evidence remains unchanged. PR/merge metadata is recorded by the GitHub PR; local application formatting remains pending.

## Implementation checkpoint — 15 November 2026

[Batch 026](batch-026-maintenance-money.md), [PR #61](https://github.com/muhdmuizz/sistem-abm/pull/61), merged `0343ac972b1f366443e9508088e7d5d7fc2286d6`, fulfils the assigned shared-grid formatting gate. [CI 43662888155](https://github.com/muhdmuizz/sistem-abm/actions/runs/43662888155) at `d9f2742950eb6e071ad003bc4609971a09173b96` passed 445 tests / 3544 assertions, build, exact formatter and Chromium checks. Two-decimal/grouped money is presentation only; source precision and calculation values are preserved through edit/paste/copy/save, with Nilai tepat for higher precision. See the batch MD for actual visual-review coverage/limits. No official financial rounding/completeness/output or local acceptance is certified. The earlier implementation-pending paragraphs describe the original clarification checkpoint.
