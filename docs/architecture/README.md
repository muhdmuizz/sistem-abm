# Sistem ABM — Architecture Decisions

This is the **single master index** for architecture decisions (ADs). Each AD has its own Markdown document when drafted and approved. Planned ADs are tracked here only; they are **not approved specifications**.

**Repository:** `muhdmuizz/sistem-abm` · **Branch:** `main`  
**Last updated:** 11 November 2026

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

**Progress:** 11 ADs approved/finalised; 1 AD drafted pending authoritative OS-specific references (AD-008).

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

**Next work:** Reconcile authoritative OS templates, financial rates and source mappings within AD-008; do not invent policies or declare AD-008 finalised prematurely. All other architecture subjects are closed.
