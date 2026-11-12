# Sistem ABM — Security Baseline and Pre-launch Verification Checklist

**Adopted for design and verification:** 13 November 2026  
**Status:** Approved security baseline; **implementation and testing NOT verified**  
**Authority:** [AD-005 Identity, Roles & Organisation](../architecture/AD-005-identity-roles-organisation.md), [AD-011 Data Integrity, Audit & Security](../architecture/AD-011-data-integrity-audit-security.md), [AD-012 Deployment & Operational Readiness](../architecture/AD-012-deployment-operational-readiness.md). Use OWASP ASVS as a verification reference, not as a claim of certification.

## Scope and boundaries

This baseline adapts the user's pre-launch security checklist to **Sistem ABM (Laravel 13, PostgreSQL, Vite)** and its approved **public Google sign-in page / post-authentication registered-user authorisation**. It specifies **controls to implement and verify**, not evidence of working security. It does **not** change approved financial hierarchy, six roles, PTJ/Aktiviti Kewangan/Tempat Bertugas scopes, OS rates, 27-column Excel contract, review workflow or historical snapshots. In any conflict, existing functional ADs govern.

**Verification states:** `NOT TESTED` until implementation/evidence exists; `PASS` only with reproducible test evidence; `FAIL` for a demonstrated gap; `N/A` only with a written rationale and scope. A Markdown checklist is **not** a security test report.

## Layer 1 — Identity and access security

- [ ] **SEC-01 Public sign-in, protected application:** An unauthenticated mobile/desktop user can reach `/login`, click **Log masuk dengan Google** and use Google's account chooser; no pre-login MyGovUC restriction. Unauthenticated access to application pages/APIs remains blocked.
- [ ] **SEC-02 Google authentication validation:** Use an approved OAuth 2.0/OpenID Connect integration with redirect URI restrictions, state anti-CSRF protection and PKCE where supported/appropriate; validate identity token/signature, issuer, audience, expiry and nonce when relevant. The Google account chooser, email text and optional domain hints alone do **not** grant permission. Link identity to a registered user using a stable provider identity, and require an active account and approved organisational eligibility.
- [ ] **SEC-03 No automatic provisioning or local user passwords:** Google handles the password and its required MFA. Do not store MyGovUC passwords in ABM. Local password hashing and reset-link expiry are **not applicable unless a separately approved local-password fallback is introduced**.
- [ ] **SEC-04 Server-side six-role authorisation:** Enforce AD-005 PTJ, Aktiviti Kewangan and Tempat Bertugas scopes for individual records, search, lists, mutations, bulk operations, direct URLs/APIs, exports, jobs and administrative actions. No role or assignment may be trusted from the browser. Test horizontal and vertical privilege escalation.
- [ ] **SEC-05 Account/permission revocation:** Disable or revoke ABM access without delay; ensure current sessions cannot retain revoked access. Protect Superadmin routes/actions server-side. Audit account provisioning, role changes and overrides.
- [ ] **SEC-06 Session and cookie security:** Use HTTPS-only Secure, HttpOnly and suitable SameSite session cookies; session regeneration on authentication; controlled logout and session invalidation; CSRF protection; idle and absolute expiry. **Approved idle timeout: 30 minutes of genuine user inactivity, enforced server-side; warn 5 minutes before expiry, permit explicit Teruskan Sesi.** Background autosave/polling must not extend idle lifetime indefinitely. Allow separate sessions on multiple devices, terminate only the current ABM session on normal logout, and protect saved drafts under AD-004. Revoke access for deactivated accounts and recheck changed permissions on protected requests. **Absolute maximum session lifetime remains undecided.** Protect unsaved spreadsheet-like Anggaran work on expiration; never claim failed saves succeeded.
- [ ] **SEC-07 MFA:** Verify actual MyGovUC/Google Workspace MFA requirements; require appropriate privileged-account assurance through approved identity-provider policy where administratively possible. Do not claim MFA is enforced until verified.
- [ ] **SEC-08 Abuse protection:** Rate-limit OAuth initiation/callback misuse, sensitive endpoints and repeated access denials appropriately; Google protects password sign-in on its own service. Avoid account enumeration and access-denied redirect loops.

## Layer 2 — Financial and data integrity

- [ ] **SEC-09 Validate all inputs on the server:** Forms, imports, uploads, filters, row edits and APIs must reject invalid/malicious input regardless of browser validation.
- [ ] **SEC-10 Verify approved Kadar and financial outcomes on the server:** Validate reference-derived Kadar against the approved reference; validate mandatory user-entered Kadar per OS rules; never trust browser-submitted rates/totals as authoritative. Preserve AD-008 calculations, canonical identities and exact decimal money representation.
- [ ] **SEC-11 SQL injection prevention:** Use Eloquent/query-builder parameter binding; restrict raw SQL and review any necessary raw expressions; use least-privilege PostgreSQL credentials.
- [ ] **SEC-12 Isolation, transactions and concurrency:** PostgreSQL relational constraints, server-side scoped queries, permission/state rechecks in critical transactions, stale-write protections, idempotent submissions, traceable Butiran identities and immutable submitted snapshots follow AD-011.
- [ ] **SEC-13 Audit integrity:** Preserve real actor, financial changes, approvals, administrative changes and security-relevant denials; append-only audit/snapshot protections; restrict read and modification of audit evidence, and keep routine security logs distinct.

## Layer 3 — Application and infrastructure hardening

- [ ] **SEC-14 HTTPS and headers:** Enforce HTTPS/TLS in production, correct trusted-proxy and host configuration, appropriate HSTS once TLS configuration is validated, and safe response headers. Review a restrictive **Content Security Policy (CSP)** in Report-Only mode before enforcing a policy compatible with Laravel/Vite and Google redirects.
- [ ] **SEC-15 XSS protection:** Use output encoding/escaping, safe templates and sanitisation where rich text is genuinely allowed; reject unsafe inline scripts. CSP is defence in depth, not a substitute.
- [ ] **SEC-16 CSRF protection:** Apply framework protections to state-changing browser routes, including applicable logout; OAuth `state` separately guards the federated login transaction.
- [ ] **SEC-17 CORS:** Restrict cross-origin API access to explicitly needed origins; avoid wildcard origins with credentials. Same-origin usage does not require inventing a cross-origin service.
- [ ] **SEC-18 File uploads:** For supported uploads, enforce authorised endpoints, allowlisted extensions and actual file type/content validation, size limits, randomised storage names, storage outside executable public paths, and malware scanning when risk warrants. Protect downloaded files by server-side permissions.
- [ ] **SEC-19 Secrets:** Keep OAuth client credentials, Laravel app keys, database passwords and API tokens only in server-side secret configuration; no secret in front-end builds, browser responses or Git. Limit secret access and rotate exposed credentials.
- [ ] **SEC-20 Production configuration:** `APP_DEBUG=false`, safe error pages, minimal DB/runtime privilege, isolated ABM secrets/data from co-hosted e-Nota Minta, and restricted Superadmin/deployment access.
- [ ] **SEC-21 Encryption and backups:** Encrypt transport and sensitive backup/storage data at rest with protected keys and controlled access; determine specific database/disk encryption settings from the actual host. Test restoration, audit/snapshot continuity and financial reconciliation; do not claim an untested backup is recoverable.
- [ ] **SEC-22 Dependencies and supply chain:** Update supported PHP/Laravel/JS/server packages under controlled change, keep reproducible lockfiles, remove unused dependencies, audit vulnerable packages and scan full Git history for leaked secrets; rotate any exposed keys.

## Layer 4 — Continuous assurance and release gate

- [ ] **SEC-23 CI security checks:** Use suitable SAST, dependency vulnerability audits, secret scanning and automated security regression checks in the actual GitHub pipeline once configured. Document accepted exceptions.
- [ ] **SEC-24 Logging and alerting:** Log suspicious Google callbacks, failed sign-in/authorisation, privilege changes, security errors, deployment and backup failures. Centralise/secure logs where operationally appropriate; remove secrets and unnecessary personal data; configure actionable notifications and accountable responders.
- [ ] **SEC-25 Security tests:** Cover public login/mobile Google chooser; invalid OAuth responses/replay; unauthorised users; all six roles and PTJ/workplace boundaries; rate tampering; XSS/SQLi/CSRF; file uploads if present; cookie/session expiry; revocation; concurrency; and protected Excel exports. Retest every material fix.
- [ ] **SEC-26 Pre-launch security report:** Record environment, build/commit SHA, review date, tester, test case/evidence, result (`PASS`/`FAIL`/`N/A`), issues, mitigations, owners and approval. **No production launch declaration** unless all applicable critical security gates and AD-012 release gates have evidence and blockers are resolved.

## Current unresolved implementation inputs

1. **Session policy:** 30-minute idle timeout, 5-minute warning, explicit renewal, multi-device sessions, ABM-only logout and AD-004 autosave are **approved**; choose **absolute session lifetime**, then implement and test all of these controls. Only successfully persisted drafts may be represented as recoverable after reauthentication.
2. **Google Workspace identity:** verify tenant-side integration approval and MFA enforcement/administrative policy; confirm required account-eligibility checks.
3. **Deployment:** inspect actual ABM source/server, HTTPS/proxy, isolation from e-Nota Minta, backups, logging and CI capabilities.
4. **Uploads and encryption:** determine actual uploaded-file use cases, sensitive-data classes and at-rest encryption implementation.
5. **Verification evidence:** security controls are requirements, **not yet demonstrated implementations or test passes**.

## Pre-launch evidence register template

| ID | Control | Status | Evidence / test reference | Owner | Date |
| --- | --- | --- | --- | --- | --- |
| SEC-01–SEC-26 | See controls above | NOT TESTED | To be recorded after execution | Not assigned | — |

Populate a separate test record per control when evidence exists. Approval of this security baseline does **not** approve a deployment or imply a completed security assessment.
