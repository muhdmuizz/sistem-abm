# Batch 013 — Audited Financial Activation

**Status:** Code verified and merged; local migration pending.
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

Tested application head: `c91a7126bb0d6ef0830d129e75d98b75ff299d9f`.

[CI 43535741061](https://github.com/muhdmuizz/sistem-abm/actions/runs/43535741061): **239 tests passed / 2005 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`; frontend build and all existing Chromium checks passed. Thirty-three added cases cover role separation/additive roles without cross-combining tuples, fresh activity/OS grants, no implicit OS grant, Fasa/date/scope denial, active parent/stale revision requirements, own-workplace assignments, protected workflow states, real-actor reasoned Superadmin bypass, catalogue exclusions/mapping, idle/absolute expiry, missing/future/revoked sessions, deactivated/unregistered-subject actors, lost roles, idempotent current retries, retained source data and actual PostgreSQL audit-insert failure rollback. Parent revision and canonical identities remain unchanged on failure.

Early CI revisions exposed an incorrect Laravel shared-lock method name and JSONB key-order assumptions in assertions; corrected before successful verification. No unsuccessful application revision is merged. Multiple-connection/physical-device/manual acceptance is not claimed. No UI source changed; browser results are existing-screen regression, not certification of a financial interface.

[PR #28](https://github.com/muhdmuizz/sistem-abm/pull/28); merged at `bf69e80a2d21712cefe917db033c900c6d043f09`. Local normal migration remains pending; the handoff will apply Batch 012 too if still unapplied. Step 02 remains in progress: source draft save services and financial screens remain pending. Keep the existing local server running.
