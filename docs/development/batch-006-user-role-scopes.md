# Batch 006 — User Organisational Identity & Six-Role Scope Foundation

Baseline: `a8a8136` on `dev`. Authority: AD-005's six roles and organisational scope, AD-003 separation of preparation roles, AD-011 current authorisation checks and relational integrity. This batch adds internal storage and read-only scope evaluation, not login or administrative mutation endpoints.

## Identity and storage

Users gain nullable PTJ and registered Tempat Bertugas references plus `is_active` (initially false). Existing and newly created users are not automatically activated or granted roles. Privileged identity/status fields are not added to User's mass-assignment whitelist. A composite foreign key enforces that a registered workplace belongs to the user's organisational PTJ; an additional check forbids workplace identity without PTJ context. PTJ-level identity may exist without a registered workplace while own-workplace preparation still requires that exact registered workplace.

Exactly six `Peranan` enum cases represent the approved functional roles. `penugasan_peranan` stores multiple explicit assignments per user. PostgreSQL checks enforce these shapes:

| Role | Stored scope |
| --- | --- |
| Superadmin | Global; no PTJ/activity/workplace restriction |
| Ketua Bidaan | Aktiviti Kewangan; all PTJ |
| Penyelia Bidaan | Aktiviti Kewangan; all PTJ |
| Penyedia Bidaan | PTJ + Aktiviti Kewangan |
| Penyedia Anggaran | PTJ + Aktiviti Kewangan; optionally own workplace |
| Pemantau Bidaan | Global JPNT coverage, one activity, or one PTJ; no workplace scope |

Null-aware uniqueness prevents duplicate global or scoped assignments. Foreign keys protect parent identity, and workplace assignments must belong to their assigned PTJ. Cross-record eligibility (such as current JPNT identity for Ketua and a workplace matching the actual user's registered workplace) is rechecked by the scope resolver; database scope-shape checks alone do not certify eligible role administration.

## Read-only scope evaluation

`SkopPeranan::meliputi` evaluates one requested role and explicit PTJ/activity context, optionally with a target workplace. It reloads the real user, current active status, organisation and assignments. Missing/inactive/unregistered organisational identity or invalid target context denies eligibility. Organisational membership alone grants no role.

Multiple assignments are additive per exact authorised tuple; they never create an unintended cross-product of PTJ/activity pairs. Penyedia Bidaan does not imply Penyedia Anggaran. PTJ-level estimate preparation covers its assigned PTJ workplaces, while workplace-level preparation requires an explicit target matching both the assignment and the user's own currently registered workplace; it cannot authorise an unrestricted workplace list. Reviews span all PTJ only within assigned activities. Ordinary Ketua eligibility requires current JPNT identity. Penyelia or Pemantau assignments do not confer Ketua or preparation roles. Monitor coverage remains read-only role eligibility, not permission to mutate. Active, organisationally registered Superadmin scope covers all approved role actions without replacing the real actor identity.

The resolver does not decide finalisation, returns, OS permissions, Kitaran/Fasa, financial editability, or Google authentication. Future operation policies must combine the relevant current role scope with Batch 005 PTJ/OS configuration, workplace filtering and workflow state, including explicitly authorised/audited Superadmin behaviour. Calls must use the authenticated actor ID on protected routes; an arbitrary supplied user ID is not authentication.

## Verification and exclusions

Tests cover default inactivity/no assignments, guarded identity fields, the six roles, role separation, exact multi-assignment pairs, own-workplace/PTJ boundaries, all reviewer and monitor scopes, JPNT-only ordinary Ketua, Superadmin scope, deactivation/revocation/organisational changes, null-aware uniqueness and database rejection of invalid scope/parent combinations. Actual PostgreSQL CI evidence is recorded on the PR.

No real users or role assignments are seeded. No sign-in page, OAuth callback, local-password flow, session-revocation implementation, role administration UI/API, audited grant/revoke service or financial operation is introduced. Existing Laravel skeleton password fields/factory remain untouched; their presence does not introduce a supported password sign-in path. Session termination and full audit controls remain required by AD-011 for later protected administration and authentication work. Fixture writes in tests are not an administrative API or a claim that user activation/assignment workflows are complete.

Approved architecture/reference documents, catalogue/mappings, rates/formulas and previous permission defaults remain unchanged.

## Local synchronisation after verified merge

On clean `dev`, pull fast-forward only, clear configuration and verify the actual PostgreSQL database is `sistem_abm`, then run `php artisan migrate`. There is no user/role seeder to run and no automatic assignment or activation. Do not use fresh/reset migrations.

## Verified close-out — 13 November 2026

- **Status:** Implemented, verified, merged and locally synchronised (user-confirmed).
- **Merge:** [PR #7](https://github.com/muhdmuizz/sistem-abm/pull/7), `f498f51f282171ac1d4c3325ef8608320bd580d8`.
- **Actual tested head:** `64307875fe57cc53b160c2f6eeebcf13bb975342`.
- **Runtime evidence:** [successful PostgreSQL CI run](https://github.com/muhdmuizz/sistem-abm/actions/runs/43403240690); PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`; **81 tests passed, 781 assertions** (full-suite totals).
- **Local evidence:** user reported “Clean” after the supplied PowerShell synchronisation instructions. This is not an independent cloud inspection of Windows/database state.
- **Remaining limitations:** the implementation boundaries above still apply; completion does not certify unimplemented screens, authentication, business workflows or production controls.

See [the development register](README.md) for the current application checkpoint and later changes.
