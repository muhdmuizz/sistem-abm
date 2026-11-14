# Batch 014 — Audited Butiran Anggaran Draft Saves

**Status:** In progress; verification, merge and local synchronisation pending.
**Date:** 14 November 2026 (Asia/Kuala_Lumpur).
**Baseline:** `afc4d8577f003eca3080015b35c99235fdc6050a` on dev.
**Authorisation:** User approved audited source draft creation/editing, stable Butiran IDs, workplace/role/central/Fasa/workflow boundaries, stale-edit protection and atomic audit. Batches 012–013 local update/migrations are now user-confirmed.

## Scope and authority

AD-002/004: independent stable source identities; duplicate source values do not merge records; incomplete drafts can persist without financial completeness. AD-005/011: actual actor, exact role tuples, central OS permissions, both old/new workplace authorisation and atomic audit. AD-006: matching dated/scoped Fasa for ordinary preparers. AD-009: drafts editable only in Dalam Penyediaan or Dikembalikan, never submitted/finalised states.

Extract Batch 013 financial actor/session, lock and authority checks into one shared guard, preserving activation behavior. Draft service validates a strict metadata envelope (selected workplace, source JSON object and expected Bidaan/Anggaran/Butiran revisions). Source IDs and parent IDs are explicit server arguments, never mass-assigned from payload. Creating a source advances all applicable technical revisions; editing retains its identity. Stale revisions conflict; unchanged input is a no-op after current authority/revision checks. An original create retry with old revisions conflicts rather than creating another row; distinct new creates with fresh revisions remain distinct even for identical values.

The opaque JSON object is draft storage only, not an approved OS field schema, calculation or completeness result. Exact decimal inputs must be represented as strings in this generic boundary; finite integers, strings, booleans, null, lists and nested JSON objects are accepted. Binary floats, non-JSON objects/resources, invalid UTF-8, recursive structures and sensitive credential-like keys are rejected. No conversion of decimal text into floats or manual amount editing. A future public OS adapter must validate approved field whitelists/types under AD-008 before enabling user entry.

A workplace-limited preparer must be authorised for the existing source workplace and the proposed workplace. PTJ-level preparers can make permitted workplace changes within their explicit scope; cross-PTJ references fail. Lock order is actor/assignments → central-permission key → Kitaran → Bidaan → Anggaran → source → workplace. Parent revisions serialise related operations. Superadmin keeps actual identity and reasoned central/Fasa overrides, with unchanged workflow/revision/catalogue/workplace integrity checks.

Rows with generated outputs cannot be changed by this draft-only service until approved recalculation is integrated. Their source/output IDs and values remain unchanged. No totals, completeness flags, outputs or submission versions are written by a draft save. Audit records explicitly constructed before/after source/workplace/revision values; a failed audit or write rolls back all source and parent mutations.

## Boundaries

Backend services only; no new UI/route, autosave browser behavior, real seeds, OS field policy, programme association, duplication/deletion API, generated output mutation/recalculation, totals/readiness or snapshot/review implementation. Step 02 remains in progress. AD-013 remains required for future UI; broad visual refinement and the known empty-state issue remain deferred/tracked.

## Verification and handoff

Pending focused isolated PostgreSQL tests, full build/browser regression, tested SHA/counts, merge checkpoint and local synchronisation. No new migration is planned. Keep the existing local server running.
