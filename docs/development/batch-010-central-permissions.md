# Batch 010 — Pengurusan Aktiviti Kewangan

**Status:** In progress; verification, merge and local acceptance pending.
**Baseline:** 674b80a78019dadc3ae3323c205baba1033b2eeb on dev.
**Authorisation:** User approved Batch 010 after the explicit proposal for the central PTJ → Aktiviti Kewangan → OS permission editor.
**Authority:** REF-04 source identities/mappings/exclusions; REF-05 category defaults, individual overrides and inheritance; AD-005 Superadmin and exact independent role scopes; AD-011 atomic audit/default deny/stale-write safeguards; AD-013 Section 11.3 expandable checkbox hierarchy.

## Bounded scope

Superadmin-only searchable/paginated PTJ list, three category-default editors and individual-PTJ editors. Expand each activity to configure that activity and each eligible OS independently. A category activity is an explicit on/off default; category OS remain unconfigured until explicitly set. An individual PTJ setting either explicitly allows/denies or inherits its category value per setting. Resetting an override deletes that override only. A stored false is distinct from missing configuration; no arbitrary OS/category/PTJ assignments are seeded.

Disabling a parent masks, but never deletes or rewrites, its stored OS choices. Checking a parent never checks all OS. Same-code purposes use their distinct catalogue IDs and descriptions. Only non-excluded mapped OS are offered/accepted; POL and AMal remain excluded, and existing source data, roles, financial records and financial hierarchy are unchanged. Neither a permission toggle nor catalogue eligibility certifies an implemented wizard/calculation.

Each activity save includes a complete explicit set of displayed eligible OS, a mandatory reason and a content-state revision covering target category, current category/individual settings and eligible reference identities. Recheck actual active Superadmin/session revision in the transaction. Serialise central category/PTJ writers with a transaction advisory lock so category dependencies cannot change between checking and saving. Reject changed target/inherited/reference state with HTTP 409. Update only that target/activity, preserve unchanged setting IDs, and commit changes with attributable before/after audit. No schema migration or seed is required. No persisted permission cache is introduced.

Client previews are labelled unsaved; saved counts change only after a successful server redirect. Multiple activity forms track unsaved changes separately and require confirmation before discarding another form's edits. Existing Google/session policy, user assignments and Superadmin exceptional authority remain unchanged.

This is central configuration administration, not financial workflow delivery or production/security certification. Future financial operations must intersect current central permissions with actual user/role/workplace/Fasa rules inside their own transaction. Security email/notification operations and previously recorded actual-device/session manual checks remain pending.

## Verification and handoff

Pending: isolated PostgreSQL suite, scope/denial/inheritance/reset/partial-payload/exclusion/variant/stale/audit-rollback cases, build/browser controls and multi-form unsaved-state checks, screenshot review, merge checkpoint and local inspection. Default-only inspection must not be recorded as an actual permission-mutation test.
