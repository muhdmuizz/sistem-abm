# Batch 018 — Scoped Anggaran Log Tindakan

**Status:** Implementation prepared; CI/visual verification/merge/local acceptance pending.
**Date:** 14 November 2026 (Asia/Kuala_Lumpur).
**Baseline:** `410698a993d6bc2de7a06ba3c4391c69f4bd5b35` on dev.
**Authorisation:** User directed continuation after Batch 017. Implement AD-004 shared Log Tindakan under AD-005/011 viewing/audit controls and AD-013 spacing/state review. Batches 016–017 local checks remain unconfirmed.

## Scope

Add a collapsible read-only Log Tindakan in the existing Anggaran workspace. Show actual recorded OS activation and draft create/update action, stable record reference, actor account display name, recorded effective role and Malaysia-local time. Resolve the name from the actual audit actor ID; the recorded role is not replaced by the account's current role. Newest first with stable ID ordering for tied times, 25 events per page; audit_page is independent of Butiran page and keeps the log expanded during pagination.

Reuse exact current workspace/catalogue/role/workplace authorisation. Constrain audit context to current PTJ/Aktiviti/Kitaran and exact entity type/identity. Source events require the current visible source, matching stored Bidaan/Anggaran identity, event workplace and both recorded old/new workplaces to be visible (create has no previous workplace). Moving a row away removes its events from a limited user's current scope; moving a foreign row in does not reveal its earlier foreign events. OS activation is OS-level metadata shared with authorised viewers of that OS. Apply all scope before pagination/counts. Unsupported action types, unrelated contexts and inaccessible rows contribute neither events nor counts.

Select only displayed metadata and actor name, not raw before/after payloads, free-text reasons, actor scope snapshots, source JSON, email or outputs. Query snapshots server-side only for parent/workplace eligibility; do not rewrite/sanitise audit storage. No history controls can mutate data or audit. Use the existing entity index and bounded queries; no migration or seed.

## Boundaries

This is not a global audit/security viewer, financial diff or Semakan/review history module. Only currently implemented OS activation and source draft create/update events are presented; future actions require deliberate integration. Snapshot/deleted-record history, programme association, OS editor/calculation/completeness, submission and user notification work remain deferred. No new public write endpoint. Prior operational/security/device limitations persist. Do not infer local acceptance from “Proceed.”

## Verification and handoff

CI/database/build/Chromium and screenshot review pending. Cases cover real audited activation/saves, payload omission without rewriting history, recorded actor/role, escaping, old/new/current workplace moves, entity/OS/context isolation, independent pagination and invalid pages, fresh permissions/role/session denial, read-only monitor and reasoned Superadmin. Synthetic events test exclusions only; they are not financial policy.

Visual states: empty/populated/scoped/paginated/read-only/validation, collapsed and expanded where applicable, on desktop/mobile. No asynchronous loading path added. Local update and UI inspection pending; keep running server and refresh after pull/cache clear.
