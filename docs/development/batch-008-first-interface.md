# Batch 008 — First Interface & Google Sign-in Preparation

**Status:** Code/build/browser verification passed and merged; first real local login, reference-page totals and logout/re-login user-confirmed; remaining manual checks pending.
**Baseline:** `2d84e0c` on `dev`.
**Authorisation:** User requested continuation after documentation reconciliation; the visible local interface is the next deliverable. Google OAuth was initially unconfigured; later setup and first login are recorded below.
**Authority:** AD-005 Google sign-in/registered-user access; AD-011 session and audit requirements; AD-013 Clean Enterprise interface; existing reference and role rules.

## Bounded delivery

Public sign-in page, a shared responsive application shell, a home page with truthful module availability, and protected read-only PTJ/Aktiviti Kewangan/Tempat Bertugas/OS lists. Global reference data is restricted to active registered Superadmin users; other assigned users see only the home shell. Desktop sidebar preference persists; mobile uses an accessible dialog drawer. Lists paginate at 25 rows and use compact cards on mobile. No arbitrary financial dashboard or fake financial data is presented.

Google authorization-code flow uses state, PKCE and account selection. The server exchanges the one-time code at Google and fetches identity directly from Google's HTTPS userinfo endpoint with the resulting access token. Browser-supplied identity claims/JWTs are not consumed; no unverified ID token is decoded, and tokeninfo is not used as a production verifier. Eligibility requires a Google-verified email, a hosted domain in the operator's configured allowlist, an active registered organisational user, and an explicit role assignment. Account/domain hints alone do not authorise access. The stable Google subject is linked uniquely to that existing account, and the real authenticated actor is audited in the same transaction. No automatic user provisioning or email-only authorisation is introduced. The legacy password column becomes nullable and is no longer mass assignable, permitting Google-only registrations without dummy passwords; existing values are retained and no password route is added. Downgrade to the password-required schema is refused while null-password accounts exist. Tokens/secrets/provider errors are not stored or flashed.

Protected requests recheck current status/assignments. Server enforces 30-minute idle and eight-hour absolute expiry. The UI warns five minutes before expiry and permits explicit renewal without resetting the absolute deadline. These server-rendered pages make no automatic polling/autosave requests that refresh activity. Ordinary POST logout invalidates the current ABM session only. Full administrative account/session-revocation services remain pending and are not claimed by these read-only screens.

## Setup and verification boundaries

See [local Google setup](google-local-setup.md). The code can render the public sign-in page before credentials exist; sign-in is explicitly unavailable until configured. No local authentication bypass is added. Credentials and the approved hosted-domain list must be entered in local `.env`, never committed or pasted into chat.

An active, organisationally registered user with an approved explicit role is also required. The original interface implementation/seeders create no real users or roles; the later explicitly authorised first-account bootstrap correction and user confirmation are recorded below. Initial account provisioning must be reviewed against actual supplied identity details; neither a guessed email nor an invented operator audit identity is acceptable. Real Google sign-in cannot be certified from HTTP mocks/browser fixtures.

HTTP tests cover routing, role restrictions, current-account checks, code/state handling, eligibility/binding and session timing. CI additionally builds existing frontend assets and runs Chromium checks/screenshots against actual Blade output rendered with isolated test identities. The static screenshot fixture server exists only in CI; it is not an application route or login bypass.

Audited user/permission editing, financial modules, OS defaults, new real-user assignments, complete security certification and production deployment remain outside this first interface slice. No approved AD or reference rule is amended.

## Evidence and close-out

- **Tested application head:** `b4af2551f9355db6d0d7821a70170cc1ad6661b5`.
- **CI:** [run 43412032393](https://github.com/muhdmuizz/sistem-abm/actions/runs/43412032393), PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`: **125 tests passed, 937 assertions**, including 17 new HTTP cases. Existing frontend build and Chromium layout checks passed.
- **Merge:** [PR #10](https://github.com/muhdmuizz/sistem-abm/pull/10), `e5a93c7ae113f2ea2e4752983d41683642ad698d`. Final evidence-only commits did not change the verified application source.
- **Browser evidence:** [screenshot artifact](https://github.com/muhdmuizz/sistem-abm/actions/runs/43412032393/artifacts/14029893078). Desktop reference table, mobile cards/header and sign-in page were downloaded and visually inspected. Sidebar persistence, accessible collapsed navigation, mobile drawer/escape and overflow checks passed. These are test-identity fixtures, not real-provider sign-in evidence.
- **Earlier verification correction:** an initial PHPUnit helper collided with its final callback method; renamed before execution. The intermediate verified revision passed 120 tests / 914 assertions; final results above include subsequent cache/logging and eligibility hardening.
- Local application setup: user-reported successful entry after the guarded pull/migration/bootstrap instructions; no independent cloud inspection.
- OAuth setup, initial registration and first real local login: user-confirmed; remaining manual acceptance is pending.

Do not call this batch fully closed until required local/provider verification is explicitly recorded.

Final source verification includes Google-only null-password registration storage, redacted failure logging, no-store responses, individual workspace/email rejection cases and measured muted-text contrast of 4.73:1. These checks do not replace real-provider/manual acceptance.

## Local provider acceptance and initial-account correction — 13 November 2026

User reports that OAuth branding/client were created in the dedicated Sistem ABM Cloud project, credentials were saved locally, the Google button is enabled, and Google password/consent completed for `muizz@moe.gov.my`. The callback returned the generic ABM denial; the redacted local log shows `RuntimeException`. A guarded read-only query against PostgreSQL `sistem_abm` returned **Matching accounts: 0**. This confirms missing local registration as a blocker; it does not exclude another identity-provider eligibility issue. Personal `suarafata@gmail.com` manages Cloud only and is not approved as an ABM login. User confirmed **Sektor Pengurusan, JPNT** for the initial operator account.

The merged bounded correction provides: a local-console-only first-Superadmin registration command using an exact existing JPNT/Sektor, explicit real-operator declaration and reason, atomic account/role/audit creation, serialised first-run guard, and refusal to overwrite existing accounts or operate after any Superadmin exists. The audit labels identity assurance as local operator declaration, never as Google verification. No password, browser session, Google subject or verified-email timestamp is supplied by setup. Normal Google validation remains mandatory. No other roles/activity/OS assignments are granted. Production bootstrap and general account administration remain outside this correction.

Baseline: `30146d5599abd26860ecf77937748d820ebdd654`. Code verification is complete; see evidence below. Code merged in PR #12; first successful local login is now user-confirmed (see subsequent acceptance record).

### Initial-account correction verification

- Tested application head: `5afed49b07bc929a966d98773244687bdb7594f5`.
- [CI 43422069980](https://github.com/muhdmuizz/sistem-abm/actions/runs/43422069980): **134 tests passed / 1002 assertions**, PHP 8.4.27 / PostgreSQL 18.7 against isolated `sistem_abm_testing`; frontend build and existing Chromium checks passed.
- Nine added bootstrap cases cover attributed initial registration, one-time closure (including removed role), existing case-variant refusal, exact organisational references, explicit declaration/reason, production denial, audit-failure rollback and normal verified-Google login after setup.
- First CI attempt caught an inherited test-helper name collision; corrected before successful verification. No unsuccessful revision was merged.
- [PR #12](https://github.com/muhdmuizz/sistem-abm/pull/12); merged at `f6e785a85367c24faee45d37a6c93c6cd6c301ae`. Subsequent evidence-only edits do not change tested PHP source.
- First successful real local login is now user-confirmed after the supplied setup instructions. Full Batch 008 manual acceptance and broader tenant/production approval remain unverified.

## User-confirmed first local login — 13 November 2026

After the guarded pull/migration/bootstrap instructions for PR #12, the user reported: **“I've entered the web app.”** Record local application setup and initial-account/real Google sign-in as user-confirmed for `muizz@moe.gov.my`, JPNT / Sektor Pengurusan, intended Superadmin. This is user-reported acceptance, not cloud inspection of Windows, a database/audit query, or an independently captured browser trace.

The initial missing-account blocker is resolved for this login. Personal Gmail remains Cloud administration only. No further OAuth client or login-policy relaxation was needed. App source remains the verified PR #12 checkpoint `f6e785a` (tested head `5afed49`, CI 43422069980: 134 tests / 1002 assertions).

At the first-login checkpoint, reference-page navigation/totals, pagination, device behaviour, logout/re-login, unregistered-account rejection and session checks were pending. See the subsequent confirmation below for the checks the user has since completed.

## User-confirmed reference pages and repeat login — 13 November 2026

The user replied **“Clean.”** to the requested checks: open all four reference sections, verify totals of PTJ **69**, Aktiviti Kewangan **5**, Tempat Bertugas **579**, OS **33**, then log out and sign in again. Record those specific checks as passed by user report. This does not establish pagination controls, mobile layout, unregistered-account rejection, idle/absolute expiry, renewal, or independent audit/database inspection. Those manual checks remain pending. No application code changed; existing CI evidence remains 134 tests / 1002 assertions at tested head `5afed49`.
