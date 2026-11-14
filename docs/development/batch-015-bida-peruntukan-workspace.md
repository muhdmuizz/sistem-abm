# Batch 015 — Bida Peruntukan Workspace & Activation UI

**Status:** Code/visual verification passed and merged; requested local acceptance user-confirmed.
**Date:** 14 November 2026 (Asia/Kuala_Lumpur).
**Baseline:** `238971301798dd475b87b731fce525744cde1f73` on dev.
**Authorisation:** User approved authorised Bidaan context by Kitaran/PTJ/Aktiviti, UI-backed initial Bidaan/OS activation, clear states and feedback, and AD-013 spacing/anti-slop. Batch 014 local synchronisation/existing-page checks are user-confirmed.

## Scope and authority

AD-003 supplies meaningful inactive Bidaan and OS catalogue cards with explicit activation; AD-005/011 govern exact role/central/workplace/session boundaries and atomic audit; AD-006 governs real year/Fasa context and Superadmin authority; AD-009 remains authoritative for workflow status labels. AD-013 requires deliberate padding, restrained hierarchy, Lucide actions, keyboard/mobile use and state QA.

Expose the preparation workspace to Superadmin, Penyedia Bidaan, Penyedia Anggaran and explicitly scoped read-only Pemantau Bidaan. Reviewer-only assignments remain in their future review module; they do not grant preparation access. Select actual Kitaran and authorised PTJ explicitly, then see permitted financial activity cards. This is per-request workspace context, not a new global active-cycle setting. No sample year or real assignment/OS permission is seeded.

The activity list uses exact role tuples and effective central activity permission, except established Superadmin cross-scope authority. The OS catalogue uses mapped, non-excluded references and ordinary users' effective OS permissions. Monitor remains read-only. A workplace-scoped preparer uses their real registered assignment for module eligibility; no Butiran or amounts from other workplaces are exposed. Show actual activation/workflow states; never fabricate completeness, financial totals or submitted versions.

Read-only GETs do not create Bidaan/Anggaran. Contextual activation forms call the tested initial activation services on CSRF-protected, throttled POSTs with parent revision checks. Superadmin permission/Fasa bypass requires the existing explicit reason and attributable audit. No UI control independently grants authority; services recheck current actor/state/role/Fasa/central permissions inside their transactions. Direct cross-scope routes must be denied. Success follows server persistence; stale/database/audit failures show truthful retained-input errors. New catalogue/layout components share spacing tokens and padded empty states; broad old-page refinement remains deferred.

## Boundaries

No OS source editor/JSON editor, approved-field inference, rates/calculations/completeness, draft-save route, deactivation/recalculation, programme workflow, formal submission/review/snapshots or production certification. Active OS explain that preparation of Butiran is not yet open. No automatic activation, financial seeds, new migration or global Kitaran choice. Existing historical/manual/security limitations remain tracked.

## Verification and handoff

Tested application head: `daf17e186514e4df0d50f52cac5b591e6e73aeee`.

[CI 43552003016](https://github.com/muhdmuizz/sistem-abm/actions/runs/43552003016): **288 tests passed / 2293 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`; frontend build and all Chromium assertions passed. Sixteen added HTTP cases cover explicit/scoped context, GET-without-creation, actual audited activation POSTs, role separation/read-only monitor/reviewer-only denial, direct IDs, fresh central/Fasa/session checks, stale input retention, real audit-insert failure rollback, exact OS catalogue boundaries, status locks, no fabricated amounts and meaningful empty states.

Desktop/mobile review passed for eight states (16 screenshots): initial context, available activity cards, OS catalogue, no cycle, no permitted activities, no permitted OS, override confirmation and validation. Browser assertions cover responsive overflow, searchable PTJ, required reason, accessible validation markers and at least 16 px empty-state insets. [Screenshot artifact](https://github.com/muhdmuizz/sistem-abm/actions/runs/43552003016/artifacts/14096961494). Screens are isolated Blade fixtures, not the user's Windows browser or real financial seeds.

Early CI exposed incorrect validation-error fixtures for Laravel 13's JSON session serialization. The final fixture uses the framework's serialized error-bag shape; POST reason validation and error rendering are checked separately. Temporary diagnostics/workarounds were removed; production session serialization/authentication are unchanged. Static fixture deadlines use the browser's real clock rather than the financial test's future year. Browser checks exercise rendered fixtures, not live Google authentication or browser-to-app POSTs; server POST integration is covered separately. Local activation/persistence and requested feedback/spacing checks are user-confirmed; broader device/security acceptance remains unverified.

[PR #32](https://github.com/muhdmuizz/sistem-abm/pull/32); merged at `96e3507150dfa595ec241fe81517a48ba6777887`. No new migration. Local synchronisation/inspection user-confirmed; subsequent Fasa correction migration also user-confirmed. Step 02 remains in progress: OS field adapters, calculation integration and source editing UI remain deferred. Keep the running local server; pull/cache clear in another terminal and refresh.

Read presentation memoisation is transient within a service instance; mutation services independently recheck the database under their existing locks. It is not a cached authorisation grant or cross-request permission cache.

## User-confirmed local acceptance — 14 November 2026

The user reported **“Clean.”** after the supplied guarded dev pull/database-check/normal-migration/cache-clear commands and the requested local checks: create/save/open a Fasa for Kitaran 2028 starting in 2026; choose Kitaran/PTJ in Bida Peruntukan; activate one Bidaan and one OS; refresh to verify persistence; inspect feedback/spacing and the active-OS preparation-not-yet-open message. Record those bounded checks as user-confirmed. No direct cloud inspection of Windows, audit rows, all roles, stale editors, physical devices or OS calculations is claimed. Earlier security/manual limitations remain tracked. No new application changes or test execution accompany this documentation update.
