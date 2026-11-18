# OS14000 — Bayaran Lebih Masa: consolidated reference checkpoint

**Recorded:** 12 November 2026.  
**Status:** References, hourly-rate formula, annual-amount formula and rounding approved; discussion resumed on 18 November 2026. Single-row output is now approved by the later dated decision below; remaining input rules/integration are not finalised and application implementation has not started. Earlier sections preserve historical checkpoints; the dated approval below supersedes their formula/rounding/pause status.

## 1. Shared personnel reference (not OS-specific ownership)

- Shared [Reference 07 — Jawatan, Gred, Gaji dan KGT](REF-07-jawatan-gred-gaji-kgt.md) and its [131-row CSV](jawatan-gred-gaji-kgt.csv) are reusable across **all Anggaran (OS) where applicable**, not just OS14000.
- **Jawatan and Gred are independent personnel attributes, never Kategori1/Kategori2/etc.**
- The already-confirmed OS14000 selection order is **Jawatan → applicable full SSPA Gred**; `Gaji Median` is retrieved from the corresponding Jawatan/Gred reference row.
- No duplicate OS14000-specific salary CSV is required: the shared catalogue is the source.

## 2. Confirmed OS14000 Kadar formula

**User-confirmed on 12 November 2026:**

```text
Kadar = Gaji Median × 12 ÷ 2504
```

- The `Gaji Median` input comes from **Reference 07** according to selected Jawatan and Gred.
- This is the confirmed **Kadar formula**, not a confirmation of any remaining overall Bayaran Lebih Masa cost formula, claim eligibility, quantity, final aggregation, or export-row mapping.
- **Precision and rounding have not been specified**; do not invent a rounding policy.
- Earlier cautions in Draft AD-008 predate this owner confirmation. **This reference checkpoint records the later explicit confirmation but does not modify or finalise AD-008.**

## 3. Program/ Aktiviti/ Tujuan

The existing [OS14000 Program/ Aktiviti/ Tujuan reference](OS14000-program-aktiviti-tujuan-options.md) is retained as written:

1. Actual **Program/ Aktiviti** registered in the system (replacing the legacy static `Aktiviti/ Program/ Mesyuarat rasmi` option).
2. `Kerja-kerja jangka masa terhad`.
3. `Kerja-kerja mustahak yang tidak boleh ditangguh`.

No additional category levels are introduced.

## 4. Existing design decisions already preserved in AD-008

See [AD-008 — OS Catalogue & Calculation Framework](../architecture/AD-008-os-catalogue-calculation-framework.md):

- Officer-level Butiran Anggaran records have stable, independent IDs; multiple officers are supported and output traceability must be preserved.
- The 27-column classification for OS14000 was confirmed at **19 Active, 8 Inactive**; `Kategori1`, `Kategori2`, and `Kategori3` are **Inactive**. `Jawatan`, `Gred`, and `Kadar` are Active reference fields.
- The **#P7OS seven-step OS design process is paused**. In particular, its input design, remaining detailed field rules, overall calculation specification, and granular output mapping are not finalised. Preserve approved work to date; do not reopen the process implicitly.

## 5. Preservation and scope

- **Markdown decision record:** this consolidated checkpoint + the existing Program/ Aktiviti/ Tujuan document + AD-008.
- **Machine-readable reference:** shared Reference 07 salary CSV. A dedicated OS14000 salary CSV would be unnecessary duplication.
- This consolidation makes **no changes** to source CSV values, master OS catalogue, PostgreSQL, Laravel, AD-008 or any other Architecture Decision.
- Further #P7OS work must resume only when separately instructed, preserving the confirmed Kadar formula and previous decisions.


## Owner-approved amount and rounding — 18 November 2026

The owner instructed resumption of OS14000 discussion, confirmed the overall formula and independent chargeable-day input, then explicitly approved the proposed rounding approach:

- **Kadar sejam = Gaji Median × 12 ÷ 2504**, from the exact selected shared Jawatan/full SSPA Gred reference.
- **Anggaran Setahun = Kadar sejam × Jam sehari × Bilangan Hari × Kekerapan**.
- Bilangan Hari is entered independently; activity dates do not automatically determine chargeable days.
- Keep the hourly rate unrounded through multiplication. Round each final calculated amount to **two decimal places using half-up rounding**. Use exact arithmetic; premature truncation or rounding of a repeating division is not permitted.
- Monetary display remains right-aligned/tabular, grouped with two displayed decimals (for example `4,320.00`); the displayed hourly rate must not replace the unrounded calculation basis.
- Example approved in discussion: 3 hours/day × 2 days/occurrence × 12 occurrences = 72 annual hours for the officer.

These later approvals supersede only the earlier unresolved overall-formula/rounding statements and historical discussion pause. They do not decide eligibility, time-fraction limits, granular output expansion/count or allocation of rounding across generated output rows. In particular, rounding an annual source amount does not approve summing separately rounded daily amounts. Resolve the output mapping and reconciliation before implementation; do not infer dates for repeated occurrences. Existing stable source IDs, shared personnel reference and programme/purpose choices remain unchanged. No application, source CSV or database change is made by this decision record. AD-008 remains Draft.


## Owner-approved single-row output — 18 November 2026

After clarification, the owner confirmed keeping **one entry and one export/output row per saved officer-level Butiran Anggaran**, retaining Jam sehari, Bilangan Hari and Kekerapan as multipliers. Do not expand days × occurrences into daily rows. Example: Ahmad, 3 hours/day, 2 days and frequency 12 remains one row representing 72 annual hours. No individual daily entries or fabricated occurrence dates are required. Each separate saved Butiran retains its own stable identity even when the same officer is named more than once; no deduplication by officer.

Apply the approved unrounded hourly-rate calculation and round the final row amount once to two decimals, half-up. This resolves the prior output-expansion/count and associated daily-rounding question for this model; no daily allocation/reconciliation is needed. Preserve existing canonical field identities and programme/purpose references. Remaining detailed field validation, programme inheritance and official calculation/export integration are not implemented or newly approved by this documentation checkpoint. AD-008 remains Draft.


## Superseding owner approval — monthly frequency and months (18 November 2026)

The owner explicitly approved reconciling the active Bilangan Bulan field with the annual amount by defining **Kekerapan as occurrences per month**:

**Anggaran Setahun = (Gaji Median × 12 ÷ 2504) × Jam sehari × Bilangan Hari setiap kejadian × Kekerapan sebulan × Bilangan Bulan.**

This supersedes the earlier four-factor amount formula and annual-frequency interpretation recorded above; those earlier approvals are historical only. Bilangan Bulan is an independent manual calculation input, not informational and not derived from dates. The repeated-work pattern has the same entered hours/day and days/occurrence for each entered monthly occurrence. Example: 3 hours/day × 2 days/occurrence × 1 occurrence/month × 12 months = **72 annual hours**, multiplied by the unrounded hourly rate.

Retain one saved officer-level Butiran per input row and one attributable output/export row containing the factors; do not expand into daily rows or deduplicate separate entries naming the same officer. Keep exact arithmetic through the hourly-rate division/multiplication and round the final row amount once to **two decimals, half-up**. Display-only grouped/tabular two-decimal money must not become the calculation basis. Dates remain independent of chargeable days and months, subject to existing Kitaran-year limits. Preserve approved personnel and programme/purpose references. No new eligibility, caps or rounding rules.

This checkpoint updates documentation only. Source-editor implementation, remaining field rules, programme inheritance and official calculation/export integration remain pending; AD-008 remains Draft.


## Batch 047 implementation in progress

Source editor implements the latest monthly formula, final-only half-up rounding and one source per officer entry. Four original mappings, salary source, purposes and independent date/factors retained. No daily outputs are generated. Programme choice is scoped reference selection; full inheritance/association, shared context classifications and official financial/export integration remain pending. Verification/merge/local acceptance pending; see Batch 047 development record. This does not finalise AD-008.
