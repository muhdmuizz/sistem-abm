# Sistem ABM — Architecture Decision Register

**Repository:** `muhdmuizz/sistem-abm`  
**Branch:** `main`  
**Updated:** 10 November 2026  
**Purpose:** Single tracking register for approved and proposed Architecture Documents (ADs).

> **Status convention:** *Committed* means an AD document exists in Git. *Baseline committed* means an initial agreed design exists but details remain refinable. *Proposed* means only the topic and scope have been identified; no architecture decision has been approved. Do not interpret proposed entries as adopted specifications.

## Existing architecture documents

| ID | Document | Status | Link |
| --- | --- | --- | --- |
| AD-001 | Aliran Kerja Anggaran dan Bidaan | Committed — agreed | [AD-001](AD-001-anggaran-bidaan-workflow.md) |
| AD-002 | Canonical Financial Data Hierarchy | Committed — agreed | [AD-002](AD-002-canonical-financial-data-hierarchy.md) |
| AD-003 | Pengaktifan Bidaan dan Kesediaan Penghantaran | Committed — finalised | [AD-003](AD-003-bidaan-activation-and-submission.md) |
| AD-004 | Anggaran Wizard Architecture | Baseline committed — refinable | [AD-004](AD-004-anggaran-wizard-architecture.md) |

### Approved architecture at a glance

- **AD-001:** Anggaran is the financial preparation unit; Bidaan is the formal submission unit. Drafts can be saved independently. No Bakul Bidaan.
- **AD-002:** Canonical hierarchy: **PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran**. Anggaran and user-facing OS refer to the same entity; financial detail is granular.
- **AD-003:** Penyedia Bidaan activates/submits Bidaan; Penyedia Anggaran activates/edits OS within scoped permissions. All active OS must be complete and at least one active OS is required to submit. Deactivation retains data; submission is read-only with snapshots; returned Bidaan allows all active OS to be edited; structured audit log.
- **AD-004:** Common Anggaran Workspace with **Maklumat, Butiran Anggaran, Semakan, Log Tindakan** as functional areas, not mandated tabs. OS-specific editors, granular source data, recalculation, drafts, validation and audit. OS14000 is the first illustrative implementation case, not a frozen formula.

## Proposed architecture documents — not yet approved

| ID | Working title | Intended coverage | Status |
| --- | --- | --- | --- |
| AD-005 | Identity, Roles & Organisation | Users, PTJ, Sektor, PPD, SMK PTJ, role and scope permissions | Proposed |
| AD-006 | Kitaran ABM & Administration | Cycles, submission windows, phases, system configuration | Proposed |
| AD-007 | Program/ Aktiviti | Programme information, objectives, participants, OS relationships | Proposed |
| AD-008 | OS Catalogue & Calculation Framework | OS eligibility, specialised calculations, rates, validation | Proposed |
| AD-009 | Submission, Review & Finalisation | Review routing, corrections, approvals, financial snapshots | Proposed |
| AD-010 | Analysis, Reporting & Excel | Dashboards, financial analysis, granular 27-column export | Proposed |
| AD-011 | Data Integrity, Audit & Security | Access enforcement, change history, data protection | Proposed |
| AD-012 | Deployment & Operational Readiness | Testing, migrations, environments, backup, release | Proposed |

## System-wide view

- **Foundation and governance:** AD-005, AD-006, AD-011, AD-012.
- **Financial workflow and canonical data:** AD-001, AD-002, AD-003.
- **Preparation and calculation:** AD-004, AD-007, AD-008.
- **Review and outputs:** AD-009, AD-010.

## Working rules

1. Keep this register current whenever an AD is approved, amended, created or retired.
2. Do not mark a proposed AD as approved until the user explicitly agrees to its substantive decisions.
3. Avoid branching into unnecessary sub-decisions; preserve the agreed architecture and refine implementation details during development.
4. Maintain cross-references to earlier ADs and avoid duplicating or contradicting existing decisions.
5. AD numbering and proposed titles are planning aids and may be revised before the respective AD is written.

## Suggested next discussion

**AD-005 — Identity, Roles & Organisation** (proposal only; not started).
