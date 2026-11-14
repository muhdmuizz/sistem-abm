# Batch 019 — Audited Draft Butiran Duplication

**Status:** Implementation prepared; CI/merge/local synchronisation pending.
**Date:** 14 November 2026 (Asia/Kuala_Lumpur).
**Baseline:** `9294293029924583bf6086fb40fb77095137ee1f` on dev.
**Authorisation:** User confirmed the combined local update/Log Tindakan inspection and directed continuation. Implement the next shared draft prerequisite under AD-004 stable-ID Salin Baris, AD-005/006 role/Fasa scope and AD-011 audit/concurrency safeguards.

## Scope

Add internal `DrafButiranAnggaran::salin` for one persisted draft source within its existing Anggaran. The caller supplies original source/estimate IDs and all three expected technical revisions, plus an optional reason. It cannot supply replacement source data, destination ownership/workplace, amount or completeness. Copy the validated stored source envelope into a new independent stable ID, preserve the actual workplace and exact JSON/decimal-text types, start the new source at revision one, leave original identity/input/revision/timestamp and siblings unchanged, and increment both parent revisions once. Blank/incomplete envelopes remain drafts; copies do not imply completion or submission.

Reuse private lock-context/current-revision helpers with existing saves; keep actor/assignment → central key → Kitaran → Bidaan → Anggaran → source → workplace lock order. Recheck current actor/session, exact Penyedia Anggaran scope, central OS permission/Fasa, active parents, editable workflow and mapped/non-excluded catalogue inside the transaction. An unchanged original source revision does not permit stale parent retries to duplicate again.

Any generated outputs on the original block copying until verified recalculation is integrated, including Superadmin. Do not clone output identities/amounts, create totals/completeness or guess a formula. Revalidate persisted generic JSON before copying so legacy-invalid floats/credential-like fields are not propagated or silently rewritten.

New copy plus parent revisions and attributable `source.draft_duplicated` audit commit atomically; actual audit failure restores all persisted state. Store private original-ID/revision lineage in audit only. The copied row remains independently editable through the existing guarded draft service. Opaque retained context is preserved; no relational Program/ Aktiviti association or OS-specific applicability is claimed.

Integrate only the new event label into existing scoped Log Tindakan, using new-row identity and recorded workplace checks. Raw source and lineage payloads remain unselected. No new UI layout, copy button, public source/copy API, migration or seed.

## Boundaries

Backend draft prerequisite only. No OS source fields/rates/calculations, calculated-source copying, bulk copy operation, deletion/history retention change, programme workflow, source editor, submission/snapshot or global audit viewer. Step 02 and bounded Step 03 remain in progress. Generic input preservation tests use synthetic fixtures, never an approved financial field schema.

## Verification and local handoff

CI/database/build/Chromium verification pending. Added cases cover independent identities and edits, exact nested/decimal data, private lineage/actual actor, stale original/parent revisions and retries, unknown/missing metadata, workplace/parent/other-role/session/central/Fasa denial, active/workflow/catalogue guards, calculated-output preservation, actual PostgreSQL audit failure rollback, legacy-invalid JSON and reasoned Superadmin. A route-level case verifies scoped duplicate-event rendering and preserves raw lineage privacy after the original moves workplace.

No structural UI change. A duplicate-event desktop/mobile fixture is added to existing browser/screenshot regression; review pending. Local synchronisation pending; direct duplication service execution has no public UI and remains unverified locally. Prior device/security/operational limitations persist.
