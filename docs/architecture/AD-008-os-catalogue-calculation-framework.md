# AD-008 — OS Catalogue & Calculation Framework

**Status:** Draft umbrella — multiple owner-approved OS specifications recorded; remaining per-OS gaps and implementation verification tracked below  
**Date:** 11 November 2026  
**Related:** AD-002, AD-003, AD-004, AD-007, AD-009, AD-010, AD-011, AD-012

## Scope and evidence boundary

Define the common OS catalogue, financial calculation, inheritance, validation and output contracts used by the single AD-004 Anggaran wizard. **The umbrella Draft status does not invalidate the later owner-approved OS decisions linked below.** Implement only their explicit approved scope; unresolved eligibility, rounding, mappings and the payroll schema remain separate gaps. Historical unverified statements below describe their original checkpoint and must not override later approvals.

At the time of drafting, GitHub `main` contains architecture documentation only; the Laravel source and official OS spreadsheets were not available in that branch. Do not infer current implementation from these architecture notes.


## Current contract and readiness reconciliation — 15 November 2026

**Current Perbelanjaan output is 30 columns**, as defined in the owner-approved revision below. The earlier 27-column matrices remain valid historical decisions: retain old 01–07; map old 08–27 forward by three; insert new 08–10 as Inactive/blank unless explicitly approved for that OS. Read mappings by canonical field identity, not stale position. Do not repeat the original applicability decisions or rename the two approved worksheets. The export contract does not require displaying 30 editable columns in the shared spreadsheet editor.

| OS scope | Approved work to preserve | Remaining implementation boundary |
| --- | --- | --- |
| OS14000 | [Consolidated references and Kadar formula](../references/OS14000-reference-consolidated.md); historical 19/8 applicability | Detailed work remains paused; overall amount, output expansion and precision/rounding are not finalised |
| OS15000 Faedah | [All 13 Kemudahan applicability and Kadar modes](../references/OS15000-faedah-faedah-kewangan-yang-lain-27-column-applicability.md) | Overall financial formulas and applicable BIPs percentage/rounding; programme inheritance |
| OS15000 Perubatan | [Column applicability and nine manual-rate categories](../references/OS15000-bayaran-perubatan-27-column-applicability.md) | Owner approved all-nine-category quantity × manual rate × frequency × manual months and kidney-treatment inclusive years/months/days on 18 November; source-editor verification pending, programme inheritance and official integration remain separate |
| OS21000 travel variants | [Direct-entry applicability and shared reference](../references/OS21000-perjalanan-sara-hidup-27-column-applicability.md) | Explicit unresolved subcategory applicability, blank-rate handling and calculations |
| OS21000 Pertukaran | [Final hierarchy, eight combinations, reference matching and formulas](../references/OS21000-tuntutan-perbelanjaan-pertukaran-category-combinations.md) | Batch 039 implements and verifies source editing/component-rate previews; special C mapping and official financial integration remain pending; preserve separation from OS22000 |
| OS22000 | [One Anggaran/two paths, corrected source ownership and KADAR × 1](../references/OS22000-unified-kategori-decisions.md) | Batch 040 implements the unified two-path source editor and targeted legacy-identity reconciliation; special grade C remains unmapped and official financial integration remains pending |
| OS23000 | [Both paths/seven combinations and monthly KADAR × 12](../references/OS23000-perhubungan-utiliti-reference.md) | Batch 031 removes obsolete Utiliti Pukal test budgets/reference and reconciles eight source mappings to the Batch 029 canonical editor; official financial integration remains pending |
| OS24000 | [Umum](../references/OS24000-sewaan-umum-reference.md) and [Mesin Fotostat](../references/OS24000-sewaan-mesin-fotostat-reference.md) approved mappings/formulas | Batches 037/038 implement Sewaan Umum and Fotostat/Riso source editing, including independent calendar durations and paired Bacaan Meter linkage. Official financial integration remains pending |
| OS25000 | [Four categories, exact reference UNIT/KADAR, quantity formula](../references/OS25000-bahan-makanan-minuman-reference.md) | Batch 023 implements typed source editing and exact row preview tests; official calculation/output/completeness integration remains pending |
| OS26000 | [Both paths, UNIT mapping, 2028 planning rates and proposal governance](../references/OS26000-bahan-mentah-reference.md) | Batches 041/042 implement baseline references and one-card row-selected Pukal/Bukan Pukal source editing; proposal approval/publication, submission safeguards and official financial integration remain pending |
| OS27000 | [Office supplies](../references/OS27000-bekalan-alat-pejabat-reference.md), [uniform](../references/OS27000-pakaian-seragam-reference.md), [Touch n Go](../references/OS27000-bekalan-dan-bahan-lain-touch-n-go-reference.md) approved variants | Pakaian Seragam/Touch ’n Go source adapters implemented through Batch 028; Batch 044 adds the baseline Bekalan Alat Pejabat editor. Official financial integration and shared proposal governance remain pending |
| OS28000 | [Conditional categories, mixed Kadar and formula](../references/OS28000-penyelenggaraan-reference.md) | Batch 026 implements typed source editing/mixed-rate exact previews; official output/completeness remains pending |
| OS29000 | [Umum](../references/OS29000-umum-reference.md), [meter](../references/OS29000-bacaan-meter-mesin-fotostat-reference.md), [Pakej](../references/OS29000-pakej-reference.md), [MySTEP](../references/OS29000-mystep-reference.md) approved mappings/formulas | Batches 031/033/035/036 implement MySTEP, grouped Pakej/Umum and Bacaan Meter source templates; programme inheritance and official financial integration remain pending |
| OS42000 | [TASKA](../references/OS42000-subsidi-yuran-taska-reference.md) and [shared SBK template](../references/OS42000-utiliti-pendidikan-rendah-reference.md) approved rules | Batch 024 implements SBK typed source editing/exact previews for both approved activities; Batch 025 implements TASKA typed sources/exact previews under PPN/010102; official output/completeness remains pending; AMal stays excluded |
| Gaji & Emolumen | Separate existing worksheet boundary | Official payroll schema still pending |

Approval of a mapping/formula is not proof of complete precision, eligibility, implementation or production readiness. Retain explicitly recorded gaps; do not invent missing policies. Common spreadsheet and draft persistence work can proceed without reopening approved decisions or silently enabling unresolved calculations.

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
- Monetary UI presentation follows [AD-013 Section 05.1](AD-013-system-ui-ux-interaction-design.md#051-monetary-display-contract--owner-clarification-15-november-2026): tabular/right-aligned values with thousands grouping and two displayed decimals (`4,320.00`). Display formatting must preserve exact source/reference/calculation values and cannot establish a financial rounding policy or alter export numeric semantics.
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
- The complete workbook has **Gaji & Emolumen** (OS11000–OS13000, official schema pending) and **Perbelanjaan** (OS14000–OS29000 and OS42000, current approved 30-column granular structure).
- Selected OS are exported only in their applicable worksheet format. Do not introduce extra export filters, empty-sheet rules or new worksheet conventions.
- Analisis has no Excel export and retains its approved filters and historical PTJ-level comparison.

## 08 — Verification gates before finalising AD-008

1. Locate the official OS catalogue/templates and verify each applicable OS and variant.
2. Verify the authorised rate and eligibility references, effective dates and source ownership.
3. Specify OS-specific source inputs, inherited dependencies, formulas, precision/rounding and row expansion.
4. Verify all 30 current Perbelanjaan column mappings by field identity and obtain the separate official Gaji & Emolumen template without inventing columns.
5. Produce representative normal, boundary, invalid and recalculation examples for each applicable OS.
6. Confirm financial totals reconcile with canonical Butiran and immutable submitted snapshots.
7. Review the resulting complete OS-specific specifications before changing this AD from **Draft** to **Finalised**.

## Status and next action

The shared framework and multiple approved OS-specific decisions are documented. Use the current readiness summary below and each authoritative reference, preserving approved mappings/formulas. AD-008 as a whole remains Draft; documentation approval is not application implementation or production verification. Resolve only actual per-OS gaps; do not restart completed column or formula discussions.

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
| **02** | **Decide the Input Design** | Approved OS Interaction Design — spreadsheet-like inline editing is the initial default under the later AD-013 decision; refine its OS-specific controls *within* the shared AD-004 wizard. Any alternative requires later individual approval; clarify multiple entries, Program/ Aktiviti inheritance, and Senarai Butiran. |
| **03** | **Confirm Active Columns** | OS × current canonical-column Applicability Matrix — classify each column Active, Inactive, or Conditional and determine OS-contextual meaning. |
| **04** | **Define Fields and References** | OS Field & Reference Specification — input types, requiredness, conditional fields, inherited values, shared/OS-specific reference sources, dropdown and lookup dependencies. |
| **05** | **Define Financial Calculations** | OS Calculation Specification — source inputs, verified rates and eligibility, exact formulas, effective dates, rounding/precision and representative test cases. Workbook behaviour alone does not approve policy. |
| **06** | **Define Granular Financial Outputs** | OS Output Mapping — map each saved Butiran Anggaran to its attributable generated current 30-column Perbelanjaan output rows (historically 27 before the approved revision), preserving stable originating Butiran ID and reconcilable totals. |
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
- **Step 02:** AD-013 §09.3–09.4 subsequently establishes spreadsheet-like inline editing as the initial default for every OS. OS14000's remaining detailed design stays paused; the common presentation decision is already approved.
- **Step 03:** **Confirmed** — 19 Active and 8 Inactive columns in the 27-column structure (see the earlier confirmed matrix).
- **Step 04:** Partially analysed. Owner **confirmed** `JAWATAN → GRED` as dependent selection: select JAWATAN first and display only applicable GRED options. Rate reference and remaining field rules require verification.
- **Steps 05–07:** Overall OS14000 calculation/output specification is incomplete. The later [consolidated reference](../references/OS14000-reference-consolidated.md) confirms KADAR = Gaji Median × 12 / 2504; preserve it. Precision/rounding and overall amount/expansion mapping remain unresolved.

**OS14000 detailed #P7OS work remains paused under its consolidated reference. Resume only when separately instructed, preserving approved decisions and the universal spreadsheet-like presentation.**

## Approved 30-column Perbelanjaan revision — 14 November 2026

**Owner-approved canonical export contract.** This section **supersedes the 27-column table above for current column positions**, while preserving that table as a historical baseline. This AD remains **Draft**. All previously approved OS decisions must be interpreted by **field identity**, not obsolete numerical positions.

The three new canonical fields were initially introduced for **OS24000 — Sewaan (Mesin Fotostat): Kontrak Keseluruhan**; the later owner-approved **OS29000 — Bacaan Meter Mesin Fotostat** specification also activates columns 08–10 for its own Kontrak Keseluruhan. These are the only two approved uses documented here. The original three date fields retain their canonical headers; only in the Mesin Fotostat editor are they presented as **Kontrak Semasa**.

| New no. | Canonical Excel header | Previous no. |
| ---: | --- | ---: |
| 01 | OBJEK SEBAGAI | 01 |
| 02 | JPNT/ PPD | 02 |
| 03 | SEKTOR/ SEKOLAH | 03 |
| 04 | NAMA PROGRAM/ AKTIVITI/ TUJUAN | 04 |
| 05 | TARIKH MULA | 05 |
| 06 | TARIKH TAMAT | 06 |
| 07 | TEMPOH | 07 |
| **08** | **TARIKH MULA KONTRAK KESELURUHAN** | **NEW** |
| **09** | **TARIKH TAMAT KONTRAK KESELURUHAN** | **NEW** |
| **10** | **TEMPOH KONTRAK KESELURUHAN** | **NEW** |
| 11 | NAMA | 08 |
| 12 | JAWATAN | 09 |
| 13 | GRED | 10 |
| 14 | KATEGORI1 | 11 |
| 15 | KATEGORI2 | 12 |
| 16 | KATEGORI3 | 13 |
| 17 | UNIT | 14 |
| 18 | GRED PENCERAMAH/ FASILITATOR | 15 |
| 19 | BIL. UNIT | 16 |
| 20 | LOKASI URUSAN RASMI | 17 |
| 21 | KADAR | 18 |
| 22 | JARAK | 19 |
| 23 | KEKERAPAN | 20 |
| 24 | JAM | 21 |
| 25 | BILANGAN HARI | 22 |
| 26 | BILANGAN BULAN | 23 |
| 27 | ANGGARAN JUMLAH SETAHUN | 24 |
| 28 | KATEGORI SUMBER PERUNTUKAN | 25 |
| 29 | KATEGORI PROGRAM | 26 |
| 30 | KATEGORI PESERTA | 27 |

### Universal export and migration compatibility

1. The **Perbelanjaan** worksheet uses one consistent 30-column contract for all applicable OS, with identical order and canonical headers; the **Gaji & Emolumen** worksheet and schema are unchanged.
2. Columns **08–10** apply to both **OS24000 Sewaan (Mesin Fotostat)** and **OS29000 Bacaan Meter Mesin Fotostat**, under their separately approved contract-period specifications. All other Perbelanjaan OS export these columns **blank**, not zero, unless a later OS-specific approval authorises their use.
3. Original 01–07 retain their canonical export headers and meaning. In the Mesin Fotostat editor only, columns 05–07 display **Kontrak Semasa** labels; column 20 displays **LOKASI MESIN FOTOSTAT**. These are not global header renames.
4. Original columns 08–27 shift forward **+3 positions**. This is a **breaking positional schema change**: update consumers, imports, exports, pivots, formulas, tests and column-index mappings before deployment. Do not assume old 27-column files are automatically interoperable.
5. Preserve field identity and previously approved applicability, formulas and rate references for **OS14000, OS21000, OS22000, OS23000 and OS24000 Sewaan (Umum)**; do not reinterpret or reopen their category decisions. This applies equally to any other documented OS.
6. Preserve stable Butiran Anggaran identities, financial snapshots, traceability, reference mapping and organisational permissions; **no data migration or implementation is authorised by this documentation revision**.
7. For Mesin Fotostat, independent Kontrak Semasa and Kontrak Keseluruhan periods each use calendar **tahun, bulan dan hari** with inclusive end dates. **BILANGAN BULAN remains independent manual input**, and approved ANGGARAN JUMLAH SETAHUN = **BIL. UNIT × KADAR × BILANGAN BULAN**.

### Verification gate before implementation

- Assert the exact 30 headers in the table above, with three new fields inserted immediately after previous column 07.
- Assert approved independent current/overall contract periods in OS24000 Sewaan Mesin Fotostat and OS29000 Bacaan Meter Mesin Fotostat; assert blank 08–10 for all other Perbelanjaan OS.
- Assert old column 24 ANGGARAN JUMLAH SETAHUN maps to new 27 without calculation changes for other OS.
- Assert current and overall contract durations calculate independently with inclusive ends, with leap-year and month-end boundary tests.
- Assert BILANGAN BULAN is never silently computed from either contract duration.
- Audit all approved OS mappings by canonical field identity and reconcile external pivot/import/export integrations for the new order.

**Change-control:** Owner-approved design specification documented only. AD-008 remains Draft; Laravel, PostgreSQL, reference CSVs and actual Excel exporter are not changed by this commit.

## OS26000 approved mapping and reference governance — 14 November 2026

The owner-approved OS26000 **Pukal/Bukan Pukal** 30-column applicability, per-path calculation, all 42 baseline UNIT assignments, 2028 planning fuel KADAR, and Superadmin-controlled global publication of approved **Bukan Pukal Cadangan Item Baharu** are specified in [OS26000 — Bekalan Bahan Mentah](../references/OS26000-bahan-mentah-reference.md#owner-approved-os26000-anggaran-specification--14-november-2026).

This OS-specific reference specification governs the distinct existing Pukal/Bukan Pukal Kod Aktiviti mappings without changing the shared AD-004 wizard or AD-008's canonical 30-column contract. Draft-only pending proposals cannot proceed to submission; approved items publish to the common reference catalogue for authorised users and require revalidation of affected drafts. The entire AD-008 remains **Draft**; these are documentation decisions, **not implementation**.

## OS27000 Bekalan Alat Pejabat approved specification — 14 November 2026

See [OS27000 Bekalan Alat Pejabat](../references/OS27000-bekalan-alat-pejabat-reference.md#owner-approved-30-column-anggaran-specification--14-november-2026) for the owner-approved 30-column applicability (9 Active / 4 Conditional / 15 Inactive / 2 Deferred), Program/ Aktiviti-only conditional inheritance, fixed Unit convention with canonical UNIT Inactive, **BIL. UNIT × KADAR** calculation, and the **Cadangan Item Baharu** workflow. The latter reuses OS26000 Bukan Pukal's Superadmin-only approval, pending-draft submission blocking, global publication, duplicate detection and audit requirements. This applies only to OS27000 Bekalan Alat Pejabat, does not change other OS27000 variants, and is documentation only; AD-008 remains **Draft**.

## OS27000 Pakaian Seragam approved specification — 14 November 2026

See [OS27000 Pakaian Seragam](../references/OS27000-pakaian-seragam-reference.md#owner-approved-30-column-anggaran-specification--14-november-2026): 11 Active, 17 Inactive and 2 Deferred columns in the canonical 30-column order; JAWATAN is a separate shared personnel reference; UNIT is fixed to **Pasang**; BIL. UNIT displays **BIL. PENGISIAN**; KEKERAPAN displays **Bil. Pasang**, fixed at **2** and not editable (owner correction, 15 November 2026); and **ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KEKERAPAN × KADAR**, with KADAR automatically selected from the existing four KATEGORI1 + JAWATAN combinations. Separate Kod Aktiviti and other OS27000 variants are unaffected. Documentation only; AD-008 remains **Draft**.

## OS27000 Bekalan dan Bahan-bahan Lain (Touch ’n Go) — approved 14 November 2026

See [OS27000 Touch ’n Go](../references/OS27000-bekalan-dan-bahan-lain-touch-n-go-reference.md) for the approved monthly budgeting specification across the existing three Kod Aktiviti: 9 Active, 19 Inactive and 2 Deferred columns; user-entered **TUJUAN**, **BIL. UNIT**, **KADAR BULANAN**, **BILANGAN BULAN**; **KEKERAPAN Inactive**; and **ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KADAR BULANAN × BILANGAN BULAN**. No category or reference-rate lookup is added; other OS27000 variants remain distinct. Documentation only; AD-008 remains **Draft**.

## OS28000 Penyelenggaraan dan Pembaikan Kecil Yang Dibeli — approved 14 November 2026

See [OS28000 Penyelenggaraan reference](../references/OS28000-penyelenggaraan-reference.md#owner-approved-30-column-anggaran-specification--14-november-2026) for the owner-approved **11 Active / 1 Conditional / 16 Inactive / 2 Deferred** column mapping. KATEGORI2 is conditional and blank for Tebang Pokok; KADAR preserves the existing eight manual/two fixed reference combinations; KEKERAPAN means occurrences per month, BILANGAN BULAN means budgeted months; **ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KADAR × KEKERAPAN × BILANGAN BULAN**. Reference data and system implementation unchanged; AD-008 remains **Draft**.

## OS29000 Umum owner-approved specification — 15 November 2026

See [OS29000 Umum](../references/OS29000-umum-reference.md#owner-approved-30-column-anggaran-and-calculation-specification--15-november-2026) for its **14 Active / 4 Conditional / 10 Inactive / 2 Deferred** applicability, six service-group UNIT assignments, universal **ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KADAR** formula, 18 automatic/four manual KADAR rules, approved hourly Penceramah/Fasilitator rates and the automatic **JAWATAN → GRED → GRED PENCERAMAH/ FASILITATOR** SSPA classification (including VU7). NAMA/JAWATAN/GRED are mandatory for Penceramah, Fasilitator and Bayaran Yuran only; inactive for Hadiah Penghargaan Pesara and other services. The original 22 source rows, other OS29000 variants and existing Kod Aktiviti mappings are unchanged. Documentation only; AD-008 remains **Draft**.

## OS29000 Bacaan Meter Mesin Fotostat — owner-approved 15 November 2026

See [OS29000 Bacaan Meter Mesin Fotostat](../references/OS29000-bacaan-meter-mesin-fotostat-reference.md#owner-approved-30-column-applicability-and-calculation--15-november-2026) for the OS24000 Mesin Fotostat-aligned **16 Active / 12 Inactive / 2 Deferred** canonical mapping, including Kontrak Semasa and Kontrak Keseluruhan date/duration fields, manual **KADAR per copy**, category choices **Berwarna / Hitam dan Putih**, and editor labels **BIL. SALINAN** (monthly copies) and **LOKASI MESIN FOTOSTAT**. Approved formula: **ANGGARAN JUMLAH SETAHUN = BIL. SALINAN per month × KADAR SESALINAN × BILANGAN BULAN**; months are manually entered and independent of contract durations. OS29000 and OS24000 remain separate financial identities and Kod Aktiviti. Reference CSVs and system implementation unchanged; AD-008 remains **Draft**.

## OS29000 Pakej — owner-approved 15 November 2026

See [OS29000 Pakej](../references/OS29000-pakej-reference.md#approved-30-column-anggaran-and-calculation-specification--15-november-2026) for its **14 Active / 14 Inactive / 2 Deferred** column map and four approved components (Penginapan, Makan Minum, Sewaan Dewan, Sewaan Peralatan). KATEGORI1 selects the component; UNIT is respectively Bilik/Orang/Dewan/Unit, all KADAR values are manually entered with prescribed per-night/per-day bases, and **ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KADAR × BILANGAN HARI**. For Penginapan, column 25 displays **BIL. MALAM** and counts chargeable nights while retaining its canonical BILANGAN HARI export header. Do not automatically infer chargeable duration from Program/ Aktiviti dates. Preserve the approved four-component Program/ Aktiviti reference and distinct OS29000 variants. Documentation only; AD-008 remains **Draft**.

## OS29000 MySTEP — owner-approved 15 November 2026

See [OS29000 MySTEP](../references/OS29000-mystep-reference.md#owner-approved-30-column-mystep-anggaran-specification--15-november-2026) for the unified MySTEP / Guru Ganti / Pembantu Pengurusan Murid (PPM) Ganti template with **15 Active / 13 Inactive / 2 Deferred** canonical columns. Column 04 records **TUJUAN LANTIKAN** (all six source choices available across all three KATEGORI1 choices); column 14 is KATEGORI1; column **15 KATEGORI2 explicitly records TARAF AKADEMIK**, superseding its earlier exclusion from KATEGORI2. NAMA (11), JAWATAN (12) and GRED (13) are Inactive. UNIT (17) = Orang; BIL. UNIT (19) is personnel headcount; KADAR (21) automatically uses the existing **JUMLAH per person per month** reference; BILANGAN BULAN (26) is budgeted months; **ANGGARAN JUMLAH SETAHUN (27) = BIL. UNIT × KADAR × BILANGAN BULAN**. Original reference CSVs, three catalogue identities, Kod Aktiviti mappings and implementation unchanged; AD-008 remains **Draft**.

## OS42000 Subsidi Yuran TASKA — owner-approved 15 November 2026

See [OS42000 Subsidi Yuran TASKA](../references/OS42000-subsidi-yuran-taska-reference.md#owner-approved-30-column-applicability-and-monthly-anggaran--15-november-2026) for **10 Active / 18 Inactive / 2 Deferred** canonical columns. Under PPN and Kod Aktiviti `010102`, column 11 NAMA means mandatory **Nama Ibu/ Bapa**; 17 UNIT = **Anak**; 19 BIL. UNIT displays **BIL. ANAK** (number of children); 21 KADAR is the existing automatic prescribed **RM180 per anak per month**; 26 BILANGAN BULAN is entered for the budget period; and **ANGGARAN JUMLAH SETAHUN = BIL. UNIT × KADAR × BILANGAN BULAN**. No individual child names, fixed one-child quantity, or unapproved per-parent row constraint is imposed. Existing one-row source reference CSV and other OS42000 variants unchanged. Documentation only; AD-008 remains **Draft**.

## OS42000 Bayaran Bil Utiliti SBK (Pendidikan Rendah) — owner-approved 15 November 2026

See [OS42000 Utiliti Pendidikan Rendah](../references/OS42000-utiliti-pendidikan-rendah-reference.md#owner-approved-30-column-applicability-and-calculation--15-november-2026) for **9 Active / 19 Inactive / 2 Deferred** canonical columns. This separate OS42000 variant, Kod Aktiviti `030228`, reuses **OS23000 Pukal** reference only: KATEGORI1 (14) fixed Pukal, KATEGORI2 (15) Air / Elektrik / Telefon/ Faks / Pembetungan, mandatory manual **monthly KADAR (21)**, fixed **BILANGAN BULAN (26) = 12**, and **ANGGARAN JUMLAH SETAHUN (27) = KADAR × 12**. No BIL. UNIT or KEKERAPAN multiplier; Bukan Pukal excluded. Existing shared reference data, Pendidikan Menengah mappings and application unchanged. Documentation only; AD-008 remains **Draft**.

## OS42000 SBK shared template — Pendidikan Menengah approval 15 November 2026

See [OS42000 Utiliti SBK shared template](../references/OS42000-utiliti-pendidikan-rendah-reference.md#shared-sbk-template-for-pendidikan-menengah--owner-approved-15-november-2026). The owner approved applying the existing **Pendidikan Rendah (`030228`) SBK Pukal** Anggaran definition to **Pendidikan Menengah (`030310`)**, while keeping the two Kod Aktiviti and financial entries distinct. Both use **9 Active / 19 Inactive / 2 Deferred** canonical columns; KATEGORI1 fixed **Pukal**, KATEGORI2 the four OS23000 Pukal utilities, manual monthly **KADAR**, fixed **BILANGAN BULAN = 12**, and **ANGGARAN JUMLAH SETAHUN = KADAR × 12**. Reuse the common existing reference CSV; do not introduce Bukan Pukal or new references. **Asrama Malaysia (AMal), `030318`, remains excluded — reference only**. Documentation only; source mappings, Laravel, database and Excel exporter unchanged; AD-008 remains **Draft**.

## Batch 027 implementation checkpoint — 15 November 2026

[PR #64](https://github.com/muhdmuizz/sistem-abm/pull/64) merged at `dffc2c44ebcf50f757cb1588488b6924f0b95c55`. Pakaian Seragam source drafts now use existing PPN/010808 mapping, shared Reference 07 Jawatan, four approved category/job rates, fixed Pasang and exact Bil. Pengisian × Bil. Pasang × Kadar preview. Actual title-case headers are implemented across OS25000/SBK/TASKA/OS28000/Pakaian Seragam, preserving canonical Excel/reference headings and accounting-style money.

Tested application head `bea2bed2522e2b9fb816fb1a6a0a13d9d52bed11`; [CI 43665138171](https://github.com/muhdmuizz/sistem-abm/actions/runs/43665138171): 452 tests / 3626 assertions, PHP 8.4.27 / PostgreSQL 18.7 isolated testing database; build/formatter/Chromium and representative desktop/mobile review passed. See [Batch 027](../development/batch-027-uniform-headers.md) for evidence limits. Local acceptance pending. No migration/seed or new policy. Other OS27000 variants, official output/totals/completeness and programme inheritance remain pending; Steps 02/03 remain in progress. AD-008 remains Draft.

## Batch 028 checkpoint — 15 November 2026

[PR #66](https://github.com/muhdmuizz/sistem-abm/pull/66) merged into dev at `496dbfb0d9cb30f982bc02f8470f6b46773b5269`. Bil. Pasang is now fixed at **2** on the screen and server, superseding the previous editable source input. Valid v1 drafts remain readable; read does not rewrite stored input, current preview uses two, and explicit audited save upgrades the row to v2. OS27000 Touch ’n Go (canonical Bekalan dan Bahan-bahan Lain) now has source editing under existing PPN/010809, Pendidikan Menengah/030304 and Pengurusan Asrama/030316 mappings, with exact Bil. Unit × Kadar Bulanan × Bilangan Bulan previews. No frequency/category/unit input or new rate policy.

Tested application head `c780d2dbb6cb5dcb40d10e6e7e7b78fcb7033a28`; [CI 43669111898](https://github.com/muhdmuizz/sistem-abm/actions/runs/43669111898): **458 tests / 3701 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB, build/formatter/Chromium and representative desktop/mobile review passed. [Batch 028](../development/batch-028-fixed-pairs-touchngo.md) records review limits. No migration/seed; local acceptance pending. Official outputs/totals/completeness, programme inheritance, remaining OS and submission remain pending; Steps 02/03 remain in progress and AD-008 remains Draft.

## Batch 029 implementation checkpoint — 15 November 2026

[PR #68](https://github.com/muhdmuizz/sistem-abm/pull/68) merged at `4c5a112ff31892df04156c62d69aeb13cb27a360`. Owner-corrected Pakaian Seragam Jawatan now offers only **Pembantu Khidmat Am / Pembantu Makmal**, using the existing rate subset of shared Reference 07. Server and paste guards reject other posts; older valid unrelated-job drafts remain preserved/repairable, with a reselection notice and explicit audited v3 replacement. Bil. Pasang remains fixed at 2.

OS23000 **Perhubungan dan Utiliti** source entry is implemented for its existing canonical mappings: seven dependent choices, manual monthly Kadar, fixed 12 months and exact Kadar × 12 previews. No legacy Utiliti Pukal identity/mapping merge is claimed; that entry remains unconfigured pending reconciliation. Shared SBK Pukal-only behaviour and other definitions remain intact.

Tested application head `f7cdd2553615c4620804fa21359348338c7b1887`; [CI 43670963928](https://github.com/muhdmuizz/sistem-abm/actions/runs/43670963928): **464 tests / 3802 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB, production build and formatter/Chromium passed. Representative final-head desktop/mobile visual review completed; [Batch 029](../development/batch-029-uniform-jobs-utilities.md) records limits. No migration/seed or role/mapping/CSV edits. Batches 028/029 local acceptance remains unconfirmed. Official outputs/totals/completeness, programme inheritance, remaining OS definitions and submission remain pending; Steps 02/03 remain in progress, AD-008 remains Draft.

## Batch 030 checkpoint — 15 November 2026

[PR #70](https://github.com/muhdmuizz/sistem-abm/pull/70) merged at `4458d0ec490de4492e9b6f008dd11615d8322257`. Bida Peruntukan now has one scoped searchable PTJ combobox with explicit selection, keyboard/no-result handling and native-select fallback. OS23000 shows one **Perhubungan dan Utiliti** preparation card using Batch 029's existing seven-path Pukal/Bukan Pukal editor. The older Utiliti Pukal standalone preparation card is removed; authorised old records remain under **Rekod sedia ada** within that card. Active older records retain read access; inactive ones are explicitly preserved without an unusable open link. New old-identity activation is blocked; old-only permissions do not grant canonical permissions. Other OS variants remain separate.

Tested application head `24e40b2cc29b5b0a309ca0d9d0ea91f258808578`; [CI 43674654094](https://github.com/muhdmuizz/sistem-abm/actions/runs/43674654094): **468 tests / 3842 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed and 15 new final-head desktop/mobile/fallback captures reviewed. [Batch 030](../development/batch-030-ptj-selector-utility-card.md) records limits. No migration/seed, financial record/permission merge, CSV edit, new OS editor or formula change. Local execution remains unconfirmed. Underlying legacy data reconciliation, official financial integration and programme/submission work remain pending; this is not a claim of physical-device/accessibility or production readiness.

## Batch 031 checkpoint — 15 November 2026

[PR #72](https://github.com/muhdmuizz/sistem-abm/pull/72) merged at `8ac02d13c0304a115783fa49190634e0b27812f9`. Owner explicitly confirmed there is **no real business data** in the current development system and authorised discarding obsolete OS23000 **Utiliti Pukal** test budgets. Batch 030's preservation panel/compatibility handling is removed. A targeted migration deletes obsolete outputs/Butiran/Anggaran and old OS permissions, repoints four existing Kod Aktiviti mappings to **Perhubungan dan Utiliti**, removes the obsolete reference and bumps affected Bidaan revisions. Current canonical budgets and unrelated records remain; no full reset or reference reseed. Catalogue: **32 identities / 30 eligible / 2 excluded**; mappings: **105 / 103 eligible**, including eight actual OS23000 source mappings. Historical 33/31 counts are superseded by this inventory.

The approved shared **OS29000 MySTEP** source grid is now implemented for all three existing variants/seven mappings, using the unchanged four JUMLAH monthly rates, independent categories/purposes, Kitaran-year dates and manual budgeted months. Exact preview = Bil. Unit × Kadar × Bilangan Bulan; date duration does not determine months. Title-case headers and accounting-style money retained.

Tested application head `005776cec1c85ebd3cdbabcb889b04925e31f228`; [CI 43679552938](https://github.com/muhdmuizz/sistem-abm/actions/runs/43679552938): **474 tests / 3925 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated test DB, production build/formatter/Chromium passed. Twelve representative final-head desktop/mobile captures reviewed; [Batch 031](../development/batch-031-utility-cleanup-mystep.md) records limits. Local targeted migration/update remains pending. Official outputs/totals/completeness, programme inheritance and submission remain pending; AD-008 remains Draft.

## Batch 032 checkpoint — 15 November 2026

[PR #74](https://github.com/muhdmuizz/sistem-abm/pull/74) merged at `3494a318fda11ecc8468d26f13e3e2f8f56caff5`. OS29000 **Pakej (Mesyuarat/ Bengkel/ Kursus)** now has the approved direct source spreadsheet across its five existing activity mappings. Four components retain separate manual rate bases: Penginapan uses rooms/nights; Makan Minum people/days; Sewaan Dewan halls/days; Sewaan Peralatan units/days. Exact preview = Bil. Unit × Kadar × chargeable duration. Calendar dates/Tempoh do not set charged nights/days. Partial drafts, source precision, title-case headers and tabular/grouped/two-decimal money are retained. Shared validation keeps rate/duration descriptions alongside errors.

Tested application head `5dfb64a5fbeab18df512772ec89de6812802ceea`; [CI 43689211251](https://github.com/muhdmuizz/sistem-abm/actions/runs/43689211251): **480 tests / 4030 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Eleven representative final-head desktop/mobile captures reviewed from 18 Pakej captures (282 total). See [Batch 032](../development/batch-032-pakej-spreadsheet.md) for evidence and limits. No migration/seed or catalogue/mapping/permission/reference CSV changes. Local Batch 032 update and financial acceptance remain pending. Batch 031 supplied local synchronisation/targeted cleanup migration is user-confirmed; detailed financial/role/device acceptance remains unverified. Official outputs/totals/completeness, programme inheritance and submission remain pending; Steps 02/03 remain in progress and AD-008 remains Draft.

## Batch 033 checkpoint — 15 November 2026

[PR #76](https://github.com/muhdmuizz/sistem-abm/pull/76) merged at `a9c51a29720d5d833aef86a51f2af568d075bffd`. A direct Pakej activity now holds shared workplace/name/dates once, with expandable component rows and an exact draft subtotal. Components retain separate quantity, manual rate basis and chargeable nights/days; dates never determine charged duration. Add/copy/remove/undo operate at both levels, nested errors reopen the affected group, and pending saves lock edits then restore controls on failure. Incomplete components leave the subtotal incomplete.

One grouped activity uses one existing Butiran source envelope (v2) with a component list and stable group-local UUIDs. This retains parent workplace ownership, transactional save/audit/revision guards and whole-activity pagination. Existing v1 component sources reopen separately as one-component activities; no name-based merge, new preservation interface, schema migration or reset. No rate/mapping/permission/reference CSV/canonical-column changes. Linking to existing Program/ Aktiviti, inheritance and official financial outputs/totals/completeness/submission remain pending; AD-008 remains Draft and Steps 02/03 remain in progress.

Tested application head `54ddebf271de5a979a089beaabd5cb0688ce2d1e`; [CI 43694224425](https://github.com/muhdmuizz/sistem-abm/actions/runs/43694224425): **485 tests / 4137 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Fifteen representative final-head screenshots reviewed from 39 Pakej captures (303 total). See [Batch 033](../development/batch-033-grouped-pakej.md) for coverage, resolved failures and visual-review limits. Local Batch 033 update/financial acceptance pending; Batch 032 local confirmation remains unreceived. No migration/seed or server restart required.

## Batch 034 checkpoint — 15 November 2026

[PR #79](https://github.com/muhdmuizz/sistem-abm/pull/79) merged at `b77a97df189d2a274d32af633a2712be3bedb746`. OS29000 **Umum** source entry is implemented for its five existing mappings, using the unchanged 22 reference rows (18 automatic/four manual) and shared 131 Jawatan/Gred pairs. Dependent personnel inputs derive the dedicated speaker/facilitator classification/rate; other services retain their approved units and rates. Inactive fields and automatic-rate overrides are rejected. Exact row preview = Bil. Unit × Kadar; partial drafts remain saveable with missing required preview inputs withheld. Title-case headers and accounting-style monetary presentation retained.

Tested `44a27bfda1c2046d322df43f12ff39f5e4111096`; [CI 43697537069](https://github.com/muhdmuizz/sistem-abm/actions/runs/43697537069): **491 tests / 4570 assertions**, PHP 8.4.27 / PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Twelve representative final-head state captures reviewed from 19 Umum captures (322 total); see [Batch 034](../development/batch-034-umum-spreadsheet.md) for coverage and limits. No migration/seed, CSV/rate/catalogue/mapping/permission changes. Local update/financial acceptance pending; previous local statuses unchanged. Programme inheritance, official outputs/totals/completeness and submission remain pending; Steps 02/03 remain in progress, AD-008 remains Draft. Pakej programme-only entry remains a documented proposal.

## Batch 035 checkpoint — 15 November 2026

[PR #81](https://github.com/muhdmuizz/sistem-abm/pull/81) merged at `c5bb00945e5ea10a0b16ccba13b5e67f46bc2b7e`. Owner approved Umum following Pakej's expandable activity/component pattern: workplace, purpose and dates are shared once; multiple service components retain their approved categories, conditional personnel, units, rates and exact calculations. Activity subtotals are withheld when any component preview is incomplete. Add/copy/remove/undo operate on components and whole activities. Existing v1 records reopen separately as one-component activities; explicit edits write v2 through existing atomic/audited guards. No name-based merging, migration, reset or reference/rate/catalogue/mapping/permission changes.

Application head `27791b5f619cb033936178079e5d03a02598054d` passed [CI 43700580952](https://github.com/muhdmuizz/sistem-abm/actions/runs/43700580952): **496 tests / 4,688 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB, production build, formatter and Chromium including Pakej regressions. Seventeen final desktop/mobile captures reviewed from 342 PNGs (39 Umum/39 Pakej); full coverage/limits in Batch 035. Local sync and financial acceptance remain pending, including prior unconfirmed batches. Steps 02/03 remain in progress; AD-008 remains Draft. Programme inheritance, official outputs/totals/completeness and submission remain pending; Pakej programme-only entry remains a proposal.

## Batch 036 checkpoint — 16 November 2026

[PR #83](https://github.com/muhdmuizz/sistem-abm/pull/83) merged at `0aba6d03b0ebb16d7137015f172d2c24e28c0836`. OS29000 Bacaan Meter source editing now supports the four existing mappings, unchanged shared print choices, monthly copies × exact manual per-copy rate × independent manual months, and separate inclusive calendar current/overall contract durations. Current dates follow Kitaran year; overall contracts may span years. OS24000 remains separate. No migration/seed/reset or reference/rate/catalogue/mapping/permission/export-schema changes.

Tested `ef1b7c2c21b21fb7686f629ca81e79d6a94fb117`; [CI 43793891272](https://github.com/muhdmuizz/sistem-abm/actions/runs/43793891272): **503 tests / 4,830 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. All 18 meter state captures plus two representative existing-editor captures reviewed from 360 PNGs; [Batch 036](../development/batch-036-bacaan-meter-spreadsheet.md) records coverage and visual limits. Local sync/financial acceptance remains pending, including earlier unconfirmed batches. Programme inheritance, official financial outputs/totals/completeness and submission remain pending; AD-008 remains Draft and Steps 02/03 remain in progress.

**UI sequencing approved:** Complete remaining OS editors, then pause for consolidated editor UI refinement before another major module. Fix usability defects as encountered and apply AD-013 throughout. This is an implementation checkpoint, not a change to financial or design approval rules.

## Batch 037 checkpoint — 16 November 2026

[PR #85](https://github.com/muhdmuizz/sistem-abm/pull/85) merged at `d95b86029a72a77da72e7e0932b6dadeb3f4be3f`. OS24000 Sewaan Umum source editor implements five existing mappings and all 64 approved paths, three-level dependent categories with cascading resets, blank Tanah leaves, distinct broadcasting labels and Berkontrak-only Kitaran-year dates/inclusive day duration. Exact draft preview = Bil. Unit × manual Kadar × manual Bilangan Bulan; dates never set months. No migration/seed/reset, reference/rate/catalogue/mapping/permission/export-schema changes. Photocopier rental remains separate and unimplemented.

Checkout access recovered without changing workflow/credentials/permissions after browser inspection confirmed existing settings were correct; precise transient upstream cause unconfirmed. [CI 43799773291 attempt 2](https://github.com/muhdmuizz/sistem-abm/actions/runs/43799773291) at `29932d87de94f89a018d01c15595a403b3337520`: **510 tests / 5,119 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Later commits changed documentation only. Reviewed 14 rental and three existing-editor captures from 382 PNGs / 22 rental captures; [Batch 037](../development/batch-037-sewaan-umum-spreadsheet.md) records coverage, earlier failures and review limits.

Local sync/financial acceptance remains pending; prior unconfirmed local statuses are unchanged. Official outputs/totals/completeness, programme inheritance and submission remain pending; AD-008 remains Draft and Steps 02/03 in progress. Complete remaining OS editors before consolidated editor UI refinement; usability defects fixed as encountered. No routine server restart required.

## Owner-approved Fotostat dependency and entry pathway — 16 November 2026

OS24000 can also use Program/ Aktiviti as an entry pathway; direct entry is not restricted by that note. Programme linkage/inheritance implementation remains separate.

OS24000 Sewaan Mesin Fotostat dan Riso activates its paired OS29000 Bacaan Meter atomically in the same Bidaan, respecting both permissions/catalogue mappings/Fasa/role guards. Direct meter activation is blocked. Paired deactivation retains drafts and links; explicit reactivation retains source-only drafts. Each saved rental item creates/updates one linked meter item, sharing workplace/category/location/current and overall contract dates. Meter copies, per-copy rate and months remain independent/initially blank. Rental is authoritative for shared details; no automatic financial copying or duplicate creation on repeated saves.

Existing unlinked meter drafts remain separate until explicit user selection/review. Linking preserves chosen meter financial inputs; only a financially blank generated meter may be explicitly replaced and deleted. Deletion of a rental requires confirmation displaying linked meter values and current revisions, then atomically audits/removes both sources/link. Direct copy/removal of linked meters uses rental instead; rental copy creates an independent pair. No name-based matching. Submitted/finalised and generated-output safeguards remain. These approved dependency rules supersede earlier standalone activation/source-ownership boundaries only for this pair; the financial identities/formulas and reference CSVs remain separate.

Implementation/verification is recorded in the Batch 038 checkpoint below; local acceptance remains pending.

## Batch 038 implementation checkpoint — 16 November 2026

[PR #87](https://github.com/muhdmuizz/sistem-abm/pull/87) merged at `9f294dc9a33c6b6dbb544e0505fa7b0bfe83a42d`. The approved rental editor and dependency/linkage rules above are implemented and cloud-verified at `75809c99be402bf0d0805837286557e2519d247a`: [CI 43817060723](https://github.com/muhdmuizz/sistem-abm/actions/runs/43817060723), 521 tests / 5,233 assertions, build/exact-money/Chromium passed. See [Batch 038](../development/batch-038-fotostat-meter-linkage.md) for guard coverage and reviewed/uninspected states. One additive linkage migration and local acceptance remain pending. Programme association/inheritance and official financial outputs/totals/completeness/submission remain deferred; direct entry remains available. No reference/rate/catalogue/mapping/export-schema changes. AD-008 remains Draft; this does not mark the broader AD implemented.

## Batch 039 checkpoint — 16 November 2026

[PR #89](https://github.com/muhdmuizz/sistem-abm/pull/89) merged at `874f7bf923fb9179231c38bae524e46b71f141b5`. OS21000 Tuntutan Perbelanjaan Pertukaran source editing supports eight approved category paths/five existing mappings, shared full-SSPA numeric grade lookup, explicit claim-component/reference-location choices, read-only fixed rates and matched-blank manual rates. Mileage preview = Kadar × Jarak; other approved claims = Kadar × 1. Special C/VU7 mapping remains unassumed. Source-only selectors add no canonical categories/export columns. Shared draft/audit/revision/Fasa/role/PTJ guards remain.

Tested `1c150db0d391d9a3c89deae03df9cc2802194fab`; [CI 43834725263](https://github.com/muhdmuizz/sistem-abm/actions/runs/43834725263), job 133188408042: **528 tests / 5,330 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/exact-money/Chromium passed. Thirteen Pertukaran and one existing-editor capture inspected from 426 PNGs (20 Pertukaran). Batch 039 records resolved fixture failures and visual/manual limits.

No new migration/seed/reset or reference/rate/catalogue/mapping/export-schema changes. Local sync/financial/device acceptance and Batch 038's additive migration remain unconfirmed. Official outputs/totals/completeness, programme inheritance and submission remain deferred; AD-008 remains Draft, Steps 02/03 remain in progress. Complete remaining OS editors before consolidated UI refinement; keep the development server running.

## Batch 040 checkpoint — 17 November 2026

[PR #91](https://github.com/muhdmuizz/sistem-abm/pull/91) merged at `a45ec936cb67d565e72446729830fd12f96047f2`. OS22000 now uses one retained catalogue identity and two Kategori1 paths: Pertukaran/ Penempatan (family, claim, Lokasi Pertukaran and personnel/reference lookup) and Pengangkutan Barang (manual Tujuan/Kadar). Both exact draft previews = Kadar × 1; inactive fields are cleared/rejected. No missing grade 11, special C/VU7 mapping or absent location is fabricated. Existing source rates and canonical export headers remain unchanged.

Targeted migration removes only the obsolete OS22000 identity's development graph/overrides, retains current/unrelated records and mapping provenance/IDs, increments affected bid revisions and aborts atomically for non-draft Bidaan. Retains all seven existing codes. Catalogue **31 (29 eligible/two excluded)**; mappings **105 (103 eligible)**. Ordinary migration and idempotent OsReferenceSeeder import are required locally; no broad reset or financial seed.

Tested `457cc94cd01d40280edea8fac2a77936d0d01d13`; [CI 44038128200](https://github.com/muhdmuizz/sistem-abm/actions/runs/44038128200), job 133885875932: **537 tests / 5,435 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/exact-money/Chromium passed. Thirteen OS22000 and one existing-editor capture reviewed from 446 PNGs (20 OS22000); Batch 040 records resolved fixture/count failures and review limits.

Local sync/migration/import/financial/device acceptance remains pending; earlier unconfirmed statuses unchanged. Use Updates separately from Server; no routine server restart. Official outputs/totals/completeness/submission, programme inheritance and export remain deferred. AD-008 stays Draft; Steps 02/03 in progress. Complete remaining OS editors before consolidated UI refinement.


## Batch 041 implementation checkpoint — 17 November 2026

OS26000 baseline source spreadsheets merged in [PR #93](https://github.com/muhdmuizz/sistem-abm/pull/93) at `cafacd3a86debdd30a0f67fec0e2c9558d21a909`: all 42 approved items, separate Pukal/Bukan Pukal permissions, derived units/rates and exact draft previews. Verified at `0680c16ec8b92ffccd7839f5abbee2bee6d70274`: 544 tests / 5,705 assertions plus build/exact-money/Chromium. No migration or seed added; local sync and prior migration/import remain pending. New-item proposal governance and official financial workflow remain deferred; AD-008 remains Draft and Steps 02/03 in progress. Full evidence and limits: [Batch 041](../development/batch-041-os26000-spreadsheets.md).


## Batch 042 implementation checkpoint — 17 November 2026

OS26000 single-card correction merged in [PR #95](https://github.com/muhdmuizz/sistem-abm/pull/95) at `ae59695121bf72c1adc061ada09f1693187c513a`. One retained identity supports Pukal/Bukan Pukal per row, preserving nine distinct activity codes and all 42 approved references/units/rates. Supersedes Batch 041's separate identities. Catalogue 30/28 eligible/two excluded; mappings 105/103 eligible. Tested `4113f1985ecd26153ae39e50e39a55a263d933d9`: 548 tests / 5,719 assertions plus build/exact-money/Chromium. Targeted obsolete development-card migration and normal reference import required; local acceptance pending. Proposal governance and official financial integration remain deferred; AD-008 stays Draft, Steps 02/03 in progress. Evidence and limits: [Batch 042](../development/batch-042-unified-os26000-spreadsheet.md).


## Batch 044 implementation checkpoint — 17 November 2026

OS27000 Bekalan Alat Pejabat baseline spreadsheet merged in [PR #97](https://github.com/muhdmuizz/sistem-abm/pull/97) at `721fe9216b9b0d0f84c806f9188599bed7c9c274`: four categories/194 unchanged approved items, exact quantity × read-only rate, five existing activity mappings and no inactive direct-entry programme/Unit/Kategori3 inputs. Verified `b096af2cac1885fef4e3252905549f3ec363862b`: 555 tests / 6,183 assertions plus build/exact-money/Chromium. No new migration/seed; local sync/financial/device acceptance pending. Cadangan Item Baharu is deferred until remaining OS editors and consolidated UI refinement finish. Programme inheritance/official financial integration remain pending; AD-008 stays Draft and Steps 02/03 in progress. OS26000 purpose removal was retracted and never merged; it remains unchanged. Evidence and limits: [Batch 044](../development/batch-044-office-supplies-spreadsheet.md).


## OS15000 Bayaran Perubatan calculation approval — 18 November 2026

Owner explicitly approved **Bil. Unit × Kadar × Kekerapan × Bilangan Bulan** for all nine medical categories, and kidney-treatment contract duration using the existing inclusive photocopier years/months/days convention. Chargeable months stay manual and independent of contract dates/duration. Existing Kitaran-year date limits remain. This resolves those two earlier gaps only; no extra eligibility, cap, quantity unit or rounding rule. Separate PPN/010804 identity; purpose remains inactive. See [approved medical checkpoint](../references/OS15000-bayaran-perubatan-27-column-applicability.md) and [Batch 045](../development/batch-045-medical-spreadsheet.md). Official financial integration/programme inheritance remain pending; umbrella AD-008 stays Draft.
