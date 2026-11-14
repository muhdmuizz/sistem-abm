# Batch 017 — Shared Anggaran Read Workspace

**Status:** Code/visual verification passed and merged; requested local acceptance user-confirmed.
**Date:** 14 November 2026 (Asia/Kuala_Lumpur).
**Baseline:** `0670cc7c87a0659f5cebc730571152d8cc374843` on dev.
**Authorisation:** User directed “Proceed.” after Batch 016 implementation. Batch 016 local update remains unconfirmed; do not infer execution from continuation.

## Scope and authority

AD-004 shared OS workspace/context, stable Butiran identity and same-Bidaan navigation; AD-005/011 exact viewing/workplace/session isolation; AD-006 Fasa access dates; AD-013 shared spacing and responsive record lists. AD-008 remains Draft: no field schema or financial policy is inferred.

Expose Buka on activated OS catalogue cards and a shared read-only Anggaran page addressed by actual Bidaan + Anggaran IDs. Show real Kitaran/PTJ/Aktiviti/status/OS context, a paginated saved Butiran metadata list, server-scoped count, all applicable OS selector, previous/next and return navigation. Inactive OS navigation goes to an explicitly permitted activation confirmation or its catalogue anchor; GET never activates or creates records. Navigation resolves destinations server-side within the same Bidaan and fresh eligible catalogue.

Query only stable row references, workplace and saved time; never load/expose opaque source JSON or calculated outputs. Scope workplaces before pagination/counting. Exact PTJ-level preparation/monitor grants may view their permitted workplaces; own-workplace preparers see only their registered assigned workplace. Multiple assignments remain additive without crossing tuples. Scoped empty counts do not reveal other workplaces' records. Reviewer-only roles remain in their future review workspace. Closed Fasa/protected workflow states preserve authorised read access, without creating write authority.

## Boundaries

This is the shared read shell, not a completed spreadsheet editor or cross-Bidaan Senarai Anggaran hub. Keep preparation-not-open wording. No public source editor/save API, invented fields, financial totals/completeness, draft creation, source duplication/deletion, programme association/navigation, review history, submission, new migration or real seeds. Programme-led navigation waits for actual programme identities. Status/filter and financial column integration of the full hub remain pending. Bidaan/Anggaran dates stay within Kitaran year; Fasa access dates remain independent. Step 02 stays in progress; Step 03 begins only with this bounded read-shell portion.

## Verification and local handoff

Verified application head `7e5273077b4647df4e45774416be2bd9051e9b50`: [CI 43559544013](https://github.com/muhdmuizz/sistem-abm/actions/runs/43559544013), **315 tests / 2505 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`; frontend build and all Chromium assertions passed. Thirteen HTTP cases added. [PR #39](https://github.com/muhdmuizz/sistem-abm/pull/39), merged at `fab380ccf69e5c8501bcd520a95a3e9d2f522e81`. Subsequent evidence edits are Markdown only.

Twelve final screenshots (six states at desktop/mobile) reviewed for context, wrapping, insets and pagination; no layout defect identified in this bounded workspace. [Screenshot artifact 14099916643](https://github.com/muhdmuizz/sistem-abm/actions/runs/43559544013/artifacts/14099916643). Browser checks also assert active sidebar, selected OS, no opaque source editor and responsive overflow/empty-state padding. HTTP tests cover live route requests separately; static fixtures do not certify real Google/browser navigation.

Early checks caught out-of-range pagination and missing parent sidebar selection during review; fixed before final verification. One final-suite attempt falsely matched random CSRF-token letters in an overly broad no-currency assertion. It now checks visible currency text; application source was unchanged by that test correction. Added HTTP cases cover scoped metadata and counts, pagination, own-workplace empty state, tuple/registered-workplace denial, read-only roles and protected states, nested IDs, inactive/excluded catalogue, fresh revocation/session, context-preserving navigation and GET-without-mutations. Fixtures are synthetic metadata, never approved OS fields/rates.

Applicable visual states: populated, scoped paginated, empty, read-only, closed-phase and validation summary. No asynchronous loading path/financial editor exists here; no such state QA claimed. Local synchronisation/actual-browser inspection remains pending, alongside prior security/device limitations.

## User-confirmed local update and inspection — 14 November 2026

The user reported **“It's there. Clean. Proceed.”** after the supplied guarded dev pull/cache-clear commands and the requested active-OS opening/Log Tindakan expansion, activation-entry, actor/time and spacing checks. Record synchronisation through Batch 018 and these bounded UI checks as user-confirmed. This also confirms delivery of Batches 016–017 by the combined pull; it does not establish manual multi-row service execution, all OS navigation, other roles/devices, hidden-row permissions or audit/database inspection. No direct Windows access or new test execution is claimed. Earlier pending local-update statements are superseded only for these checks.
