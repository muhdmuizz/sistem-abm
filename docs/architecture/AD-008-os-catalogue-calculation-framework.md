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

### Row identity clarification — applies to every OS (approved)

Each saved **Butiran Anggaran row** has a unique stable Butiran ID **even if multiple rows name the same officer or contain identical source values**. Officer identity is a data attribute, not the Butiran primary key. Every generated granular calculation/output row must be traceable to its exact originating Butiran ID. Do not deduplicate or aggregate separate source rows merely because their officer details match. This is a shared AD-004 rule; OS14000 is only one example.

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

## Confirmed workbook column classification and OS14000 applicability — 11 November 2026

**Decision status:** Confirmed by system owner for column classification and OS14000 applicability only. **AD-008 remains Draft.** This is a documentation baseline, not implementation approval or financial-policy verification.

### Standardised Perbelanjaan dataset

The owner designed one standardised set of **27 headers**, in the order below, for the applicable Perbelanjaan OS. Column meaning is **contextual by OS**, and **not every column is active for every OS**. In particular, `KATEGORI1`–`KATEGORI3` do not have universal meanings across OS.

These positions describe the 27-column canonical financial dataset; a source workbook worksheet may include additional presentation columns (for example, `BIL.`). Do not infer the official export worksheet position from a template's ancillary columns. AD-010 export structure remains unchanged.

| No. | Canonical header | Confirmed primary classification | OS14000 applicability |
| ---: | --- | --- | --- |
| 1 | OBJEK SEBAGAI | Shared reference | Active |
| 2 | JPNT/ PPD | Shared reference | Active |
| 3 | SEKTOR/ SEKOLAH | Shared reference | Active |
| 4 | NAMA PROGRAM/ AKTIVITI/ TUJUAN | Input / Inherited | Active |
| 5 | TARIKH MULA | Input / Inherited | Active |
| 6 | TARIKH TAMAT | Input / Inherited | Active |
| 7 | TEMPOH | Calculated | Active |
| 8 | NAMA | Input / Inherited | Active |
| 9 | JAWATAN | Shared reference | Active |
| 10 | GRED | Shared reference | Active |
| 11 | KATEGORI1 | OS-specific reference | Inactive |
| 12 | KATEGORI2 | OS-specific reference | Inactive |
| 13 | KATEGORI3 | OS-specific reference | Inactive |
| 14 | UNIT | OS-specific reference | Inactive |
| 15 | GRED PENCERAMAH/ FASILITATOR | Shared reference | Inactive |
| 16 | BIL. UNIT | Input / Inherited | Inactive |
| 17 | LOKASI URUSAN RASMI | Shared reference | Inactive |
| 18 | KADAR | OS-specific reference | Active |
| 19 | JARAK | Input / Inherited | Inactive |
| 20 | KEKERAPAN | Input / Inherited | Active |
| 21 | JAM | Input / Inherited | Active |
| 22 | BILANGAN HARI | Input / Inherited | Active |
| 23 | BILANGAN BULAN | Input / Inherited | Active |
| 24 | ANGGARAN JUMLAH SETAHUN | Calculated | Active |
| 25 | KATEGORI SUMBER PERUNTUKAN | Shared reference | Active |
| 26 | KATEGORI PROGRAM | Shared reference | Active |
| 27 | KATEGORI PESERTA | Shared reference | Active |

**Classification totals:** 10 Shared reference; 5 OS-specific reference; 10 Input / Inherited; 2 Calculated. **OS14000 applicability totals:** 19 Active; 8 Inactive; no Conditional or Unverified columns in the owner's confirmation.

The displayed supplementary wording of header 4 is `(DIEJA PENUH, TANPA SINGKATAN, SEMUA HURUF BESAR)`; headers 5 and 6 display `(hh/bb/tttt)`. These are part of the template's explanatory header text and are recorded here without expanding validation policies.

### Interpretation and limits of approval

- **Shared reference** means the listed column can draw from a common reference dataset where applicable across OS. This confirmation does **not** approve any particular underlying dataset, consolidation operation, mandatory status, or OS-specific filtering/eligibility rule.
- **OS-specific reference** means its reference context can differ by OS. It does not establish an exact workbook `Ref.` worksheet, lookup formula, rate source, or financial rule.
- **Input / Inherited** is a primary column classification, not a declaration that values must always be manually entered. Preserve AD-007 programme ownership and inheritance.
- **Calculated** identifies a derived column, but does not approve the calculation algorithm, rounding, rate, or precision for any OS.
- Applicability is independent from classification: a shared-reference column may be inactive for an OS; an active column is not automatically mandatory. Inactive is not automatically hidden.
- Only OS14000 has a user-confirmed 27-column applicability mapping in this record; do not infer the other OS mappings.
- Preserve AD-004's single shared Anggaran wizard, stable Butiran IDs, source-to-generated-output traceability, and AD-010's separately approved Excel extraction contract.
- Actual reference-sheet relationships, OS-specific field meaning, formula validation, effective rates, and granular mapping remain **pending workbook/financial verification**. No database, UI, or code implementation is authorised by these classifications.

### Working sequence (analysis, not a new architecture decision)

1. Confirm per-OS active/inactive/conditional applicability against the 27 canonical headers.
2. Analyse shared and OS-specific references using worksheet validations, named ranges and formula relationships.
3. Document OS-specific inputs, calculations, inherited values, and granular output mapping; flag unverified policy.
4. Review discrepancies and proposed refinements before any further AD-008 decision or implementation.

**Change-control note:** This addition records only decisions explicitly confirmed in the conversation on 11 November 2026; the remainder of this Draft AD remains unchanged.

## #P7OS — Seven-Step OS Design Protocol (confirmed 12 November 2026)

**Reusable instruction:** `#P7OS OS14000`, `Use #P7OS for [OS]`, or `Continue #P7OS`.

**Purpose:** Apply one repeatable, ordered design and specification process to each applicable OS under AD-008. This is an agreed process, **not an approval of any individual OS financial policy, formula, database design, or implementation**. AD-008 remains **Draft**.

| Step | Stage | Output |
| --- | --- | --- |
| **01** | **Understand the OS** | OS Functional Profile — purpose, expenditure scope, relevant users, and whether entries originate from Program/ Aktiviti, direct entry, or both. |
| **02** | **Decide the Input Design** | Approved OS Interaction Design — select and refine form, spreadsheet-style, hybrid, or specialised interaction *within* the existing shared AD-004 Anggaran wizard; clarify multiple entries, Program/ Aktiviti inheritance, and Senarai Butiran. |
| **03** | **Confirm Active Columns** | OS × 27-Column Applicability Matrix — classify each column Active, Inactive, or Conditional and determine OS-contextual meaning. |
| **04** | **Define Fields and References** | OS Field & Reference Specification — input types, requiredness, conditional fields, inherited values, shared/OS-specific reference sources, dropdown and lookup dependencies. |
| **05** | **Define Financial Calculations** | OS Calculation Specification — source inputs, verified rates and eligibility, exact formulas, effective dates, rounding/precision and representative test cases. Workbook behaviour alone does not approve policy. |
| **06** | **Define Granular Financial Outputs** | OS Output Mapping — map each saved Butiran Anggaran to its attributable generated 27-column Perbelanjaan output rows, preserving stable originating Butiran ID and reconcilable totals. |
| **07** | **Review and Finalise the OS Specification** | Owner-confirmed complete OS specification within Draft AD-008, recording verified evidence and unresolved issues. Confirmation of one OS does **not** finalise AD-008. |

### #P7OS operating rules

1. Follow the sequence **Understand → Design → Active Columns → Fields/References → Calculations → Granular Outputs → Review**, rather than choosing fields, formulas or references before the user journey and design.
2. Work **one OS at a time**, except when necessary to verify an actual shared reference across OS.
3. Preserve all earlier owner-confirmed decisions and map them into their #P7OS stage. Do not discard, silently amend or needlessly reopen those decisions merely because they were agreed out of sequence.
4. Preserve AD-004's **single shared wizard**, stable and independently saved Butiran IDs, one-to-many traceable granular outputs, AD-007 Program/ Aktiviti inheritance, AD-010 approved export structure, and AD-011 financial safeguards. Do not introduce Bakul Bidaan or additional financial transaction layers.
5. Distinguish **workbook evidence**, **previously approved architecture**, **owner-confirmed design**, and **unverified financial policy**. Do not invent rate, formula, eligibility, mandatory-field, or mapping rules.
6. Present decisions in manageable checkpoints for owner review. **Do not modify GitHub again without explicit authorisation**; commit consolidated approved findings only at authorised checkpoints.
7. Keep AD-008 **Draft** until its remaining catalogue, financial policies, mappings and verification gates have been completed and approved.

### Current OS14000 #P7OS position

- **Step 01:** Operational profile partially discussed; not yet completed.
- **Step 02:** Form-based, spreadsheet-style, and hybrid approaches discussed; **no input design selected or approved**.
- **Step 03:** **Confirmed** — 19 Active and 8 Inactive columns in the 27-column structure (see the earlier confirmed matrix).
- **Step 04:** Partially analysed. Owner **confirmed** `JAWATAN → GRED` as dependent selection: select JAWATAN first and display only applicable GRED options. Rate reference and remaining field rules require verification.
- **Steps 05–07:** Not completed; workbook formula observations are not approved financial calculations.

**Next action when invoking `#P7OS OS14000`: resume at Step 01 and then Step 02, retaining confirmed Step 03 and Step 04 results.**
