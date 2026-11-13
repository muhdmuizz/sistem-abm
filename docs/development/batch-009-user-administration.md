# Batch 009 — Pengguna & Peranan and Session Revocation

**Status:** In progress; verification/merge/local application pending.
**Baseline:** `c76f52d5cf0dbe43c713a6af27ecdf3ccf806153` on dev.
**Authorisation:** User requested “Let's continue” after confirming the first interface, reference totals, logout/re-login and narrow-window checks. Implement the next bounded access-administration dependency within approved AD-005/AD-011/AD-013.
**Authority:** AD-005 six roles/exact scope tuples and registered organisational identity; AD-011 attributable transactional changes, stale-write protection and all-device revocation; AD-013 Section 11.3 expandable user/role controls and form/accessibility rules.

## Scope and boundaries

Superadmin-only searchable/paginated Pengguna & Peranan list, explicit registration and identity/status edits, six-role assignment editor with independently selected scope tuples, mandatory reason and attributable before/after audit. Use exact existing PTJ/workplace/activity records. Ketua Bidaan is JPNT-only; workplace-specific Penyedia Anggaran assignments are limited to the user's own registered workplace and its parent PTJ. Selecting a role without an explicit child assignment never expands access. Existing central PTJ/activity/OS permissions remain independent and unchanged; do not seed additional OS choices, real users or role defaults.

An account save, assignment diff, access revision increment, database-session removal and audit must commit atomically. Preserve stable IDs for unchanged assignments. Reject stale editors rather than overwrite. Case-insensitive database email uniqueness refuses ambiguous existing data rather than merging identities. No password, Google subject or email-verification value is administratively set; Google remains the login authority.

All protected requests compare a server-stored access revision captured during verified Google login against the current account revision. Existing sessions without this new revision are invalidated once after migration. Deactivation and access changes revoke previous sessions across devices/drivers. Superadmin may explicitly terminate all sessions of a selected user without deactivation; authenticated users may log out all their own ABM sessions. Ordinary logout still ends the current session only; Google is unaffected. Forced revocation is audited, CSRF protected, rechecks actual authority under row locks and requires a reason. No individual-device management is added.

This is a bounded administration slice, not complete security/production delivery. Security alert emails, affected-user deactivation notifications, Security Events viewer, mail delivery configuration and operational MFA verification remain pending under AD-011/AD-012. No emails are sent during development execution. Central PTJ/activity/OS permission editing, Kitaran/Fasa administration and financial persistence are outside this batch.

## Verification and local acceptance

Pending: full isolated PostgreSQL suite; mutation/denial/scope/rollback/stale-write/session tests; frontend build; browser dynamic-row/search/own-workplace filtering and overflow checks; visual review; merge evidence; local migration and UI confirmation.

Batch 008 remains partially manually accepted. Its known code/merge/local evidence is already recorded; pending actual-device, pagination, unregistered-account and session-timing manual checks are not silently marked passed by starting Batch 009.
