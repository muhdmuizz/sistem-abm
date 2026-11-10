# Sistem ABM — Architecture Decisions

This is the **single master index** for architecture decisions (ADs). Each AD has its own Markdown document when drafted and approved. Planned ADs are tracked here only; they are **not approved specifications**.

**Repository:** `muhdmuizz/sistem-abm` · **Branch:** `main`  
**Last updated:** 10 November 2026

## Architecture decision tracker

| AD | Subject | Status | Document |
| --- | --- | --- | --- |
| AD-001 | Anggaran & Bidaan Workflow | Approved | [AD-001](AD-001-anggaran-bidaan-workflow.md) |
| AD-002 | Canonical Financial Data Hierarchy | Approved | [AD-002](AD-002-canonical-financial-data-hierarchy.md) |
| AD-003 | Bidaan Activation & Submission | Finalised | [AD-003](AD-003-bidaan-activation-and-submission.md) |
| AD-004 | Anggaran Wizard Architecture | Baseline approved — refinable | [AD-004](AD-004-anggaran-wizard-architecture.md) |
| AD-005 | Identity, Roles & Organisation | Approved | [AD-005](AD-005-identity-roles-organisation.md) |
| AD-006 | Kitaran ABM & Administration | Proposed | — |
| AD-007 | Program/ Aktiviti | Proposed | — |
| AD-008 | OS Catalogue & Calculation Framework | Proposed | — |
| AD-009 | Submission, Review & Finalisation | Proposed | — |
| AD-010 | Analysis, Reporting & Excel | Proposed | — |
| AD-011 | Data Integrity, Audit & Security | Proposed | — |
| AD-012 | Deployment & Operational Readiness | Proposed | — |

**Progress:** 5 documents committed (including one baseline); 7 topics proposed.

## Status definitions

- **Proposed:** Scope/topic listed for planning; no decisions approved and no individual AD file required.
- **Draft:** Individual AD under discussion; not yet approved.
- **Baseline approved:** Initial design agreed and documented; implementation specifics remain open to refinement.
- **Approved / Finalised:** Architectural decisions accepted and documented. Amend deliberately if requirements change.

## Core architecture already established

- **AD-001:** Anggaran is the unit of financial preparation; Bidaan is the formal submission unit. Drafts can be saved independently. No Bakul Bidaan.
- **AD-002:** `PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran`. Anggaran and OS are the same domain entity.
- **AD-003:** Separate Penyedia Bidaan and Penyedia Anggaran permissions, activation, completeness, submission, correction, snapshots and audit.
- **AD-004:** Common Anggaran Workspace (Maklumat, Butiran Anggaran, Semakan, Log Tindakan), with OS-specific inputs and calculation rules. UI is a refinable baseline.

## Tracking rules

1. **One AD = one individual Markdown file** once substantive work begins; never consolidate future AD specifications into this index.
2. Keep this README as the **only master tracker**. Update its status and document link when an AD is created, approved or amended.
3. Proposed subjects and numbering are planning aids, not approved architectural decisions.
4. Preserve prior decisions and cross-reference related ADs; avoid unnecessary branching or duplicate specifications.
5. Keep implementation refinements within the appropriate AD without inventing additional decision workstreams.

**Next proposed discussion:** AD-006 — Kitaran ABM & Administration.
