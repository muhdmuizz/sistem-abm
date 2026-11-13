# Sistem ABM — Repository Working Instructions

## Read before work

1. Read `docs/development/README.md` for the latest implemented checkpoint, pending local synchronisation and proposed work.
2. Read `docs/architecture/README.md` and the relevant approved AD/reference documents before defining or implementing a batch.
3. Read the relevant batch MD and verify the live `dev` head. Do not recreate completed work or use the abandoned legacy application.

The architecture README is the single master AD tracker. The development README tracks implementation and execution evidence; it is not another architecture tracker or a new development plan. Approved decisions outrank implementation notes. Do not change architecture, roles, financial rules, reference assignments or an agreed batch's scope without user authorisation. Routine technical choices within already-authorised scope do not require another approval.

## Required progress records for every batch or material correction

- Create/update `docs/development/batch-NNN-*.md` with the bounded scope, authoritative specifications, baseline and implementation boundaries.
- Update `docs/development/README.md` when work starts, verification completes, the PR merges or local synchronisation is confirmed. Label proposals, in-progress work, merged code and locally applied changes separately.
- Record the actual tested head SHA, CI/log link, runtime/database, result and test/assertion counts only when execution evidence supports them. Distinguish static review, cloud CI and user-reported local execution.
- Before merging, record verification and any unresolved limitation in the batch MD. If the merge SHA or later local confirmation is not yet known, mark it pending.
- After merging, record the verified merge checkpoint and PR link. Use a documentation-only follow-up when final metadata was not available before merge; do not invent a self-referential commit SHA.
- After local confirmation, record exactly what the user confirmed, without claiming direct access to Windows or independent database inspection. A brief “Clean” or “Completed without errors” confirms the supplied synchronisation commands; it does not certify unexecuted UI, security or financial behaviour.
- Reconcile stale status-only statements in relevant reference/index documents without silently changing approved requirements. Mark historical counts as historical when later corrections change the inventory.
- Do not move to another implementation batch with the previous batch's known checkpoint/test/local status missing from Markdown. Do not call a batch fully closed while required verification or local synchronisation is pending.
- Keep pending work and uncertainties explicit. A proposed next interface or batch is not approved merely because it was suggested in conversation.

## Execution and scope safeguards

Use a dedicated feature branch and PR targeting `dev`. Do not merge untested database implementation. Keep credentials and environment files out of Git. Run database tests only against isolated `sistem_abm_testing`; never use destructive fresh/reset migrations on development data.

Do not assume cloud access to `C:\Projects\sistem-abm`. When local execution is necessary, provide consolidated PowerShell commands with appropriate branch and database checks. Do not require full successful console output when the user has opted to report only errors.

For routine local updates, keep the existing development server running. Provide pull/cache commands for another terminal and ask the user to refresh the browser (Ctrl+F5 for changed assets). Request a restart only when the specific change requires it, and explain that reason. Do not include stop/start server commands as routine boilerplate.

A passing foundation test suite is not proof of Google integration, administration UI, complete financial workflow, production privilege separation or release readiness. Preserve the distinction in every progress report.
