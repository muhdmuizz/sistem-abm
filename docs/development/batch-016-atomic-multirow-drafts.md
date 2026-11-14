# Batch 016 — Atomic Multi-row Butiran Draft Saves

**Status:** Implementation prepared; CI/merge/local synchronisation pending.
**Date:** 14 November 2026 (Asia/Kuala_Lumpur).
**Baseline:** `b3762801ec22e44322b142004b9b35272b0b43dd` on dev.
**Authorisation:** User directed “Proceed.” after Batch 015 local acceptance. Implement the next bounded shared persistence prerequisite under AD-004 multi-row editing/draft saving and AD-011 transactional bulk edits; no OS-specific policy is inferred.

## Scope

Add internal `DrafButiranAnggaran::simpanBanyak` for a single Anggaran editor save: an ordered nonempty list of independent creates/edits with one expected Bidaan/Anggaran revision pair and existing-row revisions. Each new row omits butiran_id; each edit retains its explicit stable ID and supplies semakan_butiran. Source objects remain full replacements, not patches. Duplicate existing IDs (including numeric string aliases), unordered lists and unknown metadata are rejected. Same values in distinct new rows remain independent.

All entries commit together or none do, including their individual audit records and parent revisions. Reuse existing single-source guarded save within an outer transaction; locks persist across the save. Initial client parent revisions apply to the first row, then subsequent rows use the locked transaction's own updated revisions. A no-op does not increment revisions or generate audit. Original stale create retries conflict. Validation errors identify the supplied row index. No arbitrary 50-row total cap; callers should send bounded editor batches rather than full-dataset imports.

Every row retains the existing live actor/session, exact role, central permission/Fasa, old/new workplace, catalogue, active parent and workflow checks. Generated-output source changes remain blocked until approved recalculation exists. Actual actor, chosen workplace, exact decimal text and independent identities remain attributable. No success before transaction commit; later validation, scope, stale or database/audit failure rolls back earlier rows.

## Boundaries

Backend only: no new route, public JSON editor, UI, browser autosave, OS field adapter, rates/calculations, totals/completeness, duplication/deletion, programme association, submission/snapshot, migration or seed. Bidaan/Anggaran dates remain within their Kitaran year; only Fasa access dates are independent. Step 02 remains in progress. AD-008 remains Draft; approved fields must be validated by future OS adapters before public source entry.

## Verification

Pending CI against isolated sistem_abm_testing, build and existing Chromium regression. Added cases exercise mixed create/edit/no-op identities, >50 distinct rows, initial/later stale conflicts, indexed validation, strict envelope/duplicate IDs, later old/new workplace denial, source-parent isolation, generated output preservation, later real audit-insert failure, fresh guards and reasoned actual-actor overrides. Test payloads are synthetic, not OS financial specifications.

No UI change; no fresh visual QA claimed. Local update/smoke checks and direct service execution pending. Existing security/device/operational limitations remain tracked.
