# Sistem ABM — Architecture Decisions

This is the **single master index** for architecture decisions (ADs). Each AD has its own Markdown document when drafted and approved. Planned ADs are tracked here only; they are **not approved specifications**.

**Repository:** `muhdmuizz/sistem-abm` · **Development branch:** `dev`  
**Status reconciliation:** 13 November 2026

## Implementation progress is tracked separately

Foundation Batches 001–007 are implemented, merged and locally user-confirmed. Batch 008 adds the first interface and Google sign-in, followed by first-account setup at application checkpoint `f6e785a`. Code/build/browser verification passed and the user confirmed their first successful real local login. Reference totals, logout/re-login and narrow-window layout are also user-confirmed; pagination/actual-device/session manual checks remain pending. Batch 009 adds audited Pengguna & Peranan administration and all-device session revocation at `2247627`, with 153 tests / 1143 assertions plus build/browser checks; its local migration and acceptance are pending. This does **not** mean the approved ADs are fully implemented. Read the [development progress register](../development/README.md) for per-batch scope, actual CI evidence, merge checkpoints, local status and pending work. The AD status table below remains a design-approval tracker.

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

**AD-008:** Shared OS catalogue/calculation contract drafted; individual OS rates, formulas, mappings and official templates remain unverified and are not approved.

**AD-013:** Finalised UI/UX specification; all 12 sections approved, including accessibility, responsiveness and QA acceptance standards. This is design approval, not implementation or test certification.

## Existing six-step development plan — scope clarification (13 November 2026)

This records the **existing six-step development plan** without introducing another phase or additional branches. **Step 05 is corrected to apply to all applicable OS**; OS14000 is only one potential test case, not the architecture's preferred or sole OS.

| Step | Workstream | Current recorded status |
| --- | --- | --- |
| 01 | Canonical Model & Compatibility | Design completed; not evidence of implemented code |
| 02 | Anggaran Persistence | Implementation not completed |
| 03 | Senarai Anggaran & Editor UI | Not started |
| 04 | Activity-Level Submission | Not started |
| 05 | **OS End-to-End Validation — All Applicable OS** | Not started |
| 06 | Regression, Cleanup & Readiness | Not started |

**Scope for Step 05:** Validate the full applicable OS catalogue against each OS's approved fields, references, Kadar, calculation/validation and output requirements, alongside shared Anggaran persistence, editor and submission behaviour. Representative OS can be used to sequence testing, but **none may replace coverage of all applicable OS**. Maintain previously approved exclusions and defer any unresolved OS-specific rules to their authoritative references/AD-008; do not invent rates, formulas or field mappings.

**Current implementation reconciliation — 13 November 2026:** The table above describes the existing functional workstreams, not the delivery sequence of the clean rebuild. Foundation Batches 001–007 are complete; transactional Anggaran persistence and the listed financial/UI/submission workstreams are still pending. Do not restart the foundations or treat their completion as completion of Step 02. Current delivery status and proposed next work are recorded only in the [development register](../development/README.md). No new architecture or additional workstream is approved by this status note.
