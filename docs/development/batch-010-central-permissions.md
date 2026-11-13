# Batch 010 — Pengurusan Aktiviti Kewangan

**Status:** Code/tests/build/browser verification passed and merged; local synchronisation/requested inspection user-confirmed. Actual local permission mutations remain unverified.
**Baseline:** 674b80a78019dadc3ae3323c205baba1033b2eeb on dev.
**Authorisation:** User approved Batch 010 after the explicit proposal for the central PTJ → Aktiviti Kewangan → OS permission editor.
**Authority:** REF-04 source identities/mappings/exclusions; REF-05 category defaults, individual overrides and inheritance; AD-005 Superadmin and exact independent role scopes; AD-011 atomic audit/default deny/stale-write safeguards; AD-013 Section 11.3 expandable checkbox hierarchy.

## Bounded scope

Superadmin-only searchable/paginated PTJ list, three category-default editors and individual-PTJ editors. Expand each activity to configure that activity and each eligible OS independently. A category activity is an explicit on/off default; category OS remain unconfigured until explicitly set. An individual PTJ setting either explicitly allows/denies or inherits its category value per setting. Resetting an override deletes that override only. A stored false is distinct from missing configuration; no arbitrary OS/category/PTJ assignments are seeded.

Disabling a parent masks, but never deletes or rewrites, its stored OS choices. Checking a parent never checks all OS. Same-code purposes use their distinct catalogue IDs and descriptions. Only non-excluded mapped OS are offered/accepted; POL and AMal remain excluded, and existing source data, roles, financial records and financial hierarchy are unchanged. Neither a permission toggle nor catalogue eligibility certifies an implemented wizard/calculation.

Each activity save includes a complete explicit set of displayed eligible OS, a mandatory reason and a content-state revision covering target category, current category/individual settings and eligible reference identities. Recheck actual active Superadmin/session revision in the transaction. Serialise central category/PTJ writers with a transaction advisory lock so category dependencies cannot change between checking and saving. Reject changed target/inherited/reference state with HTTP 409. Update only that target/activity, preserve unchanged setting IDs, and commit changes with attributable before/after audit. No schema migration or seed is required. No persisted permission cache is introduced.

Client previews are labelled unsaved; saved counts change only after a successful server redirect. Multiple activity forms track unsaved changes separately and require confirmation before discarding another form's edits. Existing Google/session policy, user assignments and Superadmin exceptional authority remain unchanged.

This is central configuration administration, not financial workflow delivery or production/security certification. Future financial operations must intersect current central permissions with actual user/role/workplace/Fasa rules inside their own transaction. Security email/notification operations and previously recorded actual-device/session manual checks remain pending.

## Verification and handoff

Isolated suite, build/browser controls and screenshot review passed; see evidence below. Merged in PR #20 at `d514ae56bd5b8e963d921f1fd38cad794b273ac5`; requested local inspection is now user-confirmed (see acceptance record below). Default-only inspection must not be recorded as an actual permission-mutation test.

## Verification reviewed — 14 November 2026 (Asia/Kuala_Lumpur)

- Tested application head: `32ba5ef4e97879fc363ed4392d68fa9d990b7875`.
- [CI 43432337237](https://github.com/muhdmuizz/sistem-abm/actions/runs/43432337237): **168 tests passed / 1571 assertions**, PHP 8.4.27 / PostgreSQL 18.7 against isolated `sistem_abm_testing`. Frontend build and Chromium checks passed. The run completed on 13 November; its evidence was reviewed on 14 November.
- Fifteen added tests cover attributed category saves, independent PTJ overrides and inheritance/reset, parent masking/restoration, unset versus false settings, same-code OS variants, incomplete/excluded/unmapped/unknown payload rejection, target/category/reference stale conflicts, all five non-admin role denials, mutation-time actor/session rechecks, audit rollback, resolver/view agreement, validation, targets, search and pagination.
- Browser checks verify granular selection without automatic child grants, parent masking without losing OS selections, category unset/PTJ inheritance controls, protection for edits in another activity form, cancellation recovery and mobile overflow.
- [Screenshot artifact](https://github.com/muhdmuizz/sistem-abm/actions/runs/43432337237/artifacts/14038934423): category editor desktop/mobile and PTJ-list mobile screenshots downloaded and visually inspected; no layout blocker observed. These are isolated test fixtures, not the user's Windows installation.
- [PR #20](https://github.com/muhdmuizz/sistem-abm/pull/20); merged at `d514ae56bd5b8e963d921f1fd38cad794b273ac5`. Later evidence-only commits do not change tested application source.
- Local synchronisation and requested inspection are user-confirmed; actual local permission mutations remain unverified. No migration, seeder or permission-selection changes are required to apply the delivered code; no actual local permission mutations have been observed.

## Local handoff

From a clean dev branch, fast-forward pull the merged code and verify the application merge ancestor, then clear necessary configuration/routes/views from another terminal and refresh the browser while keeping the local server running (Ctrl+F5 for assets). Restart only if a specific change requires it. No schema migration, seeder, new package, OAuth setup or automatic permission assignment is required. Inspect Pentadbiran > Pengurusan Aktiviti Kewangan, the 69-PTJ list, the three category editors and one individual PTJ editor. Confirm existing values and the unset/inherited/specific controls without saving arbitrary permission selections. Record inspection separately from actual local permission-mutation acceptance.

## User-confirmed local inspection — 14 November 2026 (Asia/Kuala_Lumpur)

The user reported **“Batch 010 is clean.”** after the supplied local update and requested inspection of Pengurusan Aktiviti Kewangan: the 69-PTJ list, JPNT/PPD/SMK PTJ category editors, one individual PTJ's inherited settings, expandable activities and individual OS controls. Record local synchronisation and those requested inspection checks as passed by user report. The instructions asked for inspection without saving permission changes; this does not establish actual local permission writes, reset persistence, audit-row inspection, simultaneous stale editors, or physical-device checks. Applicable automated cases remain supported separately by CI 43432337237 (168 tests / 1571 assertions at 32ba5ef). No new application/test execution is claimed by this documentation update.

The user also clarified that the local server remains running during routine updates: pull/clear necessary caches in another terminal and refresh the browser (Ctrl+F5 when assets change). Restart only for a concrete change that requires it, with the reason explained. This preference is recorded in AGENTS.md for later sessions.
