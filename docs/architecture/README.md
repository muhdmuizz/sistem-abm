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
| 03 | Senarai Anggaran & Editor UI | In progress — Batch 017 read shell/navigation, Batch 018 Log Tindakan and Batches 023–026 shared spreadsheet source editing for OS25000, OS28000 and approved OS42000 SBK/TASKA contexts implemented; full hub, remaining OS definitions and financial integration pending |
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
