# Batch 008 — First Interface & Google Sign-in Preparation

**Status:** Code/build/browser verification passed and merged; local/provider acceptance pending.
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

- **Tested application head:** `b4af2551f9355db6d0d7821a70170cc1ad6661b5`.
- **CI:** [run 43412032393](https://github.com/muhdmuizz/sistem-abm/actions/runs/43412032393), PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`: **125 tests passed, 937 assertions**, including 17 new HTTP cases. Existing frontend build and Chromium layout checks passed.
- **Merge:** [PR #10](https://github.com/muhdmuizz/sistem-abm/pull/10), `e5a93c7ae113f2ea2e4752983d41683642ad698d`. Final evidence-only commits did not change the verified application source.
- **Browser evidence:** [screenshot artifact](https://github.com/muhdmuizz/sistem-abm/actions/runs/43412032393/artifacts/14029893078). Desktop reference table, mobile cards/header and sign-in page were downloaded and visually inspected. Sidebar persistence, accessible collapsed navigation, mobile drawer/escape and overflow checks passed. These are test-identity fixtures, not real-provider sign-in evidence.
- **Earlier verification correction:** an initial PHPUnit helper collided with its final callback method; renamed before execution. The intermediate verified revision passed 120 tests / 914 assertions; final results above include subsequent cache/logging and eligibility hardening.
- Local pull/migration: pending.
- Actual OAuth configuration, initial registered account and real-provider login: pending (user has no configured OAuth client yet).

Do not call this batch fully closed until required local/provider verification is explicitly recorded.

Final source verification includes Google-only null-password registration storage, redacted failure logging, no-store responses, individual workspace/email rejection cases and measured muted-text contrast of 4.73:1. These checks do not replace real-provider/manual acceptance.

## Local provider acceptance and initial-account correction — 13 November 2026

User reports that OAuth branding/client were created in the dedicated Sistem ABM Cloud project, credentials were saved locally, the Google button is enabled, and Google password/consent completed for `muizz@moe.gov.my`. The callback returned the generic ABM denial; the redacted local log shows `RuntimeException`. A guarded read-only query against PostgreSQL `sistem_abm` returned **Matching accounts: 0**. This confirms missing local registration as a blocker; it does not exclude another identity-provider eligibility issue. Personal `suarafata@gmail.com` manages Cloud only and is not approved as an ABM login. User confirmed **Sektor Pengurusan, JPNT** for the initial operator account.

A bounded correction is in progress: a local-console-only first-Superadmin registration command using an exact existing JPNT/Sektor, explicit real-operator declaration and reason, atomic account/role/audit creation, serialised first-run guard, and refusal to overwrite existing accounts or operate after any Superadmin exists. The audit labels identity assurance as local operator declaration, never as Google verification. No password, browser session, Google subject or verified-email timestamp is supplied by setup. Normal Google validation remains mandatory. No other roles/activity/OS assignments are granted. Production bootstrap and general account administration remain outside this correction.

Baseline: `30146d5599abd26860ecf77937748d820ebdd654`. Code verification is complete; see evidence below. Merge checkpoint, user execution and real login remain pending.

### Initial-account correction verification

- Tested application head: `5afed49b07bc929a966d98773244687bdb7594f5`.
- [CI 43422069980](https://github.com/muhdmuizz/sistem-abm/actions/runs/43422069980): **134 tests passed / 1002 assertions**, PHP 8.4.27 / PostgreSQL 18.7 against isolated `sistem_abm_testing`; frontend build and existing Chromium checks passed.
- Nine added bootstrap cases cover attributed initial registration, one-time closure (including removed role), existing case-variant refusal, exact organisational references, explicit declaration/reason, production denial, audit-failure rollback and normal verified-Google login after setup.
- First CI attempt caught an inherited test-helper name collision; corrected before successful verification. No unsuccessful revision was merged.
- [PR #12](https://github.com/muhdmuizz/sistem-abm/pull/12); merge checkpoint pending at this evidence commit. Subsequent evidence-only edits do not change tested PHP source.
- User execution of the command and actual successful Google callback remain pending. This correction does not establish Google tenant/domain eligibility or complete Batch 008 manual acceptance.
