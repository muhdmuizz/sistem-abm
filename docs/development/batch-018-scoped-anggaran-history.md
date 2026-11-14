# Batch 018 — Scoped Anggaran Log Tindakan

**Status:** Code/visual verification passed and merged; requested local acceptance user-confirmed.
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

Verified application head `48b245af39e20b2afb0e3c11e6eef5cb05f94bb9`: [CI 43561176414](https://github.com/muhdmuizz/sistem-abm/actions/runs/43561176414), **325 tests / 2596 assertions**, PHP 8.4.27 / PostgreSQL 18.7 in isolated `sistem_abm_testing`; frontend build and Chromium passed on the first run. Ten HTTP/database cases added; counts describe the complete suite. [PR #41](https://github.com/muhdmuizz/sistem-abm/pull/41), merged at `30713fd5c57244cafd3c7ae0a7a1b227c7c0a2d0`. Subsequent evidence edits are Markdown only.

Six expanded states at desktop/mobile plus empty/populated collapsed states (16 screenshots) reviewed for wrapping, context, record cards, insets and independent pagination. No defect identified in this bounded view. [Artifact 14100476192](https://github.com/muhdmuizz/sistem-abm/actions/runs/43561176414/artifacts/14100476192). Static fixtures verify rendered states/keyboard expansion; live local Google/browser flows remain unverified. Source/audit querying and denial paths are covered separately by HTTP/database tests. Cases cover real audited activation/saves, payload omission without rewriting history, recorded actor/role, escaping, old/new/current workplace moves, entity/OS/context isolation, independent pagination and invalid pages, fresh permissions/role/session denial, read-only monitor and reasoned Superadmin. Synthetic events test exclusions only; they are not financial policy.

Visual states: empty/populated/scoped/paginated/read-only/validation, collapsed and expanded where applicable, on desktop/mobile. No asynchronous loading path added. Local update and UI inspection pending; keep running server and refresh after pull/cache clear.

## User-confirmed local update and inspection — 14 November 2026

The user reported **“It's there. Clean. Proceed.”** after the supplied guarded dev pull/cache-clear commands and the requested active-OS opening/Log Tindakan expansion, activation-entry, actor/time and spacing checks. Record synchronisation through Batch 018 and these bounded UI checks as user-confirmed. This also confirms delivery of Batches 016–017 by the combined pull; it does not establish manual multi-row service execution, all OS navigation, other roles/devices, hidden-row permissions or audit/database inspection. No direct Windows access or new test execution is claimed. Earlier pending local-update statements are superseded only for these checks.
