# Batch 007 — Audit Trail Foundation

Baseline: `f498f51` on `dev`. Authority: AD-005 accountability for all six roles and AD-011 transactional, attributable, append-only audit history. This is internal infrastructure for later protected business operations, not an administration feature or a claim that existing/future operations are all audited.

## Event storage and identity

`rekod_audit` stores the actual authenticated actor, effective role, a snapshot of organisational identity and effective-role assignments, action, stable target type/ID, explicit before/after changes, optional reason and relevant PTJ/activity/workplace/Kitaran/Fasa/submission context. Actor/reference foreign keys restrict destructive parent deletion. Target IDs are stored as strings to support stable domain identifiers; no extra financial hierarchy is created. Fasa reference and submission version are contextual evidence, not new workflow entities or version-generation logic.

The event timestamp uses PostgreSQL `timestamptz` and an explicit UTC offset supplied by the writer. It represents the correct UTC instant even if the database session uses another timezone. Rendering in local time remains a consumer concern. Financial decimals should be supplied as exact strings; the audit layer preserves them rather than recalculating or converting amounts.

## Transactional writer contract

`JejakAudit::jalankan` obtains actor identity from Laravel Auth, never from caller-supplied actor IDs. It reloads/locks the active registered actor and role assignments inside the default-connection transaction. It captures role/organisation before invoking the server operation. A Superadmin acting in another functional scope is recorded as the actual Superadmin, without replacing identity. Non-admin effective roles must be explicitly assigned, with current JPNT identity required for ordinary Ketua attribution.

The callback performs and authorises its business operation on the same default database connection and returns a server-constructed `PeristiwaAudit` with explicit material changes. The writer validates metadata, required reasons when declared by the caller, and change payloads, then inserts the audit record within that transaction. A business exception, invalid payload or failed audit insert rolls back the associated database operation. Enclosing transaction rollback also rolls back nested audit inserts. Do not catch/suppress failures and report success.

This primitive verifies attribution, not business authority. Callers must still enforce current role/workplace scope, PTJ/OS permissions, workflow state, stale-write checks and mandatory reasons for each action inside the transaction. An assigned role alone does not authorise the callback's mutation. Jobs must establish a verified requesting actor context server-side; anonymous/system-job event policy is not invented here. External effects such as email/network requests cannot be rolled back by the database and must not be treated as atomic callback effects.

Only explicit scalar/array changes are accepted; raw Eloquent models and objects are rejected. Sensitive field names (including nested/camel-case passwords, tokens, secrets, credentials and keys) reject the event and roll back the operation. This is a guardrail, not automatic detection of secrets in arbitrary free text. Server callers must minimise payloads and never copy raw requests, credentials, full model dumps or sensitive strings into reasons, action names or descriptions.

## Append-only protections and limits

The read model rejects ordinary update/delete operations. PostgreSQL `ENABLE ALWAYS` triggers independently reject row updates, deletes and table truncation, including direct SQL and model-event bypass. Corrections are additional events. Role assignment revocation cannot rewrite the recorded scope snapshot. There is no update/delete audit API, fixed retention duration or purge operation.

These controls protect normal application SQL operations. A database superuser or schema/DDL owner can still disable/drop triggers or alter/drop the table; they are not a cryptographic or infrastructure-administrator tamper-proof guarantee. Production must separate migration/schema ownership from the least-privileged runtime role and verify runtime SELECT/INSERT-only audit privileges under AD-011/AD-012. Existing development credentials/roles are not silently altered by this batch. Table migrations/recovery remain privileged operational actions. The trigger function uses CREATE OR REPLACE to permit isolated test databases to be recreated without function-name conflicts.

## Verification and exclusions

Tests verify actual actor/role/scope attribution for all six roles, Superadmin attribution, current account/role checks, exact decimal strings, UTC instants under a non-UTC database session, required-reason and sensitive-field failure, database-audit failure rollback, business/outer-transaction rollback, append-only model/direct-SQL protections, appended corrections and preservation of actor/scope history.

No public route, audit viewer/export, grant/revoke/activation API, automatic observer over every model, security-email notification, financial snapshot implementation, retention policy or production-readiness declaration is added. Routine security/access logging remains distinct. Approved specifications, user assignments, reference data and financial rules are unchanged. Actual PostgreSQL CI results are recorded on the PR.

## Local synchronisation after verified merge

Pull clean `dev` with fast-forward only, clear configuration and verify the actual PostgreSQL database is `sistem_abm`, then run `php artisan migrate`. There is no audit seeder. Do not run fresh/reset migrations against development data.
