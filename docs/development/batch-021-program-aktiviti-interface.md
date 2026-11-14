# Batch 021 — Program/ Aktiviti Interface

**Status:** Verified and merged in PR #49 at `981b766d4435724abf6dc7b463bbce6c2a109fa1`; local acceptance pending. User approved the bounded interface scope on 14 November 2026.
**Baseline:** `e2197b6012d77f8f15ba7a95773e1eb1a6826618` on dev.

## Scope and authority

Implement a scoped programme list and one-programme four-section draft create/edit page under Bida Peruntukan. Reuse Batch 020's audited DrafProgramAktiviti service; preserve AD-007, Reference 06 strict objective defaults and AD-013 spacing/accessibility. Include dependent database-backed objectives, separate Jenis/Kategori, budget-year dates, package components, classified Jemputan/Peserta rows and derived totals. Authorised workplace context comes from the database. Live roles, central permissions, Fasa, active Bidaan, workflow and revisions continue to govern saves.

No OS selection/association, inheritance/recalculation, financial completeness, deletion, submission, optionality administration or resumed paused AD-008 design. Metadata readiness is not financial completion. No migration or seeder change is planned.

## Verification

Verified application/test head `e9da548c44ac0ab77a21d470de0d66b235be0654`: [CI 43583261810](https://github.com/muhdmuizz/sistem-abm/actions/runs/43583261810), **408 tests / 3129 assertions**, PHP 8.4.27 / PostgreSQL 18.7 against isolated `sistem_abm_testing`. Frontend build and existing/new Chromium checks passed. Fifteen HTTP interface tests added; foundation coverage remains intact. [PR #49](https://github.com/muhdmuizz/sistem-abm/pull/49). Local synchronisation and programme create/edit smoke checks remain pending until the user reports them. Do not equate fixtures with live Google/Windows acceptance.

## Implementation details

- Dedicated numeric GET/POST/PUT routes within the existing authenticated/current-session middleware. A Bid-level link opens a scoped, paginated programme list before create/edit. Program IDs are resolved under both the current Bid and permitted workplace scope.
- Presentation eligibility mirrors the exact Penyedia Anggaran role, active Bid, preparation/returned workflow, Fasa and authorised workplace. PB/monitor viewers receive disabled read-only metadata. The transactional save service remains authoritative, including old/new workplace and actual Superadmin reasons.
- The four-section form preserves strict Reference 06 objective defaults, source labels/local numbering and dependent-parent clearing. Jenis and Kategori remain distinct. Dates are bounded by the Kitaran year; Fasa may begin earlier.
- Each classified headcount row has a positive integer quantity, appropriate grade/Tahun/Tingkatan or Lain-lain note. Derived totals use BigInt in the browser and existing exact database sums; no user-entered total or financial completeness flag.
- Child lists are explicit full replacements, indexed contiguously after removals. Switching from Pakej with selected components requires acknowledgement before clearing the unsaved component selection. No financial association exists to delete or detach.
- Shared shell and spacing tokens; section jump links, authorised database-backed workplace search, labelled form controls, compact Lucide actions, mobile list cards, associated inline errors/summary and processing/unsaved feedback.
- Stale/save failure preserves user input and revisions; reload is explicit. Draft changes have existing beforeunload protection, including inputs returned after validation failure. UI remains a manual-save metadata form; no autosave or OS source entry is implied.
- Standard service validation now has Malay labels/messages; approved validation rules and optionality are unchanged. HTTP validation feedback is explicitly returned with inputs.
- QA capture uses committed real organisation references and synthetic programme/user data only. Artifact upload retains diagnostic fixtures when a check fails. An initial monitor fixture used an invalid scoped tuple and was corrected to the existing global monitor assignment. Redirect tests now preserve a session cookie, matching browser behaviour; temporary diagnostic output and filtered CI execution are removed.

## Scope still deferred

OS checklist/locking, programme-to-Butiran links, inherited OS data, financial recalculation/completeness, deletion, submission/review and Superadmin optionality settings. No database migration or reference import is required by this batch.

## Local acceptance

Pending after merge: guarded dev pull, cache clear, browser refresh, programme create/save/reopen/edit and dependent objective/headcount/date smoke checks. Cloud fixtures and automated PostgreSQL results do not certify the user's Windows installation, real-device/assistive-technology acceptance or a complete financial workflow.

## Browser and visual review

CI generated nineteen programme screenshots: empty list, populated list, create, edit, validation, conflict, read-only, Superadmin override and expanded Pakej at 1440/390 px, plus a dynamic mobile state. Review covered padding, wrapping, section/field gaps, derived totals, errors, disabled states and package expansion. The mobile objective-title clipping found in the first review was corrected with full selected descriptions below native selects; final screenshots confirm wrapping without overflow.

Chromium verifies dependent resets/disabled children, package reveal and acknowledged clearing, dynamic classified rows, exact totals above JavaScript's safe integer limit, contiguous submitted row indexes, unique IDs, unsaved status, budget-year native validation and mobile overflow. PHPUnit exercises actual HTTP persistence, draft validation/retained inputs, audit atomic rollback, revisions/no-op, scoping and read-only roles, workflow/phase/session/central permissions and Superadmin reasons. Fixtures are not an end-to-end signed-in Google session.

No asynchronous data retrieval was added; filtered programme-table/loading skeleton states are not applicable to this server-rendered manual-save slice. Actual-device, assistive-technology/contrast certification, concurrent browser sessions and local Windows acceptance remain unverified. No complete AD-007/financial UI or production readiness is claimed. Later evidence edits are documentation only; no additional application behavior is introduced by them.
