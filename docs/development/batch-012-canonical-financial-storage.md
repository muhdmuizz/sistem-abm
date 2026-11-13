# Batch 012 — Canonical Financial Storage

**Status:** In progress; CI, merge and local migration pending.
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

Pending isolated PostgreSQL tests for identities, same-PTJ attribution, parent/orphan integrity, exact finite money, source/output traceability, revision consistency, large row counts and model relationships. Build/browser regression remains applicable through existing CI; no new UI or visual completion is claimed. Record actual tested SHA/results and merge checkpoint before local handoff. Use guarded normal migration on local `sistem_abm`; keep the existing server running.
