# Batch 019 — Audited Draft Butiran Duplication

**Status:** Code verification passed and merged; local synchronisation pending.
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

Verified application head `ade9a9a61750b5ecb4a8a52d53fa4cd929826b59`: [CI 43562510603](https://github.com/muhdmuizz/sistem-abm/actions/runs/43562510603), **342 tests / 2722 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`; frontend build and Chromium passed on the first run. Seventeen added cases including parameterised role denials and the scoped HTTP integration case. [PR #43](https://github.com/muhdmuizz/sistem-abm/pull/43), merged at `c2ab85c9ce635b607d6111756454e9f1e5639498`. Subsequent evidence edits are Markdown only. Added cases cover independent identities and edits, exact nested/decimal data, private lineage/actual actor, stale original/parent revisions and retries, unknown/missing metadata, workplace/parent/other-role/session/central/Fasa denial, active/workflow/catalogue guards, calculated-output preservation, actual PostgreSQL audit failure rollback, legacy-invalid JSON and reasoned Superadmin. A route-level case verifies scoped duplicate-event rendering and preserves raw lineage privacy after the original moves workplace.

No structural UI change. The duplicate-event desktop/mobile fixture passed browser regression and both screenshots were reviewed: label, stable reference, wrapping and existing spacing passed. [Artifact 14101107873](https://github.com/muhdmuizz/sistem-abm/actions/runs/43562510603/artifacts/14101107873). This is static fixture QA, not direct local copy execution; the internal copy and scope behavior are separately tested against PostgreSQL. Local synchronisation pending; direct duplication service execution has no public UI and remains unverified locally. Prior device/security/operational limitations persist.
