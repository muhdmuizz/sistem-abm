# Sistem ABM — Architecture Decisions

This is the **single master index** for architecture decisions (ADs). Each AD has its own Markdown document when drafted and approved. Planned ADs are tracked here only; they are **not approved specifications**.

**Repository:** `muhdmuizz/sistem-abm` · **Development branch:** `dev`  
**Status reconciliation:** 15 November 2026

## Implementation progress is tracked separately

Foundation Batches 001–007 are implemented, merged and locally user-confirmed. Batch 008 adds the first interface and Google sign-in, followed by first-account setup at application checkpoint `f6e785a`. Code/build/browser verification passed and the user confirmed their first successful real local login. Reference totals, logout/re-login and narrow-window layout are also user-confirmed; pagination/actual-device/session manual checks remain pending. Batch 009 adds audited Pengguna & Peranan administration and all-device session revocation at `2247627`, with 153 tests / 1143 assertions plus build/browser checks; local migration, operator/add-user form inspection and own all-device logout/re-login are user-confirmed. Other administrative mutations and actual-device checks remain unverified locally. Batch 010 adds audited central category/PTJ activity/OS editors at `d514ae5`, with 168 tests / 1571 assertions plus build/browser checks and screenshot review; local synchronisation and requested editor inspection are user-confirmed, while actual local permission mutations remain unverified. Batch 011 adds bounded Kitaran/Fasa administration at `6c6e777`, with 187 tests / 1730 assertions plus build/browser checks and desktop/mobile visual review; local synchronisation/migration and requested list/year-only form inspection are user-confirmed; actual phase mutations remain unverified and the reported empty-state padding defect is corrected by Batch 022, with requested local refinement checks user-confirmed; overall design refinement remains future work. AD-013 spacing/state-review requirements were clarified by user direction on 14 November 2026. Batch 012 adds canonical financial storage and source/output traceability at `0ff960d`, verified by 206 tests / 1858 assertions plus build/browser regression; local migration user-confirmed. Financial workflows remain pending. Batch 013 adds audited initial activation services at `bf69e80`, verified by 239 tests / 2005 assertions plus build/browser regression; local migration user-confirmed; actual financial operations remain unverified locally. Financial screens remain pending. Batch 014 adds audited backend draft source saves/shared guards at `12fc145`, verified by 272 tests / 2178 assertions plus build/browser regression; local synchronisation/existing-page smoke checks user-confirmed. OS field adapters, calculations and source editing remain pending. Batch 015 exposes the authorised preparation context/catalogue/activation UI at `96e3507`, verified by 288 tests / 2293 assertions, build/Chromium checks and 16 desktop/mobile state screenshots; requested local acceptance user-confirmed. The Fasa date correction at `53d0e45` passed 290 tests / 2311 assertions plus build/browser checks; its migration and requested local Fasa/activation/persistence checks are user-confirmed. Batch 016 adds audited atomic multi-row drafts at `47f2230`, verified by 302 tests / 2403 assertions plus build/browser regression; local synchronisation user-confirmed; direct service execution unverified. Batch 017 adds a scoped active-OS read shell and navigation at `fab380c`, verified by 315 tests / 2505 assertions, build/Chromium and twelve reviewed screenshots; local update/active-OS opening user-confirmed, broader navigation checks unverified. Batch 018 adds scoped read-only Log Tindakan at `30713fd`, verified by 325 tests / 2596 assertions, build/Chromium and sixteen reviewed screenshots; requested local update/Log Tindakan inspection user-confirmed. Batch 019 adds audited internal independent draft duplication at `c2ab85c`, verified by 342 tests / 2722 assertions plus build/browser checks; local synchronisation/existing-page smoke check user-confirmed, direct copy execution unverified. Batch 020 adds approved AD-007 programme metadata/RPM reference storage and scoped audited draft validation at `97733ab`, verified by 393 tests / 3003 assertions plus build/browser regression; local migration/reference import user-confirmed, programme UI subsequently added in Batch 021; OS integration deferred. Source editing and calculation integration remain deferred. This does **not** mean the approved ADs are fully implemented. Read the [development progress register](../development/README.md) for per-batch scope, actual CI evidence, merge checkpoints, local status and pending work. The AD status table below remains a design-approval tracker.

## Architecture decision tracker

| AD | Subject | Status | Document |
| --- | --- | --- | --- |
| AD-001 | Anggaran & Bidaan Workflow | Approved | [AD-001](AD-001-anggaran-bidaan-workflow.md) |
| AD-002 | Canonical Financial Data Hierarchy | Approved | [AD-002](AD-002-canonical-financial-data-hierarchy.md) |
| AD-003 | Bidaan Activation & Submission | Finalised | [AD-003](AD-003-bidaan-activation-and-submission.md) |
| AD-004 | Anggaran Wizard Architecture | Finalised | [AD-004](AD-004-anggaran-wizard-architecture.md) |
| AD-005 | Identity, Roles & Organisation | Approved | [AD-005](AD-005-identity-roles-organisation.md) |
| AD-006 | Kitaran ABM & Administration | Approved | [AD-006](AD-006-kitaran-abm-administration.md) |
| AD-007 | Program/ Aktiviti | Finalised | [AD-007](AD-007-program-aktiviti.md) |
| AD-008 | OS Catalogue & Calculation Framework | Draft | [AD-008](AD-008-os-catalogue-calculation-framework.md) |
| AD-009 | Submission, Review & Finalisation | Finalised | [AD-009](AD-009-submission-review-finalisation.md) |
| AD-010 | Analysis, Reporting & Excel | Finalised | [AD-010](AD-010-analysis-reporting-and-excel.md) |
| AD-011 | Data Integrity, Audit & Security | Finalised | [AD-011](AD-011-data-integrity-audit-security.md) |
| AD-012 | Deployment & Operational Readiness | Finalised | [AD-012](AD-012-deployment-operational-readiness.md) |
| AD-013 | System UI/UX & Interaction Design | Finalised | [AD-013](AD-013-system-ui-ux-interaction-design.md) |

**Progress:** 12 ADs approved/finalised; 1 AD in draft (AD-008).

## Status definitions

- **Proposed:** Scope/topic listed for planning; no decisions approved and no individual AD file required.
- **Draft:** Individual AD under discussion; not yet approved.
- **Baseline approved:** Initial design agreed and documented; implementation specifics remain open to refinement.
- **Approved / Finalised:** Architectural decisions accepted and documented. Amend deliberately if requirements change.

## Core architecture already established

- **AD-001:** Anggaran is the unit of financial preparation; Bidaan is the formal submission unit. Drafts can be saved independently. No Bakul Bidaan.
- **AD-002:** `PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran`. Anggaran and OS are the same domain entity.
- **AD-003:** Separate Penyedia Bidaan and Penyedia Anggaran permissions, activation, completeness, submission, correction, snapshots and audit.
- **AD-004:** Approved shared OS wizard, multi-row editing, bulk addition, duplication, autosave, automatic completeness, deletion/history, OS-to-OS navigation, and Senarai Anggaran filters ordered Aktiviti Kewangan → PTJ → Tempat Bertugas → Status Anggaran. OS-specific fields/calculations deferred.

- **AD-007:** Finalised programme façade, four-section form, headcount classifications, OS checklist and safeguards, automatic programme completion, complete inheritance and draft recalculation contract; OS-specific implementation deferred to AD-008.

- **AD-009:** Finalised whole-Bidaan decision, parallel Penyelia/Ketua review, Ketua-only finalisation, shared comments, return/resubmission, bulk finalisation, four workflow statuses and Superadmin-only reopening.

## Tracking rules

1. **One AD = one individual Markdown file** once substantive work begins; never consolidate future AD specifications into this index.
2. Keep this README as the **only master tracker**. Update its status and document link when an AD is created, approved or amended.
3. Proposed subjects and numbering are planning aids, not approved architectural decisions.
4. Preserve prior decisions and cross-reference related ADs; avoid unnecessary branching or duplicate specifications.
5. Keep implementation refinements within the appropriate AD without inventing additional decision workstreams.

**AD-010:** Finalised and closed. Reconciled reporting, historical expenditure, Analisis and official Excel requirements; no unsolicited export filters or new worksheet rules. Pending implementation inputs do not reopen this AD. Exact payroll export template and OS calculations remain pending.

**AD-011:** Finalised technical safeguards for canonical data, immutable financial versions, audit, authorisation and concurrency; no new functional workflow or OS-specific calculations.

**AD-012:** Finalised technical deployment, backup/recovery, release verification and operational safeguards; no claim of application deployment or production readiness.

**AD-008:** Draft umbrella containing multiple owner-approved OS mappings/formulas and the current 30-column Perbelanjaan contract. Its current readiness summary identifies remaining per-OS gaps. Preserve approved decisions; Draft does not mean all OS policies are unapproved. Implementation and verification remain outstanding.

**AD-013:** Finalised UI/UX specification; all 12 sections approved, including accessibility, responsiveness and QA acceptance standards. This is design approval, not implementation or test certification.

## Existing six-step development plan — scope clarification (13 November 2026)

This records the **existing six-step development plan** without introducing another phase or additional branches. **Step 05 is corrected to apply to all applicable OS**; OS14000 is only one potential test case, not the architecture's preferred or sole OS.

| Step | Workstream | Current recorded status |
| --- | --- | --- |
| 01 | Canonical Model & Compatibility | Design completed; not evidence of implemented code |
| 02 | Anggaran Persistence | In progress — storage, audited activation and backend source draft saves implemented through Batch 014; Batch 023 adds an OS25000 typed source adapter and shared editor; other definitions/official calculation integration remain pending; Batch 015 exposes context/catalogue/activation UI; Batches 016/019 add atomic multi-row draft saves and independent draft duplication |
| 03 | Senarai Anggaran & Editor UI | In progress — Batch 017 read shell/navigation, Batch 018 Log Tindakan and Batches 023–029 shared spreadsheet source editing for OS23000 Perhubungan dan Utiliti, OS25000, OS28000, OS27000 Pakaian Seragam/Touch ’n Go and approved OS42000 SBK/TASKA contexts implemented; full hub, remaining OS definitions and financial integration pending |
| 04 | Activity-Level Submission | Not started |
| 05 | **OS End-to-End Validation — All Applicable OS** | Not started |
| 06 | Regression, Cleanup & Readiness | Not started |

**Scope for Step 05:** Validate the full applicable OS catalogue against each OS's approved fields, references, Kadar, calculation/validation and output requirements, alongside shared Anggaran persistence, editor and submission behaviour. Representative OS can be used to sequence testing, but **none may replace coverage of all applicable OS**. Maintain previously approved exclusions and defer any unresolved OS-specific rules to their authoritative references/AD-008; do not invent rates, formulas or field mappings.

**Current implementation reconciliation — 13 November 2026:** The table above describes the existing functional workstreams, not the delivery sequence of the clean rebuild. Foundation Batches 001–007 are complete; transactional Anggaran persistence and the listed financial/UI/submission workstreams are still pending. Do not restart the foundations or treat their completion as completion of Step 02. Current delivery status and proposed next work are recorded only in the [development register](../development/README.md). No new architecture or additional workstream is approved by this status note.

## Latest bounded interface checkpoint — Batch 021

Batch 021 adds the scoped Program/ Aktiviti list and four-section draft form, dependent RPM choices, package/headcount controls and retained validation/stale feedback. [PR #49](https://github.com/muhdmuizz/sistem-abm/pull/49) merged at `981b766d4435724abf6dc7b463bbce6c2a109fa1`; [CI 43583261810](https://github.com/muhdmuizz/sistem-abm/actions/runs/43583261810): 408 tests / 3129 assertions at `e9da548`, PHP 8.4.27 / PostgreSQL 18.7, build/Chromium and desktop/mobile visual review passed. Requested Batch 021 local programme checks are user-confirmed; no migration/seed. OS association, inheritance/calculations and financial completeness remain deferred. This implements metadata entry only, preserving strict Reference 06 defaults and Kitaran-year dates. No approved AD status or rule changes.

## Latest shared UI refinement — Batch 022

Batch 022 refines shared UI spacing, typography/controls and empty/read-only/processing states, restores independently scrolling sidebar/mobile navigation and makes login compact for standard laptop displays. [PR #51](https://github.com/muhdmuizz/sistem-abm/pull/51) merged at `f8d0cbf9d5875815b354b0de7cba545ba8dfb0a9`; [CI 43585733643](https://github.com/muhdmuizz/sistem-abm/actions/runs/43585733643), 410 tests / 3141 assertions at `9422886`, PHP 8.4.27 / PostgreSQL 18.7, build/Chromium and representative desktop/mobile visual review passed. Requested local Batch 022 checks user-confirmed; overall design refinement remains future work; no migration/seed or business-rule change. AD-013 requirements/approval status are unchanged; future asynchronous/loading, financial workflows and OS-policy implementations remain bounded separately.

## Latest bounded source editor checkpoint — Batch 023

[PR #54](https://github.com/muhdmuizz/sistem-abm/pull/54) merged at `683629ba734e9104897e8722363bbcaebeb92270`: shared spreadsheet-like source editing with OS25000 typed inputs/references, draft save/reopen, duplication and audited removal. [CI 43632464587](https://github.com/muhdmuizz/sistem-abm/actions/runs/43632464587) passed 423 tests / 3245 assertions, build/Chromium and 11 new reviewed grid captures at tested head `11255c04c0c7c0d117c66d58aa475896b7f654e3`. Local acceptance pending; no migration/seed. Exact row previews do not certify official totals/completeness. Other OS definitions, programme inheritance and financial workflows remain pending. No AD approval or financial rule was changed by this implementation.

## Latest typed OS checkpoint — Batch 024

[PR #56](https://github.com/muhdmuizz/sistem-abm/pull/56) merged at `68a8f46fb839675f2d83ad7914e9b4cb2f163a19`: OS42000 SBK source entry for Pendidikan Rendah 030228 and Pendidikan Menengah 030310 using the common spreadsheet, four shared Pukal options, manual monthly KADAR and fixed 12-month exact previews. Tested `5ebdce2244d8f80aa021df961b1af4efd1c4eb9f`, [CI 43635684953](https://github.com/muhdmuizz/sistem-abm/actions/runs/43635684953), 431 tests / 3354 assertions on PHP 8.4.27 / PostgreSQL 18.7, build/Chromium and visual correction review passed. No migration/seed or approved financial-rule change. Batch 023 local synchronisation/cache clear user-confirmed; its detailed functional checks unverified. Batch 024 local acceptance pending; official calculation/output/completeness and programme inheritance remain pending.

## Latest typed OS checkpoint — Batch 025

[PR #58](https://github.com/muhdmuizz/sistem-abm/pull/58) merged at `0eb73bc132474e1355d22f706104600001c76c06`: TASKA parent/child/month source inputs and exact RM180 reference-rate previews under existing PPN/010102 mapping, using the same spreadsheet as SBK/OS25000. Tested `9c4bce154c684ec232416e9e4d99572ddc64cee2`, [CI 43653498708](https://github.com/muhdmuizz/sistem-abm/actions/runs/43653498708): 438 tests / 3449 assertions, PHP 8.4.27 / PostgreSQL 18.7, build/Chromium and desktop/mobile state review passed. Batch 024 local synchronisation/cache clear user-confirmed; detailed SBK functional checks unverified. Batch 025 local acceptance pending; no migration/seed or approved financial-rule change. Official outputs/completeness and programme inheritance remain pending.

## Latest typed OS and monetary checkpoint — Batch 026

[PR #61](https://github.com/muhdmuizz/sistem-abm/pull/61) merged at `0343ac972b1f366443e9508088e7d5d7fc2286d6`: OS28000 dependent/mixed-rate source editing and AD-013's tabular, grouped/two-decimal money presentation across the four configured definitions. Exact values remain separate from display-only formatting and official financial policy. Tested `d9f2742950eb6e071ad003bc4609971a09173b96`, [CI 43662888155](https://github.com/muhdmuizz/sistem-abm/actions/runs/43662888155): 445 tests / 3544 assertions, PHP 8.4.27 / PostgreSQL 18.7, build/formatter/Chromium and representative desktop/mobile state review passed. No migration/seed; local acceptance pending. Other OS definitions, official outputs/completeness and programme inheritance remain pending.

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

[PR #85](https://github.com/muhdmuizz/sistem-abm/pull/85) merged at `d95b86029a72a77da72e7e0932b6dadeb3f4be3f`. OS24000 Sewaan Umum source editor implements five existing mappings and all 64 approved paths, three-level dependent categories with cascading resets, blank Tanah leaves, distinct broadcasting labels and Berkontrak-only Kitaran-year dates/inclusive day duration. Exact draft preview = Bil. Unit × manual Kadar × manual Bilangan Bulan; dates never set months. No migration/seed/reset, reference/rate/catalogue/mapping/permission/export-schema changes. At the Batch 037 checkpoint, photocopier rental remained separate and unimplemented; Batch 038 below supersedes its implementation status.

Checkout access recovered without changing workflow/credentials/permissions after browser inspection confirmed existing settings were correct; precise transient upstream cause unconfirmed. [CI 43799773291 attempt 2](https://github.com/muhdmuizz/sistem-abm/actions/runs/43799773291) at `29932d87de94f89a018d01c15595a403b3337520`: **510 tests / 5,119 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB; build/formatter/Chromium passed. Later commits changed documentation only. Reviewed 14 rental and three existing-editor captures from 382 PNGs / 22 rental captures; [Batch 037](../development/batch-037-sewaan-umum-spreadsheet.md) records coverage, earlier failures and review limits.

Local sync/financial acceptance remains pending; prior unconfirmed local statuses are unchanged. Official outputs/totals/completeness, programme inheritance and submission remain pending; AD-008 remains Draft and Steps 02/03 in progress. Complete remaining OS editors before consolidated editor UI refinement; usability defects fixed as encountered. No routine server restart required.

## Batch 038 checkpoint — 16 November 2026

[PR #87](https://github.com/muhdmuizz/sistem-abm/pull/87) merged at `9f294dc9a33c6b6dbb544e0505fa7b0bfe83a42d`. OS24000 Sewaan Mesin Fotostat dan Riso source editor supports the four existing mappings and its independent manual-unit/rate/month calculation. Rental activation atomically activates OS29000 Bacaan Meter; direct meter activation is blocked. Saving each rental creates/updates one linked meter draft. Shared workplace/print/location/current and overall contract dates follow the rental; meter copies, per-copy rate and months remain independent. Explicit linking reviews existing unlinked drafts, retains their financial inputs and replaces only a financially blank generated meter. Rental deletion confirms linked meter values/revision before audited pair removal; deactivation retains both drafts/links for reactivation.

Tested `75809c99be402bf0d0805837286557e2519d247a`; [CI 43817060723](https://github.com/muhdmuizz/sistem-abm/actions/runs/43817060723), job 133131406062: **521 tests / 5,233 assertions**, PHP 8.4.27/PostgreSQL 18.7 isolated testing DB, production build, exact-money formatter checks and Chromium passed. Verification and screenshot review limits are recorded in Batch 038. One additive linkage migration is required; no reset/seed or reference/rate/catalogue/mapping/export-schema changes. Local sync/migration/financial/device acceptance pending; earlier unconfirmed statuses unchanged.

OS24000's Program/ Aktiviti entry pathway is recorded; programme association/inheritance remains separate and direct entry remains available. Official outputs/totals/completeness and submission remain pending; AD-008 remains Draft and Steps 02/03 in progress. Complete remaining OS editors before consolidated editor UI refinement. Keep the development server running.

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
