# Batch 015 — Bida Peruntukan Workspace & Activation UI

**Status:** In progress; CI/visual review, merge and local acceptance pending.
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

Pending role/scope/POST/error integration tests, full PostgreSQL/build/browser regression, desktop/mobile populated/empty/validation-state checks and screenshot review, actual tested SHA/counts, merge checkpoint and local acceptance. Keep the running local server; pull/cache clear in another terminal and refresh.
