# Batch 012 — Canonical Financial Storage

**Status:** Code verified and merged; local synchronisation/migration user-confirmed; financial operations unverified locally.
**Date:** 14 November 2026 (Asia/Kuala_Lumpur).
**Baseline:** `435a360f8a026c03b8bb7c93645d88e2f5e19415` on dev.
**Authorisation:** User approved Batch 012: canonical Bidaan → Anggaran (OS) → Butiran Anggaran storage, relationships, integrity safeguards and focused tests. Apply existing UI guidance to new interface work; defer the broad visual refinement.

## Authority and bounded scope

AD-002 and AD-011 govern financial parentage, identities and restrictive foreign keys; AD-003 separates activation from workflow/completeness; AD-004 requires independent source Butiran and traceable one-to-many generated outputs. AD-009 remains authoritative for the four later workflow statuses. AD-008 remains Draft and does not approve OS-specific fields, rates, rounding or formulas.

Add storage-only models/migration for Bidaan, Anggaran, Butiran Anggaran and subordinate generated calculation rows. A generated row is an output of a source Butiran, not another ownership/navigation layer. One Bidaan per PTJ/activity/cycle; one OS catalogue identity per Bidaan (variants with identical five-digit codes remain distinct references). Fasa never participates in Bidaan identity. No numeric count cap.

Technical replicated PTJ keys support composite foreign keys from Bidaan through Anggaran/Butiran to a specific workplace under the same PTJ; they do not create another owner or financial entity. Existing workplace reference types exclude generic JPNT/PPD. Restrictive deletion protects referenced parents; there are no cascade deletes or user-facing delete actions.

The source/output JSONB object envelopes deliberately define no OS input fields or 27-column mappings. Source rows can retain incomplete objects; they are not certified complete. Output rows retain stable IDs, source IDs, source revision and per-source order, with exact finite PostgreSQL numeric amounts (no binary float, arbitrary small precision/scale limit, positive-only rule or invented rounding). Output amounts must be supplied by future approved calculations, never arbitrary user-entered totals. No totals cache, completeness verdict, rate or calculation engine is added.

Bidaan/Anggaran activation defaults are closed/inactive. No workflow status/version column is introduced ahead of snapshot/transition enforcement. Technical revision columns are not submission versions; this batch provides no editable financial application endpoint. Models are mass-assignment guarded, and model relationships do not confer authority. Future business services must enforce real actor/session, exact role/PTJ/activity/OS/workplace permissions, open Fasa, state/revision checks and atomic audit. Structural constraints do not certify those later services.

## Explicit deferrals

No real financial seed, activation/save service or API/UI, automatic programme inheritance, OS fields/formulas/validation, completeness/total aggregation, submission snapshots/review/finalisation, imports or exports. Programme association must be added as a real foreign key before programme-led persistence is enabled; do not smuggle unvalidated programme IDs into JSON. Retention and controlled source deletion remain future audited operations. Existing empty-state spacing defect remains tracked separately.

## Verification and handoff

Tested application head: `6fd0a018fc816fa4a4b831d68c98f0b5b35a2f61`.

[CI 43531677184](https://github.com/muhdmuizz/sistem-abm/actions/runs/43531677184): **206 tests passed / 1858 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`. Nineteen added cases verify identities, same-PTJ attribution, orphan/null rejection, restrictive parent deletion, exact finite money (including zero/negative and high precision), independent source IDs/edits, source/output traceability and revision FK, atomic rollback, 1001 independently pageable sources, guarded models and migration reversal retaining organisational references. Synthetic amounts/JSON keys are test fixtures, not approved OS policy or real seeds.

Frontend build and all existing Chromium regression checks passed. No UI source changed; no new visual completion or financial workflow certification is claimed. Earlier CI attempts caught a PHPUnit helper-name collision and incorrect test expectations for PostgreSQL's RESTRICT SQLSTATE; these were corrected without weakening database constraints. No unsuccessful revision is merged.

[PR #26](https://github.com/muhdmuizz/sistem-abm/pull/26); merged at `0ff960d0744f06256d4025c482047ba6684d36be`. Local normal migration remains pending. Step 02 remains in progress: audited transactional financial write services and application workflows are not delivered by these storage models. Use guarded normal migration on local `sistem_abm`; keep the existing server running.

The output/source revision FK prevents advancing a source revision while older editable outputs still reference it. Future audited recalculation must replace affected obsolete outputs and advance the source revision atomically; prior submitted snapshots require their separately immutable storage. The schema does not independently authorise a write or enforce automatic revision increments.

## User-confirmed Batches 012–013 local update — 14 November 2026

The user replied **“Clean. Proceed.”** to the last supplied guarded dev pull/database verification/config-clear/normal-migration commands. Record local synchronisation and application of any pending Batch 012/013 migrations as passed by user report. No direct cloud access to Windows, local financial/audit-row inspection or activation service execution is claimed. Those batches delivered backend storage/activation services; manual financial operations and the broader device/security checks remain unverified. Existing CI evidence remains 239 tests / 2005 assertions at `c91a712`; no new test execution is claimed by this acceptance note.
