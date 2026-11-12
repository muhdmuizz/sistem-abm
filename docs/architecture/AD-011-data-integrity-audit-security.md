# AD-011 — Data Integrity, Audit & Security

**Status:** Finalised — technical architecture (delegated implementation design)  
**Date:** 11 November 2026  
**Related:** AD-001–AD-010, especially AD-002, AD-003, AD-004, AD-005, AD-006, AD-009 and AD-010

## Purpose and boundaries

Specify enforceable technical safeguards for the existing approved financial hierarchy, roles, submissions, immutable snapshots, historical imports, and audit requirements. This AD **does not create new roles, workflow statuses, financial calculations, UI filters, export rules, or approval stages**. In any apparent conflict, the later finalised AD-009 governs collaborative review and Ketua-only finalisation; earlier AD-005 role scopes remain authoritative.

## 01 — Canonical relational integrity

- Preserve **PTJ → Aktiviti Kewangan → Bidaan → Anggaran (OS) → Butiran Anggaran**. Anggaran (OS) is one entity, not two.
- Use PostgreSQL foreign keys, non-null constraints where domain-required, and indexed unique constraints to protect parentage and identity.
- Enforce one Bidaan per **PTJ + Aktiviti Kewangan + Kitaran**; Fasa/Pindaan does not create another Bidaan.
- Enforce at most one applicable OS/Anggaran within a Bidaan. Butiran Anggaran belong to exactly one Anggaran and have stable unique IDs for amendment comparison.
- Respect configured scope, reference integrity, active/inactive OS and completeness; do not introduce arbitrary numeric caps.
- Protect referenced financial records from destructive cascade deletion. Use controlled archival/retention consistent with AD-004 and AD-006.
- Money: PostgreSQL exact numeric/decimal types; never binary floating point for stored financial amounts. Calculation-specific rounding and rates remain AD-008. Validate negative/zero amounts only according to the relevant approved OS rules, not a blanket new prohibition.
- Reconcile Anggaran totals from eligible Butiran Anggaran and Bidaan totals from eligible active Anggaran. Derived totals/caches, if used for performance, are non-authoritative and must be verifiable against granular records.

## 02 — Transactional integrity and financial versioning

- Group each business-critical operation (activation, submission, return, finalisation, reopening, bulk item decision, relevant corrections) in database transactions that atomically update domain state, version references, and audit entries.
- Recheck permissions, Fasa state, eligibility, completeness and current version **inside** the transaction. Lock the relevant Bidaan row (or use an equivalent safe concurrency mechanism) to serialize conflicting transitions. Use optimistic version checks for stale editor writes; return a conflict instead of silently overwriting.
- A saved draft does **not** advance a submission version. Only formal submission/resubmission creates V1, V2, etc., with a new immutable financial snapshot. A Fasa/Pindaan continues the same Bidaan.
- Snapshots retain the submitted financial values and granular calculation inputs necessary for traceability. Prior snapshots, comments and finalisation history cannot be rewritten by ordinary edits or Superadmin reopening.
- Finalisation references the **latest eligible submitted snapshot**, not a mutable draft; a finalised record remains locked until authorised reopening starts a new editable revision with a mandatory reason.
- Returned Bidaan permits correction of all active OS only under existing Fasa/permission controls; it does not erase the returned submission snapshot.
- Bulk finalisation validates each Bidaan independently; eligible items commit independently and invalid items remain unchanged, with individual audit entries. Avoid a single transaction that rolls back successful items because another selection fails.
- Avoid duplicate submission/finalisation from repeated requests using version/state checks and idempotent transition handling; do not create duplicate snapshots.

## 03 — Audit trail and attribution

- Use a structured append-only audit/event record with actual authenticated actor ID, effective role and scope, action, entity type/ID, timestamp (UTC storage), relevant Kitaran/Fasa/submission version, and reason when mandatory.
- Record material financial before/after changes, activation/deactivation, add/edit/remove, recalculation outcomes, submissions, returns, resubmissions, finalisations, exceptional reopening, role/assignment changes, historical imports/corrections and administrative configuration changes.
- Associate audit events and their business mutation in the same database transaction. Do not claim success if the mandatory audit write fails.
- For high-volume granular edits, use meaningful per-operation change sets with stable row IDs and changed values rather than a full database copy on every keystroke. Autosave may batch related edits while preserving accountability.
- Review comments are attributable append-only entries linked to Bidaan, optionally Anggaran, and submission version; corrections are subsequent entries, never silent overwrites.
- Separate routine access/security logs from material financial change events; sensitive administrative access and denied actions can be recorded in security logs as appropriate.
- Restrict modification/deletion of audit and snapshot tables at application and database privilege levels. No normal user, including Superadmin through application functions, can silently rewrite historical audit evidence.
- Define operational retention, backup and recovery procedures under AD-012; do not invent a fixed retention duration here.

## 04 — Authentication, authorisation and data isolation

- Use the approved **six functional roles** and their exact AD-005 organisational scopes. Multiple assignments are additive only within explicitly granted permissions; organisational job titles do not grant functional authority.
- Authenticate the real user through the **AD-005 approved Google sign-in flow**: the public login page is reachable without pre-login MyGovUC account restrictions, while the Google callback and every protected operation require server-side validation of authenticated identity, active registered user and organisational eligibility. The account chooser, optional domain hints and the user's email text must not independently grant access. Password/MFA handling belongs to Google. Do not impersonate or replace actor identity when Superadmin acts across scopes.
- Centralise Laravel policies/gates and scoped queries; enforce both record visibility and action authorisation **server-side** for UI, APIs, imports, exports, background jobs and bulk actions. Never trust client-supplied PTJ/Aktiviti Kewangan/role IDs.
- Re-evaluate effective authorisation at the time of mutation, not solely when the page loaded. Default-deny on missing or ambiguous assignment.
- Preserve AD-003 separation between Penyedia Bidaan (activation/submission) and Penyedia Anggaran (OS preparation).
- Preserve AD-009 parallel shared review: Penyelia and Ketua may review/return; **only Ketua** may finalise or bulk-finalise. No Penyelia prerequisite sign-off.
- Pemantau Bidaan remains strictly read-only. Superadmin overrides are attributable, reasoned where required and audited.
- Scope historical analysis and Excel extracts to authorised financial records. Historical PTJ-level data cannot imply Tempat Bertugas-level permissions or fabricated allocation.
- Apply framework-standard CSRF/session protection, input validation, output escaping, secret management, least-privilege database credentials and rate limiting to sensitive endpoints. Avoid secrets or unnecessary personal data in logs.

### Confirmed workplace-level authorisation and attribution — 12 November 2026

- Apply the AD-005 **PTJ-level versus Tempat Bertugas-level Penyedia Anggaran** scope when authorising lists, individual Butiran Anggaran, writes, bulk operations and exports. PTJ-level preparers may act across permitted workplaces under their assigned PTJ/Aktiviti Kewangan; workplace-level preparers are restricted to their own registered workplace and the assigned PTJ/Aktiviti Kewangan.
- Validate that each saved **Butiran Anggaran** refers to a permitted specific **Tempat Bertugas** belonging to the relevant PTJ context; reject generic **JPNT/PPD** as a selected Tempat Bertugas. A Sektor has no independent Kod PTJ; it references its parent organisational context without creating separate financial ownership.
- Keep the **actual authenticated actor**, their registered organisational identity and the **chosen Butiran Tempat Bertugas** distinguishable in authorisation/audit processes. No selection or filter expands the user's authority; preserve the stable Butiran ID and immutable submitted history.
- This implements the approved organisational scope clarification without changing existing six-role architecture, workflow statuses, financial calculation policy or AD-004 filter order.

## 05 — Import, reporting and failure safeguards

- Historical expenditure imports remain Superadmin-managed, read-only to other roles and separate from transactional Anggaran/Bidaan.
- Validate file structure, verified financial year, December snapshot completeness, identifier matching, duplicate source identities, and numeric fields before publishing an imported year as verified. Preserve source metadata, corrections and import provenance.
- Annual expenditure derives from **Belanja Bersih** in the verified month-12 snapshot. Never add Belanja Bulanan or sum cumulative months. Exclude Permohonan from analytics.
- Reject or quarantine unmatched/inconsistent rows for resolution; never fabricate PTJ, Aktiviti Kewangan, OS or Tempat Bertugas mappings. Publishing verified imports must be atomic and auditable.
- Reporting/Excel read from authorised canonical financial versions and valid granular records; historical imports must not modify estimates or submitted/finalised snapshots.
- Use database indexes and bounded/batched processing for large Butiran Anggaran datasets. Background processing, where used, must preserve requesting actor/scope, snapshot consistency, error reporting and retry safety.
- Handle failures with rollback, safe user-facing errors, structured diagnostic logs and no partially applied critical financial transition.

## Implementation verification criteria

1. Unique keys and foreign keys prevent duplicate Bidaan/OS and orphan Butiran Anggaran.
2. Concurrent submissions/returns/finalisations and stale edits cannot silently overwrite current state.
3. Draft edits do not advance V-number; each formal submission produces a separately retained snapshot.
4. Previous financial snapshots and review comments survive return, amendment and Superadmin reopening.
5. Role/scope and Fasa enforcement work on direct HTTP/API requests, not merely hidden buttons.
6. All material mutations produce attributable audit records; transaction failure leaves neither half-committed domain state nor missing required audit.
7. Bulk finalisation commits eligible items individually and reports ineligible items without changing them.
8. Imported historical totals use verified cumulative December Belanja Bersih, without double-counting.
9. Aggregated financial reports reconcile with eligible canonical granular data and correct version.
10. Large data operations remain bounded and recoverable; failure paths preserve financial integrity.

## Security baseline and evidence — 13 November 2026

The adopted [Sistem ABM Security Baseline](../security/security-baseline.md) enumerates the implementation and pre-launch verification controls (SEC-01–SEC-26) for Google sign-in, sessions, scoped access, financial protection, infrastructure, scanning and incident readiness. It **operationalises** this AD without replacing its approved financial and role rules. Every security requirement remains **unverified until supported by actual tests or operational evidence**. Session timeout and related policy details remain for separate review.

## Deferred inputs and compatibility

- AD-008 alone determines OS-specific field eligibility, calculations, rates, rounding and completeness mappings.
- AD-010 export scopes, exactly agreed worksheet formats and Analisis filters are unchanged. Official Gaji & Emolumen column template remains pending.
- AD-012 defines concrete deployment, environment configuration, operational backups, monitoring and disaster recovery.
- This document is an **architecture specification**, not a claim of completed migrations, code changes, testing or deployment.
