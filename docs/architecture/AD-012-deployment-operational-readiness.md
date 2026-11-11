# AD-012 — Deployment & Operational Readiness

**Status:** Finalised — technical architecture (delegated implementation design)  
**Date:** 11 November 2026  
**Related:** AD-002, AD-003, AD-005, AD-006, AD-008, AD-009, AD-010, AD-011

## Purpose and scope

Define safe operational deployment and recovery for Sistem ABM (Laravel 13, PostgreSQL and Vite as recorded in the handover). This is an **architecture specification**, not confirmation that a server, deployment pipeline, backups, migrations, production integrations or application tests already exist. Repository root application files were not found during the 11 November architecture review; actual source layout and hosting must be inspected before executing this plan.

No changes to approved financial workflows, role scopes, OS calculations, Excel formats or Analisis behaviour are authorised by this AD.

## 01 — Environments and configuration

- Maintain isolated **local/development**, **staging/test** (when provisioned) and **production** execution contexts, with separate database credentials, storage, secrets and integration settings. Production data must not be casually copied into development; sanitise any authorised test copy.
- Use environment variables or managed secrets for connection strings, identity integration, encryption keys, mail, storage and queue settings. Never commit real secrets or production .env files.
- In production: disable debug output, serve only the public web root, use HTTPS, set trusted proxy/host handling appropriately, and use secure cookies/session settings.
- Apply least privilege for application, migration and backup database accounts. Keep schema-changing credentials separate from normal runtime access where feasible.
- Configuration is environment-specific; no hardcoded PTJ, Kitaran, Fasa, or financial year defaults that bypass configured records.

## 02 — Release and deployment discipline

- Treat Git commits as the source of truth; use reviewed and identifiable release revisions. Maintain a reproducible dependency lockfile and compiled Vite assets for each release.
- Before deploying: verify branch/commit, dependency compatibility, environment configuration, secret availability, DB connectivity, asset build, queued-job compatibility, and backup/recovery readiness.
- Execute dependency installation/build and automated checks before traffic cutover. Use Laravel production-safe cache/config commands as appropriate to the actual application layout.
- Migrations must be versioned, reviewed and tested against a representative dataset. Prefer backward-compatible **expand → deploy → backfill → contract** schema evolution when running old and new code may overlap.
- Never rely on automatic destructive migration rollback for financial data. Use a documented forward-fix or restore plan, with database snapshots and recovery checkpoints where warranted.
- Record release identifier, deployed commit, migration versions, operator, timestamp, verification results and any rollback/forward-fix actions.
- Use maintenance mode or controlled cutover only where required by compatibility; do not assume zero downtime without a verified strategy.
- No production release may be declared successful solely because the build passes.

## 03 — Database protection, backup and recovery

- Back up PostgreSQL financial records, immutable submission snapshots, audit records, historical expenditure imports and essential metadata. Include uploaded/source files and application-managed storage where needed for complete recovery.
- Use automated backups with at least one copy isolated from the application host; encrypt backup data in transit and at rest, and restrict access.
- Choose concrete backup frequency, retention and point-in-time recovery capability based on actual hosting, data volume and approved operational risk tolerance. Do **not** assert unverified RPO/RTO or a fixed retention period.
- Test restoration to an isolated environment on a recurring basis and before major risky migrations. Verify database consistency, financial totals, audit/snapshot continuity and referenced file availability.
- A backup is not considered operationally validated until an actual restore succeeds. Keep a recovery runbook and identify who can invoke it.
- Recovery must not silently replace newer immutable financial evidence; restoring older data is a controlled incident requiring reconciliation of any post-backup submissions and decisions.

## 04 — Queues, scheduling and large data operations

- Use bounded, resumable background processing for operations that exceed normal HTTP request limits (e.g. large official Excel extracts, historical imports and intensive granular recalculation), when implementation warrants it.
- Configure supervised queue workers and scheduled tasks only if those facilities are actually used; monitor failed jobs and retry counts.
- Make retries safe: a repeated job must not duplicate submissions, snapshots, historical imports or audit business events. Use stable operation identifiers and transactionally guarded state transitions.
- Bind jobs to the initiating real user, effective authorised scope and consistent financial version; revalidate permissions at execution where required.
- Avoid unbounded memory loading for large Butiran Anggaran; stream/chunk reads and writes while preserving totals and export ordering.
- Distinguish technical retryable failures from data validation failures requiring correction. Never silently skip invalid financial rows.

## 05 — Security and operational monitoring

- Log structured application failures, deployment events, queue errors, security denials and database health signals, with correlation IDs and no leaked secrets.
- Preserve AD-011 append-only financial audit records separately from ordinary operational logs; neither application log rotation nor queue retries may destroy authoritative audit evidence.
- Monitor availability, response latency, error rates, DB connection capacity, storage growth, backup success, queue backlog and failed jobs. Define actionable alerts during implementation against actual service capacity.
- Keep PHP, Laravel dependencies, JavaScript dependencies and server components patched under a controlled update process; review security advisories and dependency vulnerabilities.
- Restrict production access and privileged operations. Ensure administrator overrides and exceptional financial reopening retain actual actor attribution.
- Define incident handling: detect, contain, preserve evidence, assess financial integrity, recover, and document the incident. Avoid publishing sensitive records in incident logs.

## 06 — Release verification and acceptance gates

A deployment candidate must pass the applicable gates:

1. **Source and build:** correct revision, dependency install, asset build and application bootstrap succeed.
2. **Database:** migrations tested, relational constraints valid, no unintended destructive changes, backup and restore plan verified.
3. **Identity and scope:** all six AD-005 roles and assignment scopes enforced by server-side checks, including direct requests and bulk operations.
4. **Financial integrity:** canonical totals reconcile; immutable submission snapshots and audit events survive correction, Pindaan and Superadmin reopening.
5. **Workflow:** AD-003 activation/completeness, AD-006 Fasa controls, AD-009 parallel review and Ketua-only finalisation behave as approved.
6. **Reporting:** AD-010 historical comparison, Analisis filters and official Excel scopes conform to their finalised specification; no new export rules are introduced.
7. **Concurrency and resilience:** stale writes, repeated requests, interrupted imports and queue retries do not create inconsistent financial records.
8. **Operational:** logs, health checks, backup status, recovery runbook and release verification are available to the responsible operator.

Where OS-specific calculations or official payroll template mappings are not yet implemented (AD-008 and pending official template), record those gates as **not met**, not silently waived or assumed passed. An architecture-complete system is not automatically production-ready.

## 07 — Operational handover and release records

Maintain a concise release checklist/runbook with: environment identifier, deployed Git SHA, migration list, build artefact reference, backup checkpoint, test results, outstanding known limitations, operator, date/time and post-deployment verification.

Maintain a recovery runbook covering application rollback/forward-fix, database restore, file restoration, queue handling, and reconciliation of financial versions and audit records. Establish actual owners, hosting specifics, retention and recovery objectives when the deployment environment is selected.

## Architecture closure and dependencies

AD-012 finalises **technical operating principles only**. Concrete hosting, pipeline, credentials, backup schedule and recovery targets are implementation/operations inputs and must be verified rather than invented. AD-008 remains deferred; its calculation rules are not filled in here. AD-010 remains finalised and unchanged. No code, migrations, test executions or deployment are claimed by this document.

## Initial hosting decision — shared VPS with Sistem e-Nota Minta (11 November 2026)

**Decision status:** Initial direction approved; detailed deployment design and operational verification deferred.

- **Sistem ABM is intended to be deployed on the same VPS used or designated for Sistem e-Nota Minta.** This is a shared hosting decision, **not** a decision to merge the two applications.
- Keep each Laravel application independent: its own application source/release lifecycle, environment secrets, PostgreSQL database and credentials, application-managed storage, and deployment/recovery controls. Separate domains or subdomains and process/queue configuration will be determined during implementation.
- Deployment must avoid unintended interruption or cross-access between the applications. Shared host resources and operational dependencies require explicit verification before rollout.
- **Not yet verified or decided:** VPS capacity and operating system/runtime versions, present e-Nota Minta deployment status, network/DNS/TLS configuration, process isolation, web-server routing, backup schedule, monitoring, CI/CD, and exact deployment procedure. No claim of ABM deployment or production readiness is made.
- **Deferred next action:** Inspect the real VPS and both application codebases, assess capacity and isolation, then complete the deployment plan and acceptance checks in this AD without inventing a separate architecture workstream.
