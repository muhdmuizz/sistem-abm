# AD-008 — OS Catalogue & Calculation Framework

**Status:** Draft — shared technical framework documented; OS-specific financial policy and mappings pending verification  
**Date:** 11 November 2026  
**Related:** AD-002, AD-003, AD-004, AD-007, AD-009, AD-010, AD-011, AD-012

## Scope and evidence boundary

Define the common OS catalogue, financial calculation, inheritance, validation and output contracts used by the single AD-004 Anggaran wizard. **This draft does not approve individual OS rates, eligibility policies, formulas, object-code mappings, or the official payroll Excel column schema.** Those require authoritative reference templates and/or verified source data.

At the time of drafting, GitHub `main` contains architecture documentation only; the Laravel source and official OS spreadsheets were not available in that branch. Do not infer current implementation from these architecture notes.

## 01 — Canonical entity and catalogue

- The hierarchy remains **PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran**.
- **Anggaran (OS)** is one transactional entity per applicable OS within a Bidaan. It owns multiple stable-ID Butiran Anggaran; no parallel OS transaction or financial cart is introduced.
- An OS catalogue is **reference/configuration data**, not another financial transaction layer. It identifies OS codes/variants and applicability to authorised Aktiviti Kewangan and provides the appropriate wizard definition.
- OS availability comes from verified catalogue and organisational reference data; never hardcode arbitrary caps or assume a worksheet title uniquely identifies an OS.
- Preserve the AD-003 separation of OS activation state from completeness. Inactive OS retains data but is excluded from current Bidaan totals and readiness.

## 02 — Shared wizard and OS-specific definition contract

All applicable OS use the existing **AD-004 shared Anggaran wizard**. Its specialised definition may supply:

1. OS identity, supported variant and applicability context.
2. Source input fields, types, requiredness and conditional visibility.
3. Permitted input method and transformation from source inputs to one or more Butiran Anggaran.
4. Source reference dependencies, eligibility and validation rules.
5. Calculation inputs, exact rate source, effective-date policy, formula, decimal precision and rounding policy.
6. Inherited Program/ Aktiviti fields consumed, with dependency/recalculation rules.
7. Output mapping into the official granular financial data structure and relevant export worksheet.
8. Deterministic completeness criteria, explanatory validation errors and test examples.

Items 2–8 must be filled from verified OS-specific requirements before an individual OS can be declared production-complete. Do not create new mandatory end-user fields merely because this technical contract lists metadata.

## 03 — Programme inheritance and recalculation

- Program/ Aktiviti is the authoritative guided façade, not a second financial store.
- Every programme field and value is available to its associated OS wizard, including the unique programme ID, objectives, classifications, implementation details, package components, headcount breakdowns and authorised organisational context.
- The OS definition explicitly declares which inherited values affect its calculation; **availability of inherited data does not imply universal financial use**.
- Inherited programme-owned fields are read-only within the OS wizard; updates occur at programme level.
- Changes to programme data automatically trigger revalidation/recalculation for affected **editable draft** Butiran Anggaran, preserving unrelated OS-specific inputs where valid and clearly identifying changed amounts.
- Submitted and finalised snapshots remain immutable; changes after submission follow the approved return/Pindaan/reopening rules.
- An OS checkbox cannot be unchecked when saved programme-associated Butiran exist until authorised deletion occurs. Automatic locked OS selections for Pakej are governed by verified OS-specific applicability rules.

## 04 — Calculation execution and financial integrity

- Treat user-entered source inputs and computed financial outputs as distinct data. Users do not arbitrarily edit derived amounts.
- Calculate deterministically from validated inputs, inherited context, verified rates and the applicable OS definition/version.
- Store financial amounts as exact decimals; do not use binary floating-point arithmetic for monetary totals. No universal rate, multiplier, rounding rule or negative/zero rule is assumed in this draft.
- A single source input may generate multiple stable, attributable granular Butiran output rows where the verified OS specification requires it.
- Each eligible valid Butiran contributes to its Anggaran total; active Anggaran totals contribute to the Bidaan total. Incomplete/blank rows do not inflate totals.
- Record calculation inputs and applicable rule/reference version in submitted immutable snapshots so historical figures remain reproducible even after rates/reference data change.
- Use transactions and safe concurrency checks for saving, recalculation and state transitions, consistent with AD-011.

## 05 — Validation and completion

- Incomplete Butiran may be saved as draft. Completion requires the verified OS-specific inputs, references, eligibility and calculation to pass validation.
- Anggaran completeness remains automatic under AD-004: **Belum Mula**, **Belum Lengkap**, **Lengkap**. No manual complete button.
- All retained Butiran for an active OS must be complete/valid; at least one qualifying record must exist. At least one active OS is required for a nonzero Bidaan submission under AD-003.
- Validation is enforced server-side, not solely in the UI. Reactivation and changes to relevant source/reference data trigger revalidation.
- Programme-level completion remains separate from overall OS completion, as finalised in AD-007.

## 06 — OS-specific reference collection and readiness

The following are **known candidate OS families/variants from the project discussions**, not an independently verified final catalogue: Gaji & Emolumen OS11000–OS13000; OS14000; OS15000; OS21000; OS22000; OS23000; OS24000; OS25000; OS26000; OS27000; OS28000; OS29000; OS42000. Actual variants, applicability and object-code mappings must be reconciled against official reference files before finalisation.

Known previously discussed specialisation requiring verification:
- **OS14000:** officer/job/grade, applicable rate source and multi-row generation; previously discussed median-salary-based calculation must not be treated as validated policy without the authoritative salary reference and formula confirmation.
- **OS21000:** travel-related components may require independent eligibility, quantities and rates rather than deriving directly from programme headcounts. Exact rules remain pending.
- **Pakej:** OS29000/OS42000 auto-selection and components depend on verified programme and OS applicability; do not invent a blanket mapping.
- **Gaji & Emolumen:** payroll export uses a separate official schema, which has not yet been supplied.

For each OS, complete an evidence-backed specification containing its authoritative source, effective policy, input/derived fields, calculation examples, inheritance dependencies, validation, granular output mapping and tests. Missing policy or reference data is an explicit **blocked** item, not a guessed default.

## Confirmed OS14000 design decision — officer-level source records

**Decision:** Approved by the system owner on 11 November 2026.

- **Each officer is independently saved as a Butiran Anggaran source record** within the applicable OS14000 Anggaran. The record has its own stable identity and is individually editable and recalculable, subject to existing permissions and submission-state restrictions.
- The system generates the required **granular financial calculation/output rows** from that officer's validated inputs. Every generated row remains traceably linked to the originating officer-level Butiran Anggaran record.
- Recalculating one officer's inputs updates only the affected current editable calculated outputs and dependent totals, without silently changing unrelated officers' records.
- Multiple officers can be entered and saved within the shared AD-004 wizard. Copying an officer record creates a new source identity, never reuses the original identity.
- Historical submitted snapshots preserve the officer inputs and their generated financial outputs; subsequent editing or deletion in an authorised revision cannot rewrite earlier submissions.
- **Not decided by this approval:** the exact OS14000 formula, salary reference, grade/rate mapping, granular expansion count, or export column mapping. Those remain subject to authoritative financial-reference verification.

This is an **approved OS14000-specific design decision within an otherwise Draft AD-008**, not approval or closure of the entire AD.

## 07 — Export compatibility

- Preserve AD-010 official scopes: **Keseluruhan Data**, **Bidaan Lengkap**, **OS Terpilih**.
- The complete workbook has **Gaji & Emolumen** (OS11000–OS13000, official schema pending) and **Perbelanjaan** (OS14000–OS29000 and OS42000, official 27-column granular structure).
- Selected OS are exported only in their applicable worksheet format. Do not introduce extra export filters, empty-sheet rules or new worksheet conventions.
- Analisis has no Excel export and retains its approved filters and historical PTJ-level comparison.

## 08 — Verification gates before finalising AD-008

1. Locate the official OS catalogue/templates and verify each applicable OS and variant.
2. Verify the authorised rate and eligibility references, effective dates and source ownership.
3. Specify OS-specific source inputs, inherited dependencies, formulas, precision/rounding and row expansion.
4. Verify all 27 Perbelanjaan column mappings and obtain the separate official Gaji & Emolumen template without inventing columns.
5. Produce representative normal, boundary, invalid and recalculation examples for each applicable OS.
6. Confirm financial totals reconcile with canonical Butiran and immutable submitted snapshots.
7. Review the resulting complete OS-specific specifications before changing this AD from **Draft** to **Finalised**.

## Status and next action

The shared technical framework is documented; the **OS-specific catalogue and calculation policies remain unverified**. Do not describe AD-008 as approved/finalised or the application as implemented. Next action is to obtain and reconcile authoritative OS templates and rate/reference sources, then complete the individual OS mappings inside this same AD rather than creating extra architectural branches.
