# AD-009 — Submission, Review & Finalisation

**Status:** Finalised — eight approved architectural decisions  
**Date:** 11 November 2026  
**Related:** AD-001 (Anggaran & Bidaan Workflow), AD-002 (Canonical Financial Data Hierarchy), AD-003 (Bidaan Activation & Submission), AD-004 (Anggaran Wizard Architecture), AD-005 (Identity, Roles & Organisation), AD-006 (Kitaran ABM & Administration), AD-007 (Program/ Aktiviti), AD-008 (OS Catalogue & Calculation Framework)

## Purpose and boundaries

Define the formal submission, shared review, correction, finalisation, bulk-finalisation and exceptional reopening lifecycle for **Bidaan**. The **entire submitted Bidaan is the unit of decision**, never individual Anggaran (OS). Reviewers may attach **OS-level comments** to clarify issues without creating OS-level approval outcomes.

AD-009 consumes Anggaran completeness/validation results established in AD-003 and AD-004. Exact OS-specific fields, rates, calculations and validation logic remain in AD-008; they are **not prerequisites to finalising this workflow design**, but must be implemented before the end-to-end workflow can operate.

## Decision 01 — One outcome for the whole Bidaan

1. **Penyedia Bidaan** submits the complete Bidaan, not separate OS submissions.
2. A Bidaan is eligible for submission only if **at least one OS is active and complete, and every active OS is complete and valid**, consistent with AD-003. Inactive OS do not block submission.
3. Reviewers assess the **entire submitted Bidaan as one decision**; there is no partial approval or return by OS.
4. General Bidaan comments and optional comments attached to particular OS are supported.
5. A returned Bidaan is resubmitted as a whole, after applicable validations pass.
6. Every formal submission retains its own immutable financial snapshot and review history.

## Decision 02 — Parallel, collaborative review and exclusive finalisation authority

**Penyelia Aktiviti and Ketua Aktiviti have the same review responsibilities and access to one shared review workspace.** Both may review simultaneously and collaboratively; neither is a prerequisite reviewer for the other. **Only Ketua Aktiviti** has authority to execute **Muktamadkan Bidaan**.

| Action | Penyelia Aktiviti | Ketua Aktiviti |
| --- | --- | --- |
| View submitted Bidaan and all its OS | Yes | Yes |
| Review financial details | Yes | Yes |
| Add Bidaan-level comments | Yes | Yes |
| Add OS-level comments | Yes | Yes |
| View shared review history | Yes | Yes |
| Kembalikan untuk Pembetulan | Yes | Yes |
| Muktamadkan Bidaan | **No** | **Yes** |
| Bulk Muktamadkan | **No** | **Yes** |

These permissions are enforced server-side and constrained by authorised PTJ/Aktiviti Kewangan scope; hiding a button alone is insufficient. **No separate Penyelia sign-off is required** before Ketua finalisation.

## Decision 03 — Kembalikan untuk Pembetulan

1. Either reviewer may return the **entire Bidaan** for correction.
2. A **reason is mandatory**. OS-specific comments are optional.
3. On return, the Bidaan enters **Dikembalikan** and all its active Anggaran (OS) become editable again by authorised **Penyedia Anggaran**, not only those mentioned in comments.
4. Relevant preparers are notified of the return and reasons.
5. **Penyedia Bidaan** resubmits the whole Bidaan after all active OS are valid and complete.
6. Return, corrections and resubmission are auditable; earlier formal submission snapshots remain unchanged.
7. A return action cannot silently overwrite an already finalised or superseded submission; server-side current-state/version checks are required.

## Decision 04 — Shared review comments

- Maintain **one chronological review history** per Bidaan across submission, return and resubmission cycles.
- Comments can be **Bidaan-level** or associated with a specific **Anggaran (OS)**.
- Each comment records its **author, role, timestamp and relevant submission version**.
- Both reviewers can see and contribute to the same history.
- Saved comments **cannot be silently overwritten or deleted**. Corrections are recorded as subsequent attributable entries.
- Review comments are not independent OS decisions; there is still only one Bidaan outcome.

## Decision 05 — Muktamadkan Bidaan

Only **Ketua Aktiviti** may finalise a Bidaan, provided all of the following are true:

1. The Bidaan is currently **Dalam Semakan**.
2. At least one OS is active and complete, and **all active Anggaran (OS) are complete and valid**.
3. There is **no outstanding return-for-correction action**.
4. The system verifies that the action targets the **latest current submitted version** and the user has authority.
5. Ketua Aktiviti explicitly **confirms** the finalisation.

Finalisation sets the Bidaan to **Muktamad**, locks it against ordinary editing/review actions, and preserves its financial snapshot, decision, comments and audit trail. No additional Penyelia approval is required. Subsequent changes require the authorised reopening/amendment workflow.

## Decision 06 — Bulk finalisation

- **Ketua Aktiviti** may select multiple submitted Bidaan via checkboxes and execute bulk **Muktamadkan**.
- The system independently performs the **same server-side finalisation checks** for each selected Bidaan.
- A confirmation shows eligible and ineligible counts and explains why any Bidaan cannot be finalised.
- **Only eligible Bidaan are finalised**; ineligible Bidaan remain unchanged. One invalid selection does not prevent eligible selections from succeeding.
- Each successfully finalised Bidaan receives its **own decision, audit record and immutable snapshot association**.
- Penyelia Aktiviti has no individual or bulk finalisation authority.

## Decision 07 — Four primary Bidaan workflow statuses

| Status | Meaning |
| --- | --- |
| **Dalam Penyediaan** | Preparers are creating or correcting a Bidaan before formal submission. |
| **Dalam Semakan** | A formally submitted Bidaan is available to both reviewers in parallel. |
| **Dikembalikan** | A reviewer has returned the whole Bidaan for correction; authorised preparers may edit. |
| **Muktamad** | Ketua Aktiviti has finalised the Bidaan; ordinary editing is locked. |

Typical transitions:

- **Dalam Penyediaan → Dalam Semakan**: authorised formal submission.
- **Dalam Semakan → Dikembalikan**: either reviewer returns with mandatory reason.
- **Dikembalikan → Dalam Semakan**: authorised resubmission after completeness checks.
- **Dalam Semakan → Muktamad**: Ketua Aktiviti finalises after all prerequisites pass.

Submission version, OS completeness, activation state, comments and audit history are **separate dimensions**, not additional primary Bidaan workflow statuses. An exceptional reopening of a Muktamad Bidaan starts a new editable revision under Decision 08, preserving the prior Muktamad version; the editable revision follows the existing preparation/submission workflow rather than introducing an extra primary status.

## Decision 08 — Exceptional reopening after finalisation

1. **Only Superadmin** may reopen a **Muktamad** Bidaan.
2. A **mandatory reason** is recorded.
3. Reopening creates a **new editable revision**, never an in-place change to the finalised snapshot.
4. Previous finalisation, financial amounts, comments and audit history remain **immutable**.
5. The revised Bidaan must be submitted, reviewed and finalised through the **normal workflow** again.
6. **Buka Semula** is distinct from ordinary **Kembalikan untuk Pembetulan** during review.

## Cross-cutting safeguards and implementation boundary

- All transitions must validate the acting user's role and authorised organisational scope **on the server**, including bulk operations.
- Enforce current submission-version and status checks to prevent stale or conflicting review/return/finalisation actions from overwriting each other.
- Record user, role, timestamp, action, reason where required, submission version and affected Bidaan/OS in **Log Tindakan**.
- Notifications for returned submissions are part of the approved workflow; delivery mechanism and template are implementation details.
- Financial calculations and OS-specific completeness are defined under **AD-008**. AD-009 defines when their validated results are consumed, not how each OS computes them.
- Respect AD-006 Fasa/Kitaran controls, amendment rules and immutable submission history.

**Closure:** AD-009 is **finalised** as an architectural design. This document records approved decisions; it does not claim that the application workflow has been implemented or deployed.
