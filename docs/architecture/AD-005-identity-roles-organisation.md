# AD-005 — Identity, Roles & Organisation

**Project:** Sistem ABM  
**Status:** Approved — architectural decisions  
**Date:** 10 November 2026  
**Related:** [AD-001](AD-001-anggaran-bidaan-workflow.md), [AD-002](AD-002-canonical-financial-data-hierarchy.md), [AD-003](AD-003-bidaan-activation-and-submission.md), [AD-004](AD-004-anggaran-wizard-architecture.md)

## Decision

Sistem ABM uses **exactly six functional roles**, with explicit organisational and financial access scopes. Users may hold multiple roles and multiple assignments. Authentication identifies the user; authorisation determines permitted actions and data scope.

## Roles

| Role | Authority |
| --- | --- |
| **Superadmin** | Full functional and administrative authority across the system, all PTJ and Aktiviti Kewangan, including operational actions, configuration, testing and justified overrides; all actions audited. |
| **Ketua Bidaan** | JPNT officers only; corresponds to **Ketua Aktiviti**. Views, comments/queries and makes the final **Lulus / Tolak** decision for Bidaan within assigned Aktiviti Kewangan, after formal Penyelia Bidaan review. |
| **Penyelia Bidaan** | Views, comments/queries and formally reviews Bidaan, then **Kemukakan** to Ketua Bidaan; no independent final approval/rejection authority. |
| **Penyedia Bidaan** | Activates and submits Bidaan within explicitly assigned PTJ + Aktiviti Kewangan; does not automatically acquire OS-editing authority. |
| **Penyedia Anggaran** | Activates/deactivates OS and prepares, edits and completes Anggaran and Butiran Anggaran within explicitly assigned PTJ + Aktiviti Kewangan; does not automatically acquire Bidaan activation/submission authority. |
| **Pemantau Bidaan** | Read-only monitoring, financial data and reporting within explicitly assigned viewing scope; cannot create, edit, activate, submit, forward, approve or reject. |

These functional roles replace earlier proposed names such as Pengurusan, Pengurusan Tertinggi and separate Ketua Aktiviti (Pemohon) categories. Organisational job titles are not themselves additional system roles.

## Scope rules

| Role | Scope |
| --- | --- |
| Superadmin | Entire system, all PTJ and Aktiviti Kewangan |
| Ketua Bidaan | **All PTJ** within explicitly assigned **Aktiviti Kewangan** |
| Penyelia Bidaan | **All PTJ** within explicitly assigned **Aktiviti Kewangan** |
| Penyedia Bidaan | Explicit **PTJ + Aktiviti Kewangan** assignment |
| Penyedia Anggaran | Explicit **PTJ + Aktiviti Kewangan** assignment |
| Pemantau Bidaan | Configurable read-only: **Seluruh JPNT**, selected **Aktiviti Kewangan** (all relevant PTJ), or selected **PTJ** (all relevant Aktiviti Kewangan) |

Multiple scoped assignments may be granted to a single user; they do not automatically expand authority to unassigned PTJ or activities. PTJ, organisational identity and Aktiviti Kewangan are distinct concepts.

## Confirmed PTJ, Sektor and Tempat Bertugas assignments — 12 November 2026

- **Penyedia Bidaan is a PTJ-level responsibility**: the assigned user activates/submits the Bidaan for the authorised **PTJ + Aktiviti Kewangan**. Sektor and non-PTJ schools **do not create independent Bidaan**. A person from a Sektor may also act as Penyedia Bidaan **only when explicitly assigned that role**; the two roles remain separate.
- **Sektor** is a subordinate organisational identity of **JPNT or PPD**, has **no individual Kod PTJ**, and may identify the registered workplace of a **Penyedia Anggaran**. The system resolves the authenticated user's registered Sektor/Tempat Bertugas identity independently from their functional role and financial assignments; Sektor membership alone grants no financial access.
- For **Penyedia Anggaran**, the existing explicitly assigned **PTJ + Aktiviti Kewangan** authorisation is retained, with an additional confirmed **Tempat Bertugas scope**:
  - **PTJ-level assignment:** may prepare/manage Butiran for all permitted Tempat Bertugas under the assigned PTJ, within the assigned Aktiviti Kewangan.
  - **Tempat Bertugas-level assignment:** may prepare/manage Butiran for **their own assigned Tempat Bertugas only**, within the authorised parent PTJ and Aktiviti Kewangan. This includes Sektor-level users and school-level users where applicable.
- Every Butiran Anggaran selects a permitted Tempat Bertugas (AD-004). **JPNT and PPD themselves are not selectable as generic Tempat Bertugas**: specific subordinate workplaces must be used to retain Sektor/school-level attribution. An **SMK PTJ** remains a PTJ with its own code and may also be its own Tempat Bertugas.
- Authorise viewing, creating, editing, bulk operations and direct access **on the server**, including the Tempat Bertugas restriction. A user cannot bypass their own-workplace restriction by choosing another dropdown value or record ID. **Superadmin's existing system-wide authority is unchanged.**
- These are **scope distinctions inside the existing six roles**, not additional functional roles, Sektor-level financial entities or independent approval workflows.

## Formal review sequence

1. **Penyedia Anggaran** prepares and completes active OS estimates.
2. **Penyedia Bidaan** submits the Bidaan when AD-003 completeness conditions are met.
3. **Penyelia Bidaan** performs a **formal review**, may provide **Ulasan/ Kuiri**, and **Kemukakan** to Ketua Bidaan.
4. **Ketua Bidaan** (JPNT officer / Ketua Aktiviti) reviews and decides **Lulus / Tolak**.

Ketua Bidaan cannot make the final decision before Penyelia Bidaan has completed the formal review and forwarded the Bidaan. Detailed review states, returns and correction mechanics belong in AD-009 and must remain consistent with AD-003.

## Ulasan/ Kuiri

**Ulasan/ Kuiri is one function**, not two separate processes. Both Ketua Bidaan and Penyelia Bidaan may view, comment and query. Comments/queries may eventually target a specific **Butiran Anggaran** through its unique ID. The UI, response mechanism and workflow effects will be determined later under AD-009; do not prematurely impose separate status flows.

## Superadmin

Superadmin has full functional authority, including all role actions, user and role management, PTJ/Aktiviti Kewangan assignments, system and Kitaran configuration, reports and exports, testing across organisational contexts, and controlled reopening/correction of finalised records.

Testing or acting in another scope must **not falsify the real actor identity**. Overrides and corrections remain auditable and preserve historical financial snapshots. Full functional authority does not imply silent alteration or deletion of audit history.

## System-wide auditability

**All six roles are accountable under the same audit framework**, including Superadmin. Capture, as applicable:

- Actual actor identity, timestamp, action and target entity/record.
- Before/after values for material changes.
- Activation/deactivation, edits, submission, forwarding, approval/rejection and correction actions.
- Ulasan/ Kuiri and relevant responses.
- User, role, permission and scope assignment changes.
- Superadmin overrides and exceptional actions.
- Immutable historical financial submission snapshots and traceable decision history.

Routine read-only page access may be handled through access/security logs rather than a full financial change event for every view. Audit implementation details and security controls may be elaborated in AD-011 without weakening these requirements.

## Compatibility and boundaries

- Respect AD-002 hierarchy: **PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran**.
- Preserve AD-003 distinction between **Penyedia Bidaan** and **Penyedia Anggaran** and the requirement for active OS completeness.
- This document defines **who can do what and where**; AD-009 will detail review-state transitions and Ulasan/ Kuiri interactions.
- No additional role categories or approval layers are authorised by this decision.
