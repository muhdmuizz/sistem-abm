# Batch 013 — Audited Financial Activation

**Status:** In progress; CI, merge and local migration pending.
**Date:** 14 November 2026 (Asia/Kuala_Lumpur).
**Baseline:** `4e42ddaa588cd3a281730fad44440ad54cb5361d` on dev.
**Authorisation:** User requested the next development batch after canonical storage. Implement the next shared prerequisite: audited initial Bidaan/OS activation services. Apply AD-013 to future UI and retain the deferred broader refinement. Batch 012 local migration is still recorded pending; “Proceed for next” is not treated as proof of local execution.

## Scope and authority

AD-002/003: one Bidaan per PTJ/activity/cycle, distinct Penyedia Bidaan activation and Penyedia Anggaran OS activation. Activation creates canonical records; no cart or extra financial owner. AD-005: exact role tuples, real actor and Superadmin authority. AD-006: open dated/scoped Fasa for ordinary preparers. AD-009: four approved workflow statuses; only initial Dalam Penyediaan is handled by this service. AD-011: transaction, locked state, fresh authorisation, stale-write conflict and mandatory atomic audit.

Add a four-value Bidaan status enum/column defaulting to Dalam Penyediaan; no submission version, snapshot or status-transition action. New service operations activate initial Bidaan and initial OS. Existing active records are returned only when the expected current parent revision matches, without duplicate audit/records. Activating an OS increments the parent Bidaan revision, invalidating stale editors. Existing inactive OS with retained sources are not reactivated here: approved recalculation/revalidation must be implemented first.

Transactions lock actor/assignments, the existing central-permission configuration advisory key, Kitaran, then Bidaan. This serialises creation and Fasa/permission changes with the relevant mutation. Recheck authenticated subject, active account, organisation, role, access revision and idle/absolute session deadlines inside the service. Ordinary roles must pass exact assignment, effective central permission and matching open Fasa at Malaysia calendar dates. Own-workplace preparers use their registered workplace tuple; OS activation does not grant cross-workplace source editing.

Superadmin retains its approved cross-scope authority without impersonation. An explicit reason is mandatory when bypassing central permission or Fasa eligibility; audit records both bypass flags. Superadmin cannot activate excluded or unmapped OS, mutate submitted/finalised states through this initial-only service, or ignore stale revisions. There is no independent edit exception for ordinary preparers.

## Boundaries

Backend services only; no new route/UI, seed, draft-save API, source/output mutation, OS completeness/calculation, reactivation of retained financial rows, deactivation, submission/review/finalisation or notifications. Existing financial data and audits are retained. Step 02 remains in progress; browser regression exercises existing screens and does not certify a new financial interface. The known empty-state visual defect remains deferred/tracked.

## Verification

Pending isolated PostgreSQL tests, build/browser regression, tested SHA, actual CI counts, merge checkpoint and guarded local normal migration. Keep the existing local server running.
