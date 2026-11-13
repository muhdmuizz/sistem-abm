# Batch 011 — Kitaran ABM & Fasa Administration

**Status:** Code verified and merged; local migration/acceptance pending.
**Baseline:** 35db2e68d0a9e49c8b4faf83e13416acb62037af on dev.
**Authorisation:** User approved the proposed Batch 011: year-only Kitaran creation; Bidaan Awal/automatically numbered Pindaan; dates and global/selected PTJ/activity/exact-pair scope; audited open/close/extend/reopen.
**Authority:** AD-006 and AD-013 Section 11.3.1; AD-005 actor/role boundaries; AD-011 atomic audit, date/scope integrity and stale-write safeguards. Later AD-009 remains authoritative for review/finalisation; no earlier sequential review process is reintroduced.

## Bounded delivery and invariants

Existing Kitaran year remains its sole required creation field and unique identity. New Fasa stores its parent/year, stable type/Pindaan number, dates, configured opening scope, explicit opening state and a technical concurrency revision. Pindaan numbers are allocated under the parent Kitaran row lock, independently by Kitaran, without an arbitrary phase count cap. Overlapping periods and multiple open phases are allowed.

Every new phase is Ditutup regardless of browser input or current date. Dates must be valid, ordered and within its Kitaran year; database composite parent/year FK and date/type/scope constraints back the service checks. Gregorian years 1–9999 match the form/date representation, not a business cap on phase counts. Edit updates dates/scope while preserving type/number/open state; opening, closing, extending and reopening are separate explicit actions with reasons and attributable audit. Extending never opens a closed phase. Only the appropriate Buka/Tutup/Buka Semula control is offered for current state.

Each action applies to that Fasa's configured scope. PTJ-only and activity-only rows are explicit wildcard scopes; pair rows match their exact PTJ/activity pair, never a cross-product of separately selected IDs. The opening resolver permits a context only if a matching manually opened phase is within its inclusive date interval (Malaysia calendar, audit timestamps still UTC). Another applicable open phase can remain effective when a different overlapping phase closes; no exclusive-active rule or per-Bidaan edit exception is introduced. The resolver is uncached and establishes opening eligibility only, not role/central-OS/financial-workflow permission.

Fasa edit/action changes lock the real actor and parent Kitaran, recheck current authority/session revision, validate the expected technical revision, and commit scope/state/audit atomically. Unchanged scope tuples retain IDs. Parent foreign keys restrict destructive deletion. No Kitaran, Fasa, real scope or current-year selection is seeded. Contextual year text identifies the record being administered, not a fabricated global active cycle.

The UI provides year-only creation, expandable year rows with compact Fasa records, scope detail/counts, dynamic exact-pair entry, searchable PTJ selections, form errors/input preservation, appropriate action confirmations, and desktop/mobile layouts.

## Explicit boundaries

This batch does not implement Bidaan/Anggaran storage, submission/version snapshots, review decisions, finalisation, a global active-cycle selector, year renaming, phase reclassification/renumbering, or Kitaran archive/delete controls. Approved retention/empty-cycle deletion rules remain authoritative for a later implementation with financial dependencies. No audit/financial history is removed or weakened. Future financial writes must recheck the opening resolver alongside user/workplace/central permissions and financial state under the documented parent-lock discipline.

## Verification and local handoff

Tested application head: `431486581113c0294be1e850134eb21ea4d00697`.

[CI 43523750417](https://github.com/muhdmuizz/sistem-abm/actions/runs/43523750417): **187 tests passed / 1730 assertions**, PHP 8.4.27 / PostgreSQL 18.7, isolated `sistem_abm_testing`. The 19 added tests cover migration/constraints, per-cycle numbering, date/scope/open-state behavior, stale conflicts, nested IDs, actor/session denial and atomic audit-failure rollback. Frontend build and Chromium checks passed, including scope switching, exact-pair controls, unique row IDs and mobile overflow assertions.

Desktop/mobile Kitaran and Fasa screenshots were inspected on 14 November 2026 (Asia/Kuala_Lumpur); layout and visible controls passed review. [Screenshot artifact](https://github.com/muhdmuizz/sistem-abm/actions/runs/43523750417/artifacts/14084112496). These are isolated test fixtures, not seeded local records.

[PR #23](https://github.com/muhdmuizz/sistem-abm/pull/23); merged at `6c6e777b83e03d85a3ebab02eba45d5d8540fbfc`. Guarded local migration and UI acceptance remain pending. Routine local updates keep the existing server running: use another terminal, apply guarded migration/cache clears, then refresh.
