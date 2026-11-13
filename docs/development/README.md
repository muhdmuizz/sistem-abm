# Sistem ABM — Development Progress Register

**Updated:** 14 November 2026 (Asia/Kuala_Lumpur).  
**Repository / development branch:** `muhdmuizz/sistem-abm` / `dev`.  
**Latest verified application-code checkpoint:** `22476277c47bcdfdb22aa6b428441528e6b182ec` (Batch 009 merged in PR #17; local migration and requested smoke checks user-confirmed). Documentation-only commits after this checkpoint do not represent another implementation batch.  
**Current application verification:** 153 tests passed, 1143 assertions on PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`.  
**Local state:** Batches 001–007 remain user-confirmed. After Batch 008 OAuth setup and PR #12 account-registration instructions, the user reports successful entry into the local web app using the intended MyGovUC identity. Initial setup, real login, four reference-page totals and logout/re-login are user-confirmed; narrow-window menu/cards/overflow are also user-confirmed; actual-device/pagination/session/manual acceptance remains pending. Batch 009 local migration, operator/add-user form inspection and own all-device logout/re-login are now user-confirmed. Other administration mutation/device checks remain unverified locally. The cloud agent did not inspect Windows or query its audit records directly.

This is the implementation/execution register. [The architecture README](../architecture/README.md) remains the single master AD index; design approval is not implementation completion. Batch numbers are bounded delivery checkpoints, not replacements for the previously documented six workstreams.

## Completed implementation and evidence

| Batch / correction | Delivered scope | Merged PR / application checkpoint | Verification at that checkpoint | Local status |
| --- | --- | --- | --- | --- |
| [001](batch-001-foundation.md) | PTJ, Aktiviti Kewangan and Kitaran models/migrations; integrity tests | [#1](https://github.com/muhdmuizz/sistem-abm/pull/1) / `893c0d0`; subsequent test-isolation commit `4388220` | Successful local validation reported in the supplied handover; foundational cases also pass in later CI. No initial dedicated CI count is claimed. | Completed per supplied handover |
| [002](batch-002-organisational-references.md) | Kod Aktiviti and approved organisational reference import | [#2](https://github.com/muhdmuizz/sistem-abm/pull/2) / `89f9cbf` | Handover reports local 16 tests / 47 assertions; original raw console output is not independently recovered here. Later CI covers these tests. | Completed per supplied handover |
| [003](batch-003-workplaces.md) | 579 workplaces and organisational PTJ relationships | [#3](https://github.com/muhdmuizz/sistem-abm/pull/3) / `3541052` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43367136902): 25 tests / 274 assertions | User confirmed “Completed without errors” |
| [004](batch-004-os-references.md) | 33 OS catalogue entries and 105 source mappings | [#4](https://github.com/muhdmuizz/sistem-abm/pull/4) / `15e5898` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43368361719): 39 tests / 631 assertions | Superseded by corrected checkpoint below before local sync |
| TASKA correction to 004 | Assign `010102` under PPN; upgrade prior blank mapping in place | [#5](https://github.com/muhdmuizz/sistem-abm/pull/5) / `4352dc4` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43368901971): 41 tests / 641 assertions | User confirmed “Done” after revised synchronisation instructions |
| [005](batch-005-ptj-permissions.md) | PTJ permission storage/resolution; 15 approved category/activity defaults | [#6](https://github.com/muhdmuizz/sistem-abm/pull/6) / `a8a8136` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43401547856): 59 tests / 711 assertions | User confirmed “Clean” |
| [006](batch-006-user-role-scopes.md) | User organisation and six-role assignment/scope foundation | [#7](https://github.com/muhdmuizz/sistem-abm/pull/7) / `f498f51` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43403240690): 81 tests / 781 assertions | User confirmed “Clean” |
| [007](batch-007-audit-foundation.md) | Transactional, attributable, append-only audit primitive | [#8](https://github.com/muhdmuizz/sistem-abm/pull/8) / `54ae2f7` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43405028148): 108 tests / 837 assertions | User confirmed “Clean” |
| [008](batch-008-first-interface.md) | Public Google login, responsive shell and read-only references; first local operator bootstrap | [#10](https://github.com/muhdmuizz/sistem-abm/pull/10), [#12](https://github.com/muhdmuizz/sistem-abm/pull/12) / `f6e785a` | 134 tests / 1002 assertions at `5afed49`; build/browser checks | User confirmed login, totals, logout/re-login and narrow-window layout; remaining manual checks tracked |
| [009](batch-009-user-administration.md) | Audited user/status/role administration, stale-write protection and all-device revocation | [#17](https://github.com/muhdmuizz/sistem-abm/pull/17) / `2247627` | [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43427826563): 153 tests / 1143 assertions at `b8ea428`; build/browser checks and visual review | User-confirmed local migration, operator/add-user inspection and own all-device logout/re-login; other mutations/devices unverified locally |

Tests/assertions above are full-suite totals at each tested revision, not the number added by a batch. Merged PR metadata and the successful CI log summaries were rechecked during this reconciliation. Batches 001–002 local evidence comes from the user-supplied 13 November handover; subsequent local confirmations come from this conversation. Neither is presented as direct cloud access to Windows.

## Current reference inventory

| Reference | Current count / state |
| --- | --- |
| PTJ | 69: 1 JPNT, 8 PPD, 60 SMK PTJ |
| Aktiviti Kewangan | 5 |
| Kod Aktiviti | 38 after TASKA correction; original Batch 002 imported 37 |
| Workplace references | 579: 15 JPNT organisational entries + 56 PPD Sektor + 448 non-PTJ schools + 60 SMK PTJ self-workplaces |
| OS catalogue | 33: 31 eligible references + 2 exclusions (POL and AMal) |
| Source OS mappings | 105: 103 non-excluded + 2 excluded |
| TASKA | OS42000, Kod Aktiviti `010102`, PPN |
| Permission defaults seeded | 15 category/activity defaults only |
| OS permission choices / individual PTJ exceptions | Not selected or seeded; do not guess |
| Real user activation / role assignments | Foundation seeders create no real users/roles. First local operator registration and Google login are now user-confirmed after the PR #12 bootstrap instructions. |

These are current source/import counts, not hardcoded application limits. Institutional codes for non-PTJ schools remain unverified and must not be fabricated.

## What is implemented versus still pending

| Area | Actual implementation state |
| --- | --- |
| Database and reference foundations | Implemented and tested within Batches 001–004 |
| Kitaran | Foundational model/table only; no Fasa or complete cycle administration |
| PTJ permissions | Storage, read-only resolution and approved activity defaults; no administration interface/mutation service |
| User/role scopes | Exact six-role storage/evaluation plus Batch 009 audited registration/status/scope administration; code merged; requested local smoke checks user-confirmed; other mutations unverified locally |
| Audit | Internal atomic audit primitive and model/PostgreSQL history protections; no audit viewer or automatic coverage of all future business operations |
| Audited access administration | Batch 009 user/status/role changes and all-device revocation implemented/tested; requested local smoke checks user-confirmed; other manual mutations/devices unverified. Central OS-permission editing and security email/notification delivery remain pending |
| ABM web interface | Batch 008 implements sign-in, shell and protected read-only references; code merged; first local Google login user-confirmed, remaining manual checks pending. Batch 009 adds the editable user/role interface (requested local smoke checks user-confirmed); financial and central permission interfaces remain pending. |
| Google sign-in and approved session policy | Batch 008 implements code flow, registered identity checks and idle/absolute expiry; first real local Google sign-in user-confirmed; Batch 009 adds revision-enforced all-device revocation. Own all-device logout/re-login is user-confirmed; other local administration/device/session acceptance remains pending. |
| Financial persistence and workflows | Bidaan, transactional Anggaran/Butiran, programme workflow, calculations, submissions/reviews/snapshots and official exports remain pending |
| Production/security release | Not certified; runtime/DDL privilege separation and other AD-012/security-baseline gates remain outstanding |

The foundation suite does not certify complete security controls, financial outcomes, deployed UI or production readiness. AD-008 remains Draft; catalogue import does not finalise OS financial specifications.

## Batch 008 — first local login confirmed; remaining manual acceptance pending

The user's “Let's continue” instruction after reconciliation is being executed as the next visible-interface deliverable: Google sign-in preparation, a responsive application shell and protected read-only reference screens. Baseline: `2d84e0c`. [PR #10](https://github.com/muhdmuizz/sistem-abm/pull/10) has verified code: head `b4af255`, [CI](https://github.com/muhdmuizz/sistem-abm/actions/runs/43412032393), 125 tests / 937 assertions plus build/browser checks and visual inspection. Code merged in PR #10 at `e5a93c7ae113f2ea2e4752983d41683642ad698d`. Google OAuth was initially unconfigured; initial local setup and real sign-in are now user-confirmed. See the acceptance updates below for remaining manual checks. No login bypass or guessed user/OS assignments is authorised.

Audited user/permission editing, financial workflows and production release remain outside this first interface slice. Build/browser checks and screenshot review passed. Local/provider acceptance must still be recorded here and in [Batch 008](batch-008-first-interface.md); do not mark the batch fully closed from a Git pull alone.

## Recording progress from now on

Follow [AGENTS.md](../../AGENTS.md). Each batch MD must record scope, baseline, actual tested SHA and evidence, merge checkpoint, local synchronisation and remaining limitations. Update this register at start, verification, merge and local confirmation. Use a documentation-only follow-up where final merge/local metadata arrives later. Do not begin another implementation batch while known close-out information is missing.

For the next session: verify live `dev`, read this register and the relevant batch/AD/reference files, then continue from the actual recorded state. Do not restart completed foundations or confuse the older six-step workstream description with a fresh implementation starting point.

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

## User-confirmed narrow-window layout — 13 November 2026

The user replied **“Clean.”** after narrowing the browser window and checking that the mobile menu opens/closes, reference tables become cards and no horizontal overflow appears. Mark those responsive layout checks passed by user report. This is a resized desktop browser check, not proof of sign-in or layout on an actual mobile device.

Current manual acceptance: initial Google login, four reference-page totals, logout/re-login and the specified narrow-window layout checks are user-confirmed. Pagination controls, actual mobile-device sign-in/layout, unregistered-account rejection and idle warning/renewal/idle/absolute expiry remain pending. Existing CI covers automated role/identity/session and browser-layout cases, but is recorded separately from manual acceptance. Batch 008 remains partially accepted; no complete security or production certification is claimed. Application source/tests are unchanged (134 tests / 1002 assertions at `5afed49`).

## Batch 009 — locally applied; requested smoke checks confirmed

User requested continuation after the recorded local interface checks. [Batch 009](batch-009-user-administration.md) adds the approved Pengguna & Peranan administration slice: explicit audited identity/status/scope changes, exact six-role tuples, stale-editor protection and all-device session revocation. Baseline `c76f52d`. No financial rules, real-user seeds or central OS permissions are changed. Security email delivery/notifications and Security Events viewer remain pending. Verification passed at tested head `b8ea428`: 153 tests / 1143 assertions, PHP 8.4.27 / PostgreSQL 18.7, build/browser checks and visual review. Merged in [PR #17](https://github.com/muhdmuizz/sistem-abm/pull/17) at `22476277c47bcdfdb22aa6b428441528e6b182ec`. Local migration and the requested inspection/own-logout smoke checks are now user-confirmed (see the acceptance record below); other manual mutations/device checks remain unverified. Batch 008's remaining manual checks stay open.

## User-confirmed Batch 009 local smoke checks — 13 November 2026

The user replied **“Clean.”** after the supplied guarded dev pull/database verification/migration/server instructions and the requested checks: open Pengguna & Peranan; inspect the existing operator's JPNT / Sektor Pengurusan / Superadmin record; inspect Tambah Pengguna; use own Log Keluar Semua Peranti from Utama; then sign in through Google again. Record local synchronisation/migration and those specific smoke checks as passed by user report. No direct cloud inspection of Windows, local audit rows or browser trace is claimed.

This does not establish manual creation of another real account, role/status mutations, simultaneous physical-device revocation, forced revocation of another user, stale concurrent browser editors, or notification delivery. Those remain unverified locally; their applicable automated cases are supported separately by CI 43427826563 (153 tests / 1143 assertions at b8ea428). Security email/notification operations and other out-of-scope work remain pending. No application changes or new test execution are claimed by this documentation update.

## Batch 010 — verification passed; merge/local acceptance pending

User explicitly approved the central PTJ → Aktiviti Kewangan → OS editor. [Batch 010](batch-010-central-permissions.md) implements category defaults and per-setting PTJ overrides/reset, mapped non-excluded OS choices, atomic attributable saves and content-state stale-editor checks. Baseline 674b80a. No permission selections, source mappings, roles, financial data or OAuth clients are seeded/changed by delivery. Verification reviewed on 14 November: tested head `32ba5ef`, [CI 43432337237](https://github.com/muhdmuizz/sistem-abm/actions/runs/43432337237), 168 tests / 1571 assertions on PHP 8.4.27 / PostgreSQL 18.7, build/browser checks and final visual review passed. Merge and local inspection remain pending. Earlier manual/security limitations remain tracked.
