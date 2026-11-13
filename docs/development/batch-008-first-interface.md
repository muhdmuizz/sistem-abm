# Batch 008 — First Interface & Google Sign-in Preparation

**Status:** Final verification in progress after contrast and Google-only registration-schema refinements; merge/local setup and real Google sign-in pending.
**Baseline:** `2d84e0c` on `dev`.
**Authorisation:** User requested continuation after documentation reconciliation; the visible local interface is the next deliverable. User explicitly confirmed that Google OAuth is not configured yet.
**Authority:** AD-005 Google sign-in/registered-user access; AD-011 session and audit requirements; AD-013 Clean Enterprise interface; existing reference and role rules.

## Bounded delivery

Public sign-in page, a shared responsive application shell, a home page with truthful module availability, and protected read-only PTJ/Aktiviti Kewangan/Tempat Bertugas/OS lists. Global reference data is restricted to active registered Superadmin users; other assigned users see only the home shell. Desktop sidebar preference persists; mobile uses an accessible dialog drawer. Lists paginate at 25 rows and use compact cards on mobile. No arbitrary financial dashboard or fake financial data is presented.

Google authorization-code flow uses state, PKCE and account selection. The server exchanges the one-time code at Google and fetches identity directly from Google's HTTPS userinfo endpoint with the resulting access token. Browser-supplied identity claims/JWTs are not consumed; no unverified ID token is decoded, and tokeninfo is not used as a production verifier. Eligibility requires a Google-verified email, a hosted domain in the operator's configured allowlist, an active registered organisational user, and an explicit role assignment. Account/domain hints alone do not authorise access. The stable Google subject is linked uniquely to that existing account, and the real authenticated actor is audited in the same transaction. No automatic user provisioning or email-only authorisation is introduced. The legacy password column becomes nullable and is no longer mass assignable, permitting Google-only registrations without dummy passwords; existing values are retained and no password route is added. Downgrade to the password-required schema is refused while null-password accounts exist. Tokens/secrets/provider errors are not stored or flashed.

Protected requests recheck current status/assignments. Server enforces 30-minute idle and eight-hour absolute expiry. The UI warns five minutes before expiry and permits explicit renewal without resetting the absolute deadline. These server-rendered pages make no automatic polling/autosave requests that refresh activity. Ordinary POST logout invalidates the current ABM session only. Full administrative account/session-revocation services remain pending and are not claimed by these read-only screens.

## Setup and verification boundaries

See [local Google setup](google-local-setup.md). The code can render the public sign-in page before credentials exist; sign-in is explicitly unavailable until configured. No local authentication bypass is added. Credentials and the approved hosted-domain list must be entered in local `.env`, never committed or pasted into chat.

An active, organisationally registered user with an approved explicit role is also required. No real users are created, activated or assigned roles by this batch. Initial account provisioning must be reviewed against actual supplied identity details; neither a guessed email nor an invented operator audit identity is acceptable. Real Google sign-in cannot be certified from HTTP mocks/browser fixtures.

HTTP tests cover routing, role restrictions, current-account checks, code/state handling, eligibility/binding and session timing. CI additionally builds existing frontend assets and runs Chromium checks/screenshots against actual Blade output rendered with isolated test identities. The static screenshot fixture server exists only in CI; it is not an application route or login bypass.

Audited user/permission editing, financial modules, OS defaults, new real-user assignments, complete security certification and production deployment remain outside this first interface slice. No approved AD or reference rule is amended.

## Evidence and close-out

- **Tested application head:** `768b5b1cff838dd932fb65b1bc863341ccbad7fe`.
- **CI:** [run 43410640128](https://github.com/muhdmuizz/sistem-abm/actions/runs/43410640128), PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`: **124 tests passed, 933 assertions**, including 16 new HTTP cases. Existing frontend build and Chromium layout checks passed.
- Merge checkpoint: pending.
- **Browser evidence:** [screenshot artifact](https://github.com/muhdmuizz/sistem-abm/actions/runs/43410640128/artifacts/14028848406). Desktop reference table, mobile cards/header and sign-in page were downloaded and visually inspected. Sidebar persistence, accessible collapsed navigation, mobile drawer/escape and overflow checks passed. These are test-identity fixtures, not real-provider sign-in evidence.
- **Earlier verification correction:** an initial PHPUnit helper collided with its final callback method; renamed before execution. The intermediate verified revision passed 120 tests / 914 assertions; final results above include subsequent cache/logging and eligibility hardening.
- Local pull/migration: pending.
- Actual OAuth configuration, initial registered account and real-provider login: pending (user has no configured OAuth client yet).

Do not call this batch fully closed until required local/provider verification is explicitly recorded.
